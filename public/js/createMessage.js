/**
 * !Important : Le textarea que le user voit à l'écran, n'est pas le inputMessage qui va contenir le message (et transiter en POST par la suite)
 * !tout se passe dans : 
 * *<div class="editor">
 * *</div>
 * !C'est dans cette div que Quill est initialisé et que la zone de saisie de Quill va s'afficher. 
 * !Avant d'envoyer des données en POST, tout le contenu de cette zone de saisie sera envoyé à l'input "inputMessage", qui fait bien partie du formulaire et donc sa "value" sera traité en POST
 * 
 */


const preview = document.querySelector('.preview');
const formMessage = document.querySelector('.formMessage');
const inputMessage = document.querySelector('.inputMessage');
const alertMessageTopic = document.querySelector('.alertMessageTopic');

const allBtnsQuote = document.querySelectorAll('.quoteMessage')
// const editor = document.querySelector('.ql-editor');

import { quill } from './common/initQuill.js';


//Ecouteur Formulaire
formMessage.addEventListener('submit', async (e) => {
    e.preventDefault();

    /**
     * !cas particulier
     * si le user clique sur "enter" sans rien écrire, Quill va quand même insérer du contenu : des balises vides. Donc la vérification if (quill.root.innerHTML === "") ne fonctionne pas.
     * l'astuce : 
     * l'astuce c'est de remplacer les balises vides' par rien du tout, sauf si la chaîne contient au moins une balise img. La méthode replace prend en premier argument une expression régulière qui correspond à toutes les balises HTML (c’est-à-dire tout ce qui est entre < et >), et en second argument une fonction de callback (ou fléchée plus loin) qui renvoie la correspondance elle-même si elle contient la chaîne 'img', sinon une chaîne vide.
     * 
     * Autre version plus "lisible" :
     * Dans cet exemple, nous utilisons une fonction de rappel pour vérifier si chaque correspondance de la regex contient la chaîne 'img'. Si c’est le cas, nous renvoyons la correspondance elle-même (c’est-à-dire que nous ne remplaçons rien), sinon nous renvoyons une chaîne vide pour remplacer la balise par rien du tout :
     *  
     * 
     * const contenuDeVerification = quill.root.innerHTML.replace(/<[^>]*>/g, function(match) {
        if (match.includes('img')) {
            return match;
        } else {
            return '';
        }
    });
     */

    try {


        // si la chaine n'est pas vide ou si il y a une balise img du type "<img src="data:image/"
        if (!quill.root.innerHTML.replace(/<[^>]*>/g, match => match.includes('<img src="data:image/') ? match : '')) {
            throw new Error(`Veuillez entrer un contenu valide.`);
        }


        // parcourir le contenu de l'éditeur pour trouver les images encodées en base64
        inputMessage.value = quill.root.innerHTML;
        const parser = new DOMParser();
        const doc = parser.parseFromString(inputMessage.value, 'text/html');
        const images = doc.querySelectorAll('img[src^="data:image/"]');

        //si il y a des images on procede au vérification d'usage et à l'enregistrement du message avec contenu temporaire pour obtenir son ID
        let resultat;
        if (images.length > 0) {
            //on boucle sur toutes les images pour s'assurer que le message du User ne comporte pas d'image supérieur à 300ko et que le type mime correspond aux contenu du tableau de type autorisé

            //Vérif du type MIME
            const TypeMimeAuthorized = [
                "image/jpg",
                "image/jpeg",
                "image/gif",
                "image/png"
            ];

            for (const image of images) {
                const imageBase64 = image.src;
                const blob = await fetch(imageBase64).then(res => res.blob());
                if (blob.size > 307200) {
                    throw new Error(`Le poids de l'image doit être inférieure à 300ko !`);
                }
                if (!TypeMimeAuthorized.includes(blob.type)) {
                    throw new Error(`Le fichier n'est pas une image valide. Extensions autorisées : png, gif ou jpeg(jpg) !`);
                }
            }
            //si type et poids ok, le code continu : 
            //! on créé un message avec contenu temporaire POUR obtenir son ID: 
            inputMessage.value = 'Contenu temporaire';

            let formData = new FormData(formMessage);
            let response = await fetch('?controller=message&action=create', {
                method: 'POST',
                body: formData
            });
            if (!response.ok) throw new Error(`Une erreur est survenue: ${response.status}`);

            resultat = await response.json();

            if (!resultat.boolean) throw new Error(resultat.message);

            //!ID
            const messageID = resultat.data.messageID;

            //on boucle pour enregistrer les images sur le serveur
            for (const image of images) {
                const imageBase64 = image.src;
                //*la fonction uploadImage renvoi l'adresse de l'image stocker sur le serveur.
                const imageUrl = await uploadImage(imageBase64, messageID);
                // * Une fois l'url récupérer, on remplace la représentation en base64 par l'URL de l'image.
                //! ainsi le contenu de l'editeur qui sera envoyer en base de données ne va pas contenir le text + image base64 mais bien le text + les urls d'images
                image.src = imageUrl;

            }
            //on incorpore le nouveau contenu dans l'input
            inputMessage.value = doc.body.innerHTML;

            formData = new FormData(formMessage);
            response = await fetch(`?controller=message&action=update&messageID=${messageID}`, {
                method: 'POST',
                body: formData
            });

            if (!response.ok) throw new Error(`Une erreur est survenue: ${response.status}`);

            resultat = await response.json();

            //si le boolen est à false
            if (!resultat.boolean) throw new Error(resultat.message);

        } else {
            const formData = new FormData(formMessage);
            const response = await fetch('?controller=message&action=create', {
                method: 'POST',
                body: formData
            });
            if (!response.ok) throw new Error(`Une erreur est survenue: ${response.status}`);

            resultat = await response.json();

            if (!resultat.boolean) throw new Error(resultat.message);
        }

        updateDOM(resultat);


    } catch (error) {

        if (error.message === 'noConnected') {
            //je le redirige en haut de la page pour qu'il se conecte, ensuite il sera redirigé vers la zone d'édition (voir code dans app.js)
            window.scrollTo({ top: 0, behavior: 'smooth' });
            document.querySelector('.btnLogin').classList.add('btnLoginAnimate')
        } else if (error.message === 'expired token') {
            window.location = "?controller=login";
        } else {
            alertMessageTopic.textContent = error.message;
            alertMessageTopic.style.display = "block";
        }
    }




});
async function uploadImage(imageBase64, messageID) {
    // * imageBase64 correspond à l'image en base64.
    // * la ligne ci dessous converti la représentation en base64 de l’image en un objet Blob.
    //* Blob contient les données binaires brutes de l’image
    const blob = await fetch(imageBase64).then(res => res.blob());

    if (blob.size > 307200) {
        throw new Error(`Le poids de l'image doit être inférieure à 300ko`);
    }
    const topicID = document.querySelector('.topicID').value;
    // *créer un objet FormData pour envoyer les données de l'image au serveur via la constante "blob"
    const formData = new FormData();
    formData.append('image', blob);
    formData.append('topicID', topicID);

    // * envoi d'une requête POST au serveur avec les données de l'image. Ce dernier l'interpretera avec un $_file
    // try {
    const response = await fetch(`index.php?controller=message&action=uploadImage&messageID=${messageID}`, {
        method: 'POST',
        body: formData,
    });

    // vérifier si la requête a réussi
    if (!response.ok) {
        throw new Error(`Une erreur est survenue lors du téléchargement de l'image: ${response.status}`);
    }

    //si la communication c'est bien déroulée , on traite les donnée json
    const resultat = await response.json();

    if (!resultat.boolean) {
        // dataTypeError = resultat.data;
        throw new Error(resultat.message);
    }

    return resultat.data.url



}

/**
 * !écouteur sur quill pour supprimer les éventuels message d'alerte
 * 
 * !On ne peux pas utiliser addEventListener directement avec l’éditeur Quill. L’éditeur Quill est une instance d’un objet JavaScript qui fournit sa propre API pour gérer les événements. Pour ajouter un écouteur d’événement à l’éditeur Quill, on doit utiliser la méthode "on" de l’éditeur Quill,
 */

// Ajouter un écouteur d'événement pour l'événement "text-change" de l'éditeur Quill
quill.on('text-change', () => {
    // Vérifier si du texte a été entré dans l'éditeur
    if (quill.getText()) {
        //supprime les éventuels messages d'alerte
        alertMessageTopic.textContent = "";
        alertMessageTopic.style.display = "none";
    }
});


/**
 * La fonction listenerQuote() devra être réutilisée dans la fonction updateDOM lorsqu'un nouveau message est crée par l'utilisateur. Ainsi, lorsqu'il créer un nouveau message, le user n'est pas obligé de recharger la page pour modifier son "citer" son message s'il le souhaite.
 */
allBtnsQuote.forEach(btnQuote => {
    btnQuote.addEventListener('click', () => listenerQuote(btnQuote));
});


function listenerQuote(btnQuote) {
    //dans chaque message listé, il y a deux attributs, data-pseudo et data-date qui possèdent du pseudo et de la date du message. cela permet de donner des infos à la citation. 
    const pseudoMessageCite = btnQuote.getAttribute('data-pseudo')
    const dateMessageCite = btnQuote.getAttribute('data-date')

    const messageQuote = btnQuote.parentNode.parentNode.nextElementSibling.innerHTML;
    //Quill ne prend en compte que quelques balises html (https://quilljs.com/docs/formats/)
    const insertDatas = `<blockquote><em><u>${pseudoMessageCite}</u> a écrit le ${dateMessageCite} : </em><br>${messageQuote}</blockquote>`;

    // //*Cette ligne utilise la méthode dangerouslyPasteHTML de l’objet clipboard de Quill pour insérer du contenu HTML dans l’éditeur Quill. Le premier argument, quill.getLength(), spécifie l’index où le contenu HTML doit être inséré. Dans ce cas, nous utilisons la méthode getLength pour obtenir la longueur actuelle du contenu de l’éditeur, ce qui signifie que le contenu HTML sera inséré à la fin du contenu existant. Le deuxième argument, insertDatas, est le contenu HTML à insérer.
    quill.clipboard.dangerouslyPasteHTML(quill.getLength(), insertDatas);

    // // Convertir le contenu HTML en un objet Delta
    // const delta = quill.clipboard.convert(insertDatas);

    // // Insérer le contenu dans l'éditeur QUILL
    // quill.setContents(delta, 'silent');

    //*Cette ligne utilise la méthode insertText pour insérer un caractère de saut de ligne (\n) dans l’éditeur Quill. Le premier argument, quill.getLength(), spécifie l’index où le caractère de saut de ligne doit être inséré. Comme pour la ligne précédente, nous utilisons la méthode getLength pour obtenir la longueur actuelle du contenu de l’éditeur, ce qui signifie que le caractère de saut de ligne sera inséré à la fin du contenu existant. Le deuxième argument, '\n', est le caractère de saut de ligne à insérer.
    quill.insertText(quill.getLength(), '\n');

    //*Cette ligne utilise la méthode setSelection pour déplacer le curseur dans l’éditeur Quill. Le premier argument, quill.getLength(), spécifie l’index où le curseur doit être placé. Comme pour les lignes précédentes, nous utilisons la méthode getLength pour obtenir la longueur actuelle du contenu de l’éditeur, ce qui signifie que le curseur sera placé à la fin du contenu existant. Le deuxième argument, 0, spécifie la longueur de la sélection. Dans ce cas, nous passons 0 pour indiquer que nous ne voulons pas sélectionner de texte, mais simplement déplacer le curseur.
    quill.setSelection(quill.getLength(), 0);
}




const formatDate = (date) => {
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: '2-digit', hour: '2-digit', minute: '2-digit' };
    let dateString = date.toLocaleString('fr-FR', options);
    dateString = dateString.charAt(0).toUpperCase() + dateString.slice(1);
    dateString = dateString.replace(':', 'h');
    return dateString;
}


function updateDOM(resultat) {

    console.log(resultat);
    const filePathAvatar = resultat.data.dataUser.filepathAvatar;
    const pseudo = resultat.data.dataUser.pseudo;
    const userGuitare = resultat.data.dataUser.userGuitare;
    const messagesCount = Number(resultat.data.dataUser.messagesCount) + 1;

    const templateItem = document.querySelector('.template-item');
    const messageList = document.querySelector('.messageList');
    const lastMessage = templateItem.content.cloneNode(true);

    lastMessage.querySelector('.avatar img').setAttribute('src', `./images/profils/${filePathAvatar}`);
    lastMessage.querySelector('.pseudo span').textContent = pseudo;
    lastMessage.querySelector('.guitare span').textContent = 'Ma guitare: ' + userGuitare;
    lastMessage.querySelector('.totalMessagesUser span').textContent = messagesCount + ' message' + (messagesCount > 1 ? 's' : '');

    let date = new Date();
    const dateActuelle = formatDate(date);

    lastMessage.querySelector('.messageDate span').textContent = dateActuelle;

    //editMessage
    lastMessage.querySelector('.editMessage a').setAttribute('href', `?controller=message&action=viewEdit&messageID=${resultat.data.messageID}`);
    //quoteMessage
    const btnQuote = lastMessage.querySelector('.quoteMessage');
    btnQuote.setAttribute('data-pseudo', pseudo);
    btnQuote.setAttribute('data-date', dateActuelle);
    btnQuote.addEventListener('click', () => listenerQuote(btnQuote));

    lastMessage.querySelector('.messageText span').innerHTML = resultat.data.reponseTopic;

    messageList.append(lastMessage);
    quill.root.innerHTML = "";

}


