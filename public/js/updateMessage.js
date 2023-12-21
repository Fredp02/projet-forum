const formMessage = document.querySelector('.formMessage');
const inputMessage = document.querySelector('.inputMessage');
const messageID = document.querySelector('.messageID').value;
const topicID = document.querySelector('.topicID');
const alertMessageTopic = document.querySelector('.alertMessageTopic');
import { quill } from './common/initQuill.js';
import {
    verifImageBase64,
    convertImageBase64,
    updateMessage,
    listenerContentQuill
} from './common/functions.js';

quill.root.innerHTML = inputMessage.value;

//Ecouteur formulaire
formMessage.addEventListener('submit', async (e) => {
    e.preventDefault();

    try {

        // si la chaine n'est pas vide ou si il y a une balise img du type "<img src="data:image/"
        // if (!quill.root.innerHTML.replace(/<[^>]*>/g, match => match.includes('<img src="data:image/') ? match : '')) {
        //     throw new Error(`Veuillez entrer un contenu valide`);
        // }
        if (!quill.root.innerHTML.replace(/<[^>]*>/g, match => match.includes('<img src=') ? match : '')) {
            throw new Error(`Veuillez entrer un contenu valide`);
        }

        // parcourir le contenu de l'éditeur pour trouver les images encodées en base64
        inputMessage.value = quill.root.innerHTML;
        const parser = new DOMParser();
        const doc = parser.parseFromString(inputMessage.value, 'text/html');
        const images = doc.querySelectorAll('img[src^="data:image/"]');

        //on procede à la vérification des images 
        verifImageBase64(images);

        //on boucle pour enregistrer les images sur le serveur
        for (const image of images) {
            image.src = await convertImageBase64(image, messageID, topicID.value)
        }

        //on incorpore le nouveau contenu dans l'input
        inputMessage.value = doc.body.innerHTML;

        const resultat = await updateMessage(formMessage, messageID)

        window.location.href = `index.php?controller=topics&action=thread&threadID=${resultat.data.topicID}`


    } catch (error) {

        if (error.message === 'noConnected') {
            window.location.href = 'index.php?controller=login'
        } else if (error.message === 'expired token') {
            window.location = "?controller=login";
        } else {
            alertMessageTopic.textContent = error.message;
            alertMessageTopic.style.display = "block";
        }
    }
});


// Ajouter un écouteur d'événement pour l'événement "text-change" de l'éditeur Quill
listenerContentQuill(quill, alertMessageTopic);
