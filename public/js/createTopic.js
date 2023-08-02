
// const preview = document.querySelector('.preview');
const formCreateTopic = document.querySelector('.formCreateTopic');
const inputTitleCreateTopic = document.querySelector('.inputTitleCreateTopic');
const topicID = document.querySelector('.topicID');
const inputTextTopic = document.querySelector('.inputTextTopic');
const alertCreateTopic = document.querySelector('.alertCreateTopic');
const editor = document.querySelector('.ql-editor');

inputTitleCreateTopic.addEventListener("keyup", () => {
    alertCreateTopic.textContent = "";
    alertCreateTopic.style.display = "none";
});

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


formCreateTopic.addEventListener('submit', async (e) => {
    e.preventDefault();


    try {
        //si titre vide, erreur
        if (inputTitleCreateTopic.value === "") {
            throw new Error("Le titre du topic ne doit pas être vide");
        }

        //CRéER LE TITRE DU TOPIC
        const formData = new FormData(formCreateTopic);
        const response = await fetch('?controller=topics&action=createTitleTopic', {
            method: 'POST',
            body: formData
        });
        //si erreur de communication avec le serveur
        if (!response.ok) throw new Error(`Une erreur est survenue: ${response.status}`);
        const resultat = await response.json();
        //si le boolen est à false
        if (!resultat.boolean) throw new Error(resultat.message);

        topicID.value = resultat.data.topicID;

        //insertion du premier message, celui du créateur du topic
        const contenuDeVerification = quill.root.innerHTML.replace(/<[^>]*>/g, match => match.includes('img') ? match : '');

        //si zone texte vide : erreur
        if (!contenuDeVerification) {
            throw new Error("Veuillez entrer du contenu dans la zone de texte");
        }

        //sinon on peux continuer
        inputTextTopic.value = quill.root.innerHTML;

        // parcourir le contenu de l'éditeur pour trouver les images encodées en base64
        const parser = new DOMParser();
        const doc = parser.parseFromString(inputTextTopic.value, 'text/html');
        const images = doc.querySelectorAll('img[src^="data:image/"]');

        //on boucle sur toutes les images pour s'assurer que le message du User ne comporte pas d'image supérieur à 300ko
        for (const image of images) {
            const imageBase64 = image.src;
            const blob = await fetch(imageBase64).then(res => res.blob());
            if (blob.size > 307200) {
                throw new Error(`Le poids de l'image doit être inférieure à 300ko`);
            }
        }
        // si aucunes images n'est supérieur à 300ko le code continue...
        for (const image of images) {
            const imageBase64 = image.src;
            //*la fonction uploadImage renvoi l'adresse de l'image stocker sur le serveur.
            const imageUrl = await uploadImage(imageBase64);
            // * Une fois l'url récupérer, on remplace la représentation en base64 par l'URL de l'image.
            //! ainsi le contenu de l'editeur qui sera envoyer en base de données ne va pas contenir le text + image base64 mais bien le text + les urls d'images
            image.src = imageUrl;

        }
        //et on incorpore le contenu de Quill dans inputTextTopic
        inputTextTopic.value = doc.body.innerHTML;

        const formText = new FormData(formCreateTopic);
        const response2 = await fetch('?controller=message&action=validation', {
            method: 'POST',
            body: formText
        });

        if (!response2.ok) {
            throw new Error(`Une erreur est survenue: ${response2.status}`);
        }

        const resultat2 = await response2.json();

        //si le boolen est à false
        if (!resultat2.boolean) {
            throw new Error(resultat2.message);
        }

        //si pas d'erreur,
        inputTextTopic.value = "";
        quill.root.innerHTML = "";
        inputTitleCreateTopic.value = "";
        //le topic est créer dans son intégralité;On peu rediriger.
        window.location.href = `index.php?controller=topics&action=list&catID=${resultat2.data.categoryID}`;


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
async function uploadImage(imageBase64) {
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
    const response = await fetch('?controller=message&action=uploadImage', {
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


