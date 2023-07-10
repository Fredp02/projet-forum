
const inputAvatar = document.getElementById('formAvatar');
const elFormulaireAvatar = document.querySelector('.formulaireAvatar')
const elImgAvatar = document.querySelector('.divAvatar img')
const ElAlerteAvatarSpan = document.querySelector('.alerteAvatar span')
const ElAlerteAvatar = document.querySelector('.alerteAvatar')
const elDivAvatar = document.querySelector('.divAvatar')
const elContentFormAvatar = document.querySelector('.contentFormAvatar')
const elHideDivFormAvatar = document.querySelector('.hideDivFormAvatar')

const elAlertIdentifiant = document.querySelector('.alertIdentifiants');
//input email
const elDisplayEditEmail = document.querySelector('.displayEditEmail');
const elDivEmail = document.querySelector('.divEmail');
const elDivFormEmail = document.querySelector('.divFormEmail');
const elBtnEditEmail = document.querySelector('.btnEditEmail');
const elCancelEditEmail = document.querySelector('.cancelEditEmail');
const elInputEmail = document.querySelector('.inputEmail');
const elFormEmail = document.querySelector('.formEmail');
// const regexMail = /^\w+([-+.']\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/;

//password
const elDivPassword = document.querySelector('.divPassword');
const elDisplayEditPassword = document.querySelector('.displayEditPassword');
const elDivFormPassword = document.querySelector('.divFormPassword');
const elBtnEditPassword = document.querySelector('.btnEditPassword');
const elCancelEditPassword = document.querySelector('.cancelEditPassword');
const elAncienPassword = document.querySelector('#ancienPassword');
const elNouveauPassword = document.querySelector('#nouveauPassword');
const elConfirmPassword = document.querySelector('#confirmPassword');
const elFormPassword = document.querySelector('.formPassword');
const regexPassword = /^(?=.*[A-Z])(?=.*[!@#$%^&*()_+\-=\[\]{};':\"\\|,.<>\/?].*[!@#$%^&*()_+\-=\[\]{};':\"\\|,.<>\/?])[a-zA-Z0-9!@#$%^&*()_+\-=\[\]{};':\"\\|,.<>\/?]{8,50}$/;
;

//section "A propos"
const elbtnDisplayEditAbout = document.querySelector('.displayEditAbout');
const elContentAbout = document.querySelector('.contentAbout');
const elDivAbout = document.querySelector('.divAbout');
const elDivFormAbout = document.querySelector('.divFormAbout');
const elCancelEditAbout = document.querySelector('.cancelEditAbout');
const elBtnEditAbout = document.querySelector('.btnEditAbout');
const elFormAbout = document.querySelector('.formAbout');
const elSpanGuitare = document.querySelector('.spanGuitare');
const elSpanEmploi = document.querySelector('.spanEmploi');
const elSpanVille = document.querySelector('.spanVille');
const elAlertAbout = document.querySelector('.alertAbout');
const elInputGuitare = document.querySelector('.inputGuitare')
const elInputEmploi = document.querySelector('.inputEmploi')
const elInputVille = document.querySelector('.inputVille')

let email;
let emploi;
let guitare;
let ville;

// elAncienPassword.addEventListener("input", inputDefault);
// elNouveauPassword.addEventListener("input", inputDefault);
// elConfirmPassword.addEventListener("input", inputDefault);
// elInputEmail.addEventListener("input", inputDefault);
[elAncienPassword, elNouveauPassword, elConfirmPassword, elInputEmail].forEach(elInput => {
    elInput.addEventListener("input", inputDefault);
});


async function getData() {
    const response = await fetch('datasFormProfil');
    const resultat = await response.json();
    email = resultat.data.email;
    emploi = resultat.data.emploi;
    guitare = resultat.data.guitare;
    ville = resultat.data.ville;
}

//Ecouteurs boutons annuler
function listenerBtnCancelEdit() {
    const btnsCancelEdit = [
        elCancelEditEmail,
        elCancelEditPassword,
        elCancelEditAbout
    ];
    for (const btn of btnsCancelEdit) {
        btn.addEventListener('click', async (e) => {
            e.preventDefault();
            hideForm();
            undo();
            if (btn.classList.contains('cancelEditAbout')) {
                await getData();
                elInputGuitare.value = guitare;
                elInputEmploi.value = emploi;
                elInputVille.value = ville;
            }
            if (btn.classList.contains('cancelEditEmail')) {
                await getData();
                elInputEmail.value = email;
            }
            if (btn.classList.contains('cancelEditPassword')) {
                elAncienPassword.value = "";
                elNouveauPassword.value = "";
                elConfirmPassword.value = "";
            }

        })
    }


}
listenerBtnCancelEdit();

//Ecouteurs sur tous les boutons "EDITER" du Dom
function listenerBtnEdit() {
    const btnsDisplayEdit = [
        elDisplayEditEmail,
        elDisplayEditPassword,
        elbtnDisplayEditAbout
    ];
    for (const btn of btnsDisplayEdit) {
        btn.addEventListener('click', async () => {
            //s'ils sont affichés les autre formulaires vont être masqué
            hideForm();
            //s'ils sont masqués les autres éléments d'infos vont être affichés
            undo();

            await getData();
            elInputGuitare.value = guitare;
            elInputEmploi.value = emploi;
            elInputVille.value = ville;
            elInputEmail.value = email;

            const grandParent = btn.parentNode.parentNode;
            const NextElementGrandParent = grandParent.nextElementSibling;
            grandParent.style.display = "none";
            NextElementGrandParent.style.display = "block";
        })
    }
}
listenerBtnEdit();

//réinitialise et masque les formulaires visibles
function hideForm() {
    const tabElement = [elDivFormEmail, elDivFormPassword, elDivFormAbout]
    for (const element of tabElement) {
        const elementsInputs = element.querySelectorAll('input')
        for (const input of elementsInputs) {
            // input.value = "";
            if (input.getAttribute('type') !== "hidden") {
                input.classList.remove('inputError');
                input.previousElementSibling.style.color = "";
            }

        }

        element.style.display = "none";
    }
    elAncienPassword.value = "";
    elNouveauPassword.value = "";
    elConfirmPassword.value = "";
}

//affiche les lignes d'informations pour remettre la page "par defaut"
function undo() {
    const tabElement = [elDivEmail, elDivPassword, elDivAbout];
    for (const element of tabElement) {
        element.style.display = "flex";
    }
    elAlertIdentifiant.textContent = "";
    elAlertIdentifiant.style.backgroundColor = "";

}



function inputDefault() {

    this.classList.remove("inputError");
    this.previousElementSibling.style.color = "";
    elAlertIdentifiant.textContent = "";
    elAlertIdentifiant.style.backgroundColor = "";

}
//EDITION EMAIL
elBtnEditEmail.addEventListener('click', async (e) => {
    e.preventDefault();
    if (elInputEmail.value !== email && elInputEmail.value !== "" && elInputEmail.value.match(regexMail)) {
        const formEmail = new FormData(elFormEmail);
        try {
            //envoi des données au serveur avec la méthode POST
            const response = await fetch("editEmail", {
                method: "POST",
                body: formEmail,
            });
            if (!response.ok) {
                //si erreur detecté, on génère un nouveau message d'erreur.
                // le "catch" va le récupérer.
                throw new Error(`Une erreur est survenue: ${response.status}`);
            }
            //si la communication c'est bien déroulée , on traite les donnée json
            const resultat = await response.json();

            //si dans ces données, on à un booléen à false, alors on affiche son message
            //Il est "false" si les identifiants sont incorrectes ou si le compte est inactif
            if (!resultat.boolean) {
                // dataTypeError = resultat.data;
                throw new Error(resultat.message);

            }
            //si résultat.boolean = "true" : 
            elDivFormEmail.style.display = "none";
            elDivEmail.style.display = "flex";

            elAlertIdentifiant.style.backgroundColor = "#18AA50";
            elAlertIdentifiant.textContent = resultat.message;
            setTimeout(() => {
                elAlertIdentifiant.style.backgroundColor = "";
                elAlertIdentifiant.textContent = "";
            }, 10000);


        } catch (error) {
            if (error.message === "expired token") {
                window.location.href = "http://localhost/projet-forum/accueil";
            } else {
                elAlertIdentifiant.textContent = error.message;
                elAlertIdentifiant.style.backgroundColor = "#FF4242";
                setTimeout(() => {
                    console.log(error);
                    elAlertIdentifiant.textContent = "";
                    elAlertIdentifiant.style.backgroundColor = "";
                }, 10000);
            }
        }
    } else {
        elInputEmail.classList.add('inputError');
        elInputEmail.previousElementSibling.style.color = "#FF4242";
        if (elInputEmail.value === "") {
            $message = 'Le champs "email" ne doit pas être vide';
        } else if (elInputEmail.value === email) {
            $message = "L'adresse email est identique";
        } else {
            $message = "Format de l'adresse email incorrecte";
        }
        elAlertIdentifiant.textContent = $message;
        elAlertIdentifiant.style.backgroundColor = "#FF4242";
    }

})

//EDITION PASSWORD 
elBtnEditPassword.addEventListener('click', async (e) => {
    e.preventDefault();
    const elPasswordInputs = document.querySelectorAll('.divFormPassword input');
    elPasswordInputs.forEach(input => {
        if (input.getAttribute('type') !== "hidden") {
            input.classList.remove('inputError');
            input.previousElementSibling.style.color = "";
            elAlertIdentifiant.textContent = "";
            elAlertIdentifiant.style.backgroundColor = "";
        }

    });
    if (validateFormPassword()) {
        //on interrgoge la base de données
        const formData = new FormData(elFormPassword);
        try {
            // envoi des données au serveur avec la méthode POST
            const response = await fetch("password", {
                method: "POST",
                body: formData,
            });
            if (!response.ok) {
                //si erreur detecté, on génère un nouveau message d'erreur.
                // le "catch" va le récupérer.
                throw new Error(`Une erreur est survenue: ${response.status}`);
            }
            //si la communication c'est bien déroulée , on traite les donnée json
            const resultat = await response.json();

            //si dans ces données, on à un booléen à false, alors on affiche son message
            //Il est "false" si les identifiants sont incorrectes ou si le compte est inactif
            if (!resultat.boolean) {
                throw new Error(resultat.message);

            }
            elAncienPassword.value = "";
            elNouveauPassword.value = "";
            elConfirmPassword.value = "";
            hideForm();
            undo();
            elAlertIdentifiant.textContent = resultat.message;
            elAlertIdentifiant.style.backgroundColor = "#18AA50";
            setTimeout(() => {
                elAlertIdentifiant.textContent = "";
                elAlertIdentifiant.style.backgroundColor = "";
            }, 5000);

        } catch (error) {
            if (error.message === "expired token") {
                window.location.href = "http://localhost/projet-forum/accueil";
            } else {
                elAlertIdentifiant.textContent = error.message;
                elAlertIdentifiant.style.backgroundColor = "#FF4242";
            }
        }
    }


})
function validateFormPassword() {

    //Vérification du password
    // debugger;
    if (elAncienPassword.value === "") {
        elAncienPassword.classList.add('inputError');
        elAncienPassword.previousElementSibling.style.color = "#FF4242";
        elAlertIdentifiant.textContent = "L'ancien mot de passe est requis";
        elAlertIdentifiant.style.backgroundColor = "#FF4242";
        return false;
    }
    if (elNouveauPassword.value === "") {
        elNouveauPassword.classList.add('inputError');
        elNouveauPassword.previousElementSibling.style.color = "#FF4242";
        elAlertIdentifiant.textContent = "Champs \"nouveau mot de passe\" requis";
        elAlertIdentifiant.style.backgroundColor = "#FF4242";
        return false;
    }
    if (!elNouveauPassword.value.match(regexPassword)) {
        elNouveauPassword.classList.add('inputError');
        elNouveauPassword.previousElementSibling.style.color = "#FF4242";
        elAlertIdentifiant.textContent = "Mot de passe : entre 8 et 50 caractères dont 2 caractères spéciaux et une majuscule";
        elAlertIdentifiant.style.backgroundColor = "#FF4242";
        return false;
    }
    if (elConfirmPassword.value === "") {
        elConfirmPassword.classList.add('inputError');
        elConfirmPassword.previousElementSibling.style.color = "#FF4242";
        elAlertIdentifiant.textContent = "Confirmez votre mot de passe";
        elAlertIdentifiant.style.backgroundColor = "#FF4242";
        return false;
    }
    if (elNouveauPassword.value !== elConfirmPassword.value) {
        // elNouveauPassword.classList.add('inputError');
        elConfirmPassword.classList.add('inputError');
        // elNouveauPassword.previousElementSibling.style.color = "#FF4242";
        elConfirmPassword.previousElementSibling.style.color = "#FF4242";
        elAlertIdentifiant.textContent = "Les mots de passe de correspondent pas";
        elAlertIdentifiant.style.backgroundColor = "#FF4242";
        return false;
    }

    return true
}




//display form avatar
elDivAvatar.addEventListener('click', (e) => {
    elContentFormAvatar.style.display = "flex";
})
elHideDivFormAvatar.addEventListener('click', (e) => {
    elContentFormAvatar.style.display = "none";
})



// EDIT AVATAR
inputAvatar.addEventListener('change', async function () {
    // const elFormulaireAvatar = document.querySelector('.formulaireAvatar')
    try {

        // On vérifie s'il y a un fichier sélectionné
        if (this.files.length > 0) {
            // On récupère le premier fichier
            const fichier = this.files[0];

            if (fichier.type !== "image/png" && fichier.type !== "image/gif" && fichier.type !== "image/jpeg") {
                // si l'extension c'est "jpg" (et pas jpeg) fichier.type sera quand même égale à "image/jpeg"
                throw new Error("Le fichier n'est pas une image valide. Extensions autorisées : png, gif ou jpeg(jpg)");
            }
            if (fichier.size > 153600) { //150 ko * 1024
                // si limites dépassées
                throw new Error("Le poids de l'image doit être inférieur à 150ko");
            }
            // On appel la fonction de vérification
            const { width, height } = await getImageDimensions(fichier);
            // On vérifie si la largeur ou la hauteur dépasse les limites
            if (width > 200 || height > 200) {
                // si limites dépassées
                throw new Error('Le fichier doit avoir une largeur et une hauteur maximale de 200 pixels.');
            }
            //sinon
            let formData = new FormData(elFormulaireAvatar);
            // formData.append("avatarPhoto", fichier);

            const response = await fetch("avatar", {
                method: "POST",
                body: formData,
            });
            if (!response.ok) {
                //si erreur detecté, on génère un nouveau message d'erreur.
                // le "catch" va le récupérer.
                throw new Error(`Une erreur est survenue: ${response.status}`);
            }
            //si la communication c'est bien déroulée , on traite les donnée json
            const resultat = await response.json();
            if (!resultat.boolean) {
                throw new Error(resultat.message);
            }
            //si résultat "true" :
            const userId = resultat.data.userId
            const avatar = resultat.data.avatar
            const cheminNouvelAvatar = "../images/profils/" + userId + '/' + avatar;
            elImgAvatar.setAttribute("src", cheminNouvelAvatar);
            inputAvatar.value = "";
            elContentFormAvatar.style.display = "none";

        }
    } catch (error) {
        if (error.message === "expired token") {
            window.location.href = "http://localhost/projet-forum/accueil";
        } else {
            ElAlerteAvatarSpan.textContent = error.message
            ElAlerteAvatar.style.backgroundColor = 'red';
            inputAvatar.value = "";
        }
    }
});

async function getImageDimensions(fichier) {
    let img = new Image();
    img.src = URL.createObjectURL(fichier);
    await img.decode();
    // On utilise les propriétés naturalWidth et naturalHeight
    let width = img.naturalWidth;
    let height = img.naturalHeight;
    return {
        width,
        height,
    };
}

//SECTION A PROPOS
elBtnEditAbout.addEventListener('click', async (e) => {
    e.preventDefault();
    if (elInputGuitare.value === "") elInputGuitare.value = "Non renseigné";
    if (elInputEmploi.value === "") elInputEmploi.value = "Non renseigné";
    if (elInputVille.value === "") elInputVille.value = "Non renseigné";

    const formAbout = new FormData(elFormAbout);
    try {
        // debugger;
        if (elInputGuitare.value === guitare &&
            elInputEmploi.value === emploi &&
            elInputVille.value === ville) {
            throw new Error("Aucune modifications effectuées : valeur identique");
        }



        //envoi des données au serveur avec la méthode POST
        const response = await fetch("about", {
            method: "POST",
            body: formAbout,
        });
        if (!response.ok) {
            //si erreur detecté, on génère un nouveau message d'erreur.
            // le "catch" va le récupérer.
            throw new Error(`Une erreur est survenue: ${response.status}`);
        }
        //si la communication c'est bien déroulée , on traite les donnée json
        const resultat = await response.json();

        //si dans ces données, on à un booléen à false, alors on affiche son message
        //Il est "false" si les identifiants sont incorrectes ou si le compte est inactif
        if (!resultat.boolean) {
            // dataTypeError = resultat.data;
            throw new Error(resultat.message);

        }
        //si résultat.boolean = "true" : 
        const elAboutInputs = document.querySelectorAll('.formAbout input');
        elAboutInputs.forEach(input => {
            input.value = "";
        });
        elDivFormAbout.style.display = "none";

        elSpanGuitare.textContent = `Ma guitare : ${resultat.data.guitare}`;
        elSpanEmploi.textContent = `Emploi : ${resultat.data.emploi}`;
        elSpanVille.textContent = `Ville : ${resultat.data.ville}`;

        //on met à jour les variables déclarées au début du code
        //Si besoins, elles nous serviront pour mettre à jours les inputs 
        guitare = resultat.data.guitare;
        emploi = resultat.data.emploi;
        ville = resultat.data.ville;

        elDivAbout.style.display = "flex";

        elAlertAbout.style.backgroundColor = "#18AA50";
        elAlertAbout.textContent = resultat.message;
        setTimeout(() => {
            elAlertAbout.style.backgroundColor = "";
            elAlertAbout.textContent = "";
        }, 5000);


    } catch (error) {
        if (error.message === "expired token") {
            window.location.href = "http://localhost/projet-forum/accueil";
        } else {
            elDivFormAbout.style.display = "none";
            elDivAbout.style.display = "flex";
            elAlertAbout.style.backgroundColor = "#FF4242";
            elAlertAbout.textContent = error.message;
            setTimeout(() => {
                elAlertAbout.style.backgroundColor = "";
                elAlertAbout.textContent = "";
            }, 5000);
        }



    }
})

