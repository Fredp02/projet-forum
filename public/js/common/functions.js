const typeMimeAuthorized = [
    "image/jpg",
    "image/jpeg",
    "image/gif",
    "image/png"
];

export async function verifImageBase64(images) {
    for (const image of images) {
        const imageBase64 = image.src;
        const blob = await fetch(imageBase64).then(res => res.blob());
        if (blob.size > 307200) {
            throw new Error(`Le poids de l'image doit être inférieure à 300ko !`);
        }
        if (!typeMimeAuthorized.includes(blob.type)) {
            throw new Error(`Le fichier n'est pas une image valide. Extensions autorisées : png, gif ou jpeg(jpg) !`);
        }
    }
}

export async function addMessageWithTempContent(inputMessage, formMessage) {
    inputMessage.value = 'Contenu temporaire';

    const formData = new FormData(formMessage);
    const response = await fetch('?controller=message&action=create', {
        method: 'POST',
        body: formData
    });
    if (!response.ok) throw new Error(`Une erreur est survenue: ${response.status}`);

    const resultat = await response.json();

    if (!resultat.boolean) throw new Error(resultat.message);

    //!ID
    return resultat.data.messageID;
}
export async function convertImageBase64(image, messageID, topicID) {
    const imageBase64 = image.src;
    //la fonction uploadImage renvoi l'adresse de l'image stocker sur le serveur.
    const imageUrl = await uploadImage(imageBase64, messageID, topicID);
    // Une fois l'url récupérer, on remplace la représentation en base64 par l'URL de l'image.
    //ainsi le contenu de l'editeur qui sera envoyer en base de données ne va pas contenir le text + image base64 mais bien le text + les urls d'images
    return imageUrl;
}


async function uploadImage(imageBase64, messageID, topicID) {
    // * imageBase64 correspond à l'image en base64.
    // * la ligne ci dessous converti la représentation en base64 de l’image en un objet Blob.
    //* Blob contient les données binaires brutes de l’image
    const blob = await fetch(imageBase64).then(res => res.blob());

    if (blob.size > 307200) {
        throw new Error(`Le poids de l'image doit être inférieure à 300ko`);
    }
    // const topicID = document.querySelector('.topicID').value;
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

export async function updateMessage(formMessage, messageID) {
    const formData = new FormData(formMessage);
    const response = await fetch(`?controller=message&action=update&messageID=${messageID}`, {
        method: 'POST',
        body: formData
    });

    if (!response.ok) throw new Error(`Une erreur est survenue: ${response.status}`);

    const resultat = await response.json();
    //si le boolen est à false
    if (!resultat.boolean) throw new Error(resultat.message);

    return resultat;
}