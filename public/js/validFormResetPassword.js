const elBtnReset = document.querySelector('.btnResetPassword');
const elNouveauPassword = document.querySelector('.nouveauPassword');
const elConfirmPassword = document.querySelector('.confirmPassword');
const elFormPassword = document.querySelector('.formPassword');
const elAlertPassword = document.querySelector('.alertPassword');

const regexPassword = /^(?=.*[A-Z])(?=.*[!@#$%^&*()_+\-=\[\]{};':\"\\|,.<>\/?].*[!@#$%^&*()_+\-=\[\]{};':\"\\|,.<>\/?])[a-zA-Z0-9!@#$%^&*()_+\-=\[\]{};':\"\\|,.<>\/?]{8,50}$/;

elNouveauPassword.addEventListener("input", inputDefault);
elConfirmPassword.addEventListener("input", inputDefault);

// elBtnReset.addEventListener('click', async (e) => {
//     e.preventDefault();
//     if (validateFormReset()) {
//         const formData = new FormData(elFormPassword);

//         try {
//             // envoi des données au serveur avec la méthode POST
//             const response = await fetch("validationInscription", {
//                 method: "POST",
//                 body: formData,
//             });
//             if (!response.ok) {
//                 //si erreur detecté, on génère un nouveau message d'erreur.
//                 // le "catch" va le récupérer.
//                 throw new Error(`Une erreur est survenue: ${response.status}`);
//             }
//             //si la communication c'est bien déroulée , on traite les donnée json
//             const resultat = await response.json();

//             //si dans ces données, on à un booléen à false, alors on affiche son message
//             //Il est "false" si les identifiants sont incorrectes ou si le compte est inactif
//             if (!resultat.boolean) {
//                 throw new Error(resultat.message);
//             }
//             window.location.href = "accueil";

//         } catch (error) {
//             console.log(error);
//             elAlertPassword.style.backgroundColor = "#FF4242";
//             elAlertPassword.textContent = error.message;
//             setTimeout(() => {
//                 elAlertPassword.style.backgroundColor = "";
//                 elAlertPassword.textContent = "";
//             }, 5000);


//         }
//     }
// })
function validateFormReset() {

    //Vérification du password
    if (elNouveauPassword.value === "") {
        elNouveauPassword.classList.add('inputError');
        elNouveauPassword.previousElementSibling.style.color = "#FF4242";
        elNouveauPassword.previousElementSibling.textContent = "Ce champs est requis";
        return false;
    }
    if (!elNouveauPassword.value.match(regexPassword)) {
        elNouveauPassword.classList.add('inputError');
        elNouveauPassword.previousElementSibling.style.color = "#FF4242";
        elNouveauPassword.previousElementSibling.textContent = "Le mot de passe doit contenir entre 8 et 50 caractères dont au moins 2 caractères spéciaux et une majuscule";
        return false;
    }
    if (elConfirmPassword.value === "") {
        elConfirmPassword.classList.add('inputError');
        elConfirmPassword.previousElementSibling.style.color = "#FF4242";
        elConfirmPassword.previousElementSibling.textContent = "Ce champs est requis";
        return false;
    }
    if (elNouveauPassword.value !== elConfirmPassword.value) {
        elConfirmPassword.classList.add('inputError');
        elConfirmPassword.classList.add('inputError');
        elConfirmPassword.previousElementSibling.style.color = "#FF4242";
        elConfirmPassword.previousElementSibling.textContent = "Les mots de passe ne sont pas identiques";
        return false;
    }

    return true
}

function inputDefault() {

    this.classList.remove("inputError");
    switch (this.className) {
        case 'nouveauPassword':
            this.previousElementSibling.innerHTML = "Nouveau mot de passe : <br>(entre 8 et 50 caractères dont 2 caractères spéciaux et une majuscule) ";
            this.previousElementSibling.style.color = "#EEEEEE";
            this.placeholder = "Pseudo...";
            break;

        case 'confirmPassword':
            this.previousElementSibling.textContent = "Confirmer le mot de passe :";
            this.previousElementSibling.style.color = "#EEEEEE";
            this.placeholder = "Confirmez...";
            break;

    }
}

