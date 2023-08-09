/**
 * !Important : Le textarea que le user voit à l'écran, n'est pas le inputMessage qui va contenir le message (et transiter en POST par la suite)
 * !tout se passe dans : 
 * *<div class="editor">
 * *</div>
 * !C'est dans cette div que Quill est initialisé et que la zone de saisie de Quill va s'afficher. 
 * !Avant d'envoyer des données en POST, tout le contenu de cette zone de saisie sera envoyé à l'input "inputMessage", qui fait bien partie du formulaire et donc sa "value" sera traité en POST
 * 
 */



const formMessage = document.querySelector('.formMessage');
const inputMessage = document.querySelector('.inputMessage');
const alertMessageTopic = document.querySelector('.alertMessageTopic');
const topicID = document.querySelector('.topicID')
const allBtnsQuote = document.querySelectorAll('.quoteMessage')
// const editor = document.querySelector('.ql-editor');

import { quill } from './common/initQuill.js';
import {
    verifImageBase64,
    addMessageWithTempContent,
    convertImageBase64,
    updateMessage,
    addMessage,
    listenerContentQuill
} from './common/functions.js';


//Ecouteur Formulaire
formMessage.addEventListener('submit', async (e) => {
    e.preventDefault();

    /**
     * !cas particulier
     * si le user clique sur "enter" sans rien écrire, Quill va quand même insérer des balises vides. Donc la vérification if (quill.root.innerHTML === "") ne fonctionne pas.
     * l'astuce c'est de remplacer les balises vides' par rien du tout, sauf si la chaîne contient au moins une balise img. La méthode replace prend en premier argument une regex qui correspond à toutes les balises HTML (c’est-à-dire tout ce qui est entre < et >), et en second argument une fonction de callback (ou fléchée plus loin) qui renvoie la correspondance elle-même si elle contient la chaîne 'img', sinon une chaîne vide.
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


        // si la chaine est vide 
        if (!quill.root.innerHTML.replace(/<[^>]*>/g, match => match.includes('<img src="data:image/') ? match : '')) {
            throw new Error(`Veuillez entrer un contenu valide.`);
        }


        // parcourir le contenu de l'éditeur pour trouver les images encodées en base64
        inputMessage.value = quill.root.innerHTML;
        const parser = new DOMParser();
        const doc = parser.parseFromString(inputMessage.value, 'text/html');
        const images = doc.querySelectorAll('img[src^="data:image/"]');

        //si il y a des images 
        let resultat;
        if (images.length > 0) {
            //on procede à la vérification des images        
            verifImageBase64(images);

            //si type et poids ok, le code continu : 
            //on créé un message avec contenu temporaire POUR obtenir son ID: 
            const messageID = await addMessageWithTempContent(inputMessage, formMessage);

            //on boucle pour enregistrer les images sur le serveur
            for (const image of images) {
                image.src = await convertImageBase64(image, messageID, topicID.value);
            }
            //on incorpore le nouveau contenu dans l'input
            inputMessage.value = doc.body.innerHTML;

            //et on met à jour le message
            resultat = await updateMessage(formMessage, messageID);


            //si il n'y a pas d'image, inutile de créer un message avec contenu temporaire
        } else {
            resultat = await addMessage(formMessage);
        }

        updateDOM(resultat);
        setTimeout(() => {
            alertMessageTopic.textContent = "Message créé avec succès";
            alertMessageTopic.style.display = "block";
            alertMessageTopic.style.backgroundColor = "green";
        }, 50);

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
            alertMessageTopic.style.backgroundColor = "rgb(242, 50, 50)";
        }
    }




});


// Ajouter un écouteur d'événement pour l'événement "text-change" de l'éditeur Quill
listenerContentQuill(quill, alertMessageTopic);


/**
 * La fonction listenerQuote() devra être réutilisée dans la fonction updateDOM lorsqu'un nouveau message est crée par l'utilisateur. Ainsi, lorsqu'il créer un nouveau message, le user n'est pas obligé de recharger la page pour son "citer" son message s'il le souhaite.
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


