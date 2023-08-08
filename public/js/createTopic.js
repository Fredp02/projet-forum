
// const preview = document.querySelector('.preview');
const formCreateTopic = document.querySelector('.formCreateTopic');
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


formCreateTopic.addEventListener('submit', async (e) => {
    e.preventDefault();


    try {
        //si titre vide, erreur
        if (inputTitleCreateTopic.value === "") {
            throw new Error("Le titre du topic ne doit pas être vide !");
        }

        //CRéER LE TITRE DU TOPIC
        const formData = new FormData(formCreateTopic);
        let response = await fetch('?controller=topics&action=createTitleTopic', {
            method: 'POST',
            body: formData
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

        //si il y a des images on procede au vérification d'usage et à l'enregistrement du message avec contenu temporaire pour obtenir son ID

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

            let formData = new FormData(formCreateTopic);
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

            formData = new FormData(formCreateTopic);
            response = await fetch(`?controller=message&action=update&messageID=${messageID}`, {
                method: 'POST',
                body: formData
            });

            if (!response.ok) throw new Error(`Une erreur est survenue: ${response.status}`);

            resultat = await response.json();

            //si le boolen est à false
            if (!resultat.boolean) throw new Error(resultat.message);

        } else {
            const formData = new FormData(formCreateTopic);
            const response = await fetch('?controller=message&action=create', {
                method: 'POST',
                body: formData
            });
            if (!response.ok) throw new Error(`Une erreur est survenue: ${response.status}`);

            resultat = await response.json();

            if (!resultat.boolean) throw new Error(resultat.message);
        }

        //si pas d'erreur,
        inputMessage.value = "";
        quill.root.innerHTML = "";
        inputTitleCreateTopic.value = "";
        //le topic est créer dans son intégralité;On peu rediriger.
        window.location.href = `index.php?controller=topics&action=list&catID=${resultat.data.categoryID}`;
        // window.location.href = `index.php`;


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
        alertCreateTopic.textContent = "";
        alertCreateTopic.style.display = "none";
    }
});


