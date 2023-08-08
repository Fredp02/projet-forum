formMessage.addEventListener('submit', async (e) => {
    e.preventDefault();
    try {
        // ...
        // Vérifier si le contenu du formulaire est valide
        // ...

        // Créer le message en base de données avec un contenu temporaire
        const formData = new FormData(formMessage);
        formData.set('content', 'Contenu temporaire');
        let response = await fetch('?controller=message&action=create', {
            method: 'POST',
            body: formData
        });
        if (!response.ok) {
            throw new Error(`Une erreur est survenue: ${response.status}`);
        }
        let resultat = await response.json();
        if (!resultat.boolean) {
            throw new Error(resultat.message);
        }
        const messageID = resultat.messageID;

        // Traiter les images
        // ...
        // Vérifier la taille et le type MIME des images
        // ...

        for (const image of images) {
            const imageBase64 = image.src;
            const imageUrl = await uploadImage(imageBase64, messageID);
            image.src = imageUrl;
        }

        // Mettre à jour le contenu du message en base de données avec le contenu final
        inputMessage.value = doc.body.innerHTML;
        formData.set('content', inputMessage.value);
        response = await fetch('?controller=message&action=update&id=' + messageID, {
            method: 'POST',
            body: formData
        });
        if (!response.ok) {
            throw new Error(`Une erreur est survenue: ${response.status}`);
        }
        resultat = await response.json();
        if (!resultat.boolean) {
            throw new Error(resultat.message);
        }
    } catch (error) {
        // ...
    }
});

async function uploadImage(imageBase64, messageID) {
    // ...
    // Télécharger l'image sur le serveur dans un dossier portant l'identifiant du message
    // ...
}
