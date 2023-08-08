const formMessage = document.querySelector('.formMessage');
const inputMessage = document.querySelector('.inputMessage');
const messageID = document.querySelector('.messageID');
const alertMessageTopic = document.querySelector('.alertMessageTopic');
// const editor = document.querySelector('.ql-editor');
import { quill } from './common/initQuill.js';


quill.root.innerHTML = inputMessage.value;

//Ecouteur Formulaire
formMessage.addEventListener('submit', async (e) => {
    e.preventDefault();

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

        //on boucle pour enregistrer les images sur le serveur
        for (const image of images) {
            const imageBase64 = image.src;
            //*la fonction uploadImage renvoi l'adresse de l'image stocker sur le serveur.
            const imageUrl = await uploadImage(imageBase64, messageID.value);
            // * Une fois l'url récupérer, on remplace la représentation en base64 par l'URL de l'image.
            //! ainsi le contenu de l'editeur qui sera envoyer en base de données ne va pas contenir le text + image base64 mais bien le text + les urls d'images
            image.src = imageUrl;

        }
        //on incorpore le nouveau contenu dans l'input
        inputMessage.value = doc.body.innerHTML;

        const formData = new FormData(formMessage);
        const response = await fetch(`?controller=message&action=update&messageID=${messageID.value}`, {
            method: 'POST',
            body: formData
        });

        if (!response.ok) throw new Error(`Une erreur est survenue: ${response.status}`);

        const resultat = await response.json();

        //si le boolen est à false
        if (!resultat.boolean) throw new Error(resultat.message);

        //si type et poids ok, le code continu : 


        window.location.href = `index.php?controller=topics&action=thread&threadID=${resultat.data.topicID}`


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