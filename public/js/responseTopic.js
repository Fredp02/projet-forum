
const preview = document.querySelector('.preview');
const formResponse = document.querySelector('.formResponse');
const inputResponse = document.querySelector('.inputResponse');
const alertMessageTopic = document.querySelector('.alertMessageTopic');

const allBtnsQuote = document.querySelectorAll('.quoteMessage')
const editor = document.querySelector('.ql-editor');

listennerBtnsQuote();

/**
 * ********************************************************
 */
/**
 * Ce code permet de régler un problème : 
 * Si on a un message avec un ou de smiley, ces dernier sont affichées grace à des span qui ont une classe particuliere : ap ap-xxxxxx. Le problèle c'est que si je clique sur le bouton citation d'un de ces messages, l'objet quill récupère le contenu mais uniuement le text ! donc plus de balise span (+les classes) et donc plus d'émoticon ! Pourquoi ?
 * Parce que seule quelque balise html son acceptées dans la zone de l'éditeur de quill. voir ce liens pour plus de détails : (https://quilljs.com/docs/formats/)
 * Il faut donc spécifier à Quill quel autre format je souhaite intégrer dans l'éditeur, et donc des span. Ainsi les spans + leurs classe respectuives sont incorporée dans les blockquote et les emoticone apparaissent bien dans les citations
 */
const Inline = Quill.import('blots/inline');

// Créer une nouvelle classe de format pour les éléments span
class SpanBlot extends Inline {
    static create(value) {
        let node = super.create();
        node.setAttribute('class', value);
        return node;
    }

    static formats(node) {
        return node.getAttribute('class');
    }
}
SpanBlot.blotName = 'span';
SpanBlot.tagName = 'span';
// Enregistrer la nouvelle classe de format
Quill.register(SpanBlot);

/**
 * ****************************************************
 */



const toolbarOptions = {
    container: [
        ['bold', 'italic', 'underline', 'strike'],
        ['blockquote'],
        [{ 'header': 1 }, { 'header': 2 }],
        [{ 'list': 'ordered' }, { 'list': 'bullet' }],
        [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
        [{ 'color': [] }, { 'background': [] }],
        ['clean'],
        ['emoji'],
        ['link', 'image']
    ],
    handlers: {
        'emoji': function () { }
    }
}

const quill = new Quill('.editor', {
    modules: {
        "toolbar": toolbarOptions,
        "emoji-toolbar": true,
        "emoji-shortname": true,
        "emoji-textarea": false
    },
    placeholder: 'Compose an epic...',
    theme: 'snow',

});
// const inputString = '<p><img src="data:image/jpeg;base64,/9j/4AA...FGZ"></p><p><img src="data:application/pdf;base64,/9j/4Ahk...FGZ"></p><p><img src="data:image/jpeg;base64,/9j/4Ag...FGZ"></p>';
// const outputString = inputString.replace(/<img[^>]*>/g, function (match) {
//     if (match.includes('src="data:image/')) {
//         return match;
//     } else {
//         return '';
//     }
// });
// console.log(outputString);

formResponse.addEventListener('submit', async (e) => {
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

        //Vérif du type MIME
        // parcourir le contenu de l'éditeur pour trouver les images encodées en base64
        const parser = new DOMParser();
        const doc = parser.parseFromString(quill.root.innerHTML, 'text/html');
        const images = doc.querySelectorAll('img[src^="data:image/"]');

        //on boucle sur toutes les images pour s'assurer que le message du User ne comporte pas d'image supérieur à 300ko et que le type mime correspond aux contenu du tableau de type autorisé
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
        //si type et poids ok, le code continu ...
        //tout le contenu de l'editeur Quill est enregistré dans le champs "inputResponse"
        inputResponse.value = quill.root.innerHTML;
        const formData = new FormData(formResponse);
        const response = await fetch('?controller=message&action=validation', {
            method: 'POST',
            body: formData
        });

        if (!response.ok) {
            //si erreur detecté, on génère un nouveau message d'erreur.
            // le "catch" va le récupérer.
            throw new Error(`Une erreur est survenue: ${response.status}`);
        }

        const resultat = await response.json();

        //si le boolen est à false
        if (!resultat.boolean) {
            throw new Error(resultat.message);
        }
        updateDOM(resultat);
        // listennerBtnsQuote();

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



function listennerBtnsQuote() {
    allBtnsQuote.forEach(btnQuote => {
        btnQuote.addEventListener('click', () => {

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
        })
    });
}








const formatDate = (date) => {
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: '2-digit', hour: '2-digit', minute: '2-digit' };
    let dateString = date.toLocaleString('fr-FR', options);
    dateString = dateString.charAt(0).toUpperCase() + dateString.slice(1);
    dateString = dateString.replace(':', 'h');
    return dateString;
}


function updateDOM(resultat,) {
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
    lastMessage.querySelector('.quoteMessage').setAttribute('data-pseudo', pseudo);
    lastMessage.querySelector('.quoteMessage').setAttribute('data-date', dateActuelle);
    // lastMessage.querySelector('.messageText span').innerHTML = content;
    lastMessage.querySelector('.messageText span').innerHTML = resultat.data.reponseTopic;

    messageList.append(lastMessage);
    quill.root.innerHTML = "";

}


