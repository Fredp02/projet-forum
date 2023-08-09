
// const preview = document.querySelector('.preview');
const formMessage = document.querySelector('.formCreateTopic');
const inputTitleCreateTopic = document.querySelector('.inputTitleCreateTopic');
const topicID = document.querySelector('.topicID');
const inputMessage = document.querySelector('.inputMessage');
const alertCreateTopic = document.querySelector('.alertCreateTopic');
// const editor = document.querySelector('.ql-editor');

inputTitleCreateTopic.addEventListener("keyup", () => {
    alertCreateTopic.textContent = "";
    alertCreateTopic.style.display = "none";
});



import { quill } from './common/initQuill.js';
import {
    verifImageBase64,
    addMessageWithTempContent,
    convertImageBase64,
    updateMessage,
    addMessage,
    listenerContentQuill
} from './common/functions.js';

formMessage.addEventListener('submit', async (e) => {
    e.preventDefault();


    try {
        //si titre vide, erreur
        if (inputTitleCreateTopic.value === "") {
            throw new Error("Le titre du topic ne doit pas être vide !");
        }

        //CRÉER LE TITRE DU TOPIC
        const formDataTitle = new FormData(formMessage);
        let response = await fetch('?controller=topics&action=createTitleTopic', {
            method: 'POST',
            body: formDataTitle
        });
        //si erreur de communication avec le serveur
        if (!response.ok) throw new Error(`Une erreur est survenue: ${response.status}`);
        let resultat = await response.json();
        //si le boolen est à false
        if (!resultat.boolean) throw new Error(resultat.message);

        //on stock l'id du topic fraichement crée qui nous servira à relier le message
        topicID.value = resultat.data.topicID;

        //insertion du premier message, celui du créateur du topic

        // si la chaine n'est pas vide ou si il y a une balise img du type "<img src="data:image/"
        if (!quill.root.innerHTML.replace(/<[^>]*>/g, match => match.includes('<img src="data:image/') ? match : '')) {
            throw new Error(`Veuillez entrer un contenu valide.`);
        }
        // parcourir le contenu de l'éditeur pour trouver les images encodées en base64
        inputMessage.value = quill.root.innerHTML;
        const parser = new DOMParser();
        const doc = parser.parseFromString(inputMessage.value, 'text/html');
        const images = doc.querySelectorAll('img[src^="data:image/"]');

        //s'il y a des images 
        if (images.length > 0) {
            //on procede à la vérification des images 
            verifImageBase64(images);

            //si type et poids ok, le code continu : 
            //on créé un message avec contenu temporaire POUR obtenir son ID: 
            const messageID = await addMessageWithTempContent(inputMessage, formMessage)

            //on boucle pour enregistrer les images sur le serveur
            for (const image of images) {
                image.src = await convertImageBase64(image, messageID, topicID.value)
            }
            //on incorpore le nouveau contenu dans l'input
            inputMessage.value = doc.body.innerHTML;

            //et on met à jour le message
            resultat = await updateMessage(formMessage, messageID)

        } else {
            resultat = await addMessage(formMessage);
        }

        //si pas d'erreur,
        inputMessage.value = "";
        quill.root.innerHTML = "";
        inputTitleCreateTopic.value = "";
        // console.log(categoryID);
        //le topic est créer dans son intégralité;On peu rediriger.
        window.location.href = `index.php?controller=topics&action=list&catID=${resultat.data.categoryID}`;



    } catch (error) {
        window.scrollTo({ top: 0, behavior: 'smooth' });
        if (error.message === 'expired token') {
            window.location.href = "index.php";
        } else if (error.message === 'no-connected') {
            window.location.href = "index.php?controller=login";
        } else {
            alertCreateTopic.textContent = error.message;
            alertCreateTopic.style.display = "block";
        }
    }





});



// Ajouter un écouteur d'événement pour l'événement "text-change" de l'éditeur Quill
listenerContentQuill(quill, alertCreateTopic);
// quill.on('text-change', () => {
//     // Vérifier si du texte a été entré dans l'éditeur
//     if (quill.getText()) {
//         //supprime les éventuels messages d'alerte
//         alertCreateTopic.textContent = "";
//         alertCreateTopic.style.display = "none";
//     }
// });


