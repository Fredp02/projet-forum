const elPseudo = document.querySelector(".inputPseudoInscription");
const elEmail = document.querySelector(".inputEmailInscription");
const elPassword = document.querySelector(".inputPasswordInscription");
const elConfirmPassword = document.querySelector(".inputConfirmPassword");
const elPseudoLabel = document.querySelector(".pseudoLabel");
const elEmailLabel = document.querySelector(".emailLabel");
const elPasswordLabel = document.querySelector(".passwordLabel");
const elCheckbox = document.querySelector("#accept");
const elAlertAccept = document.querySelector(".alertAccept");
const elBtnInscription = document.querySelector(".btnInscription");
const elForm = document.querySelector(".formInscription");
const elMessageInscription = document.querySelector(".messageInscription");
const regexMail = /^\w+([-+.']\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/;
const regexPseudo = /^[a-zA-Z0-9éèêëàâäôöûüçî ]+$/;
const regexPassword = /^(?=.*[A-Z])(?=.*[!@#$%^&*()_+\-=\[\]{};':\"\\|,.<>\/?].*[!@#$%^&*()_+\-=\[\]{};':\"\\|,.<>\/?])[a-zA-Z0-9!@#$%^&*()_+\-=\[\]{};':\"\\|,.<>\/?]{8,50}$/;


// const regexPassword = /^(?=(?:[^!@#$%^&*()_+\-=\[\]{};':\"\\|,.<>\/?]*[!@#$%^&*()_+\-=\[\]{};':\"\\|,.<>\/?]){2}[^!@#$%^&*()_+\-=\[\]{};':\"\\|,.<>\/?]*$)[a-zA-Z0-9!@#$%^&*()_+\-=\[\]{};':\"\\|,.<>\/?]{8,50}$/g


elPseudo.addEventListener("keyup", inputDefault);
elEmail.addEventListener("keyup", inputDefault);
elPassword.addEventListener("keyup", inputDefault);
elConfirmPassword.addEventListener("keyup", inputDefault);
elCheckbox.addEventListener("change", inputDefault);



elBtnInscription.addEventListener('click', async () => {

    if (validateFormInscription()) {
        let dataTypeError;
        const formData = new FormData(elForm);

        // // Créer un objet URLSearchParams à partir de FormData
        // let params = new URLSearchParams(formData);

        // // Parcourir les clés et les valeurs de params
        // for (let [key, value] of params) {
        //     // Décoder la valeur avec decodeURIComponent
        //     value = decodeURIComponent(value);
        //     // Remplacer la valeur dans params
        //     params.set(key, value);
        // }

        try {
            // envoi des données au serveur avec la méthode POST
            const response = await fetch(`${URL_WEBSITE}register/sendMail`, {
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
                dataTypeError = resultat.data;
                throw new Error(resultat.message);

            }
            window.location.href = `${URL_WEBSITE}home`;

        } catch (error) {
            console.log(error);
            if (error.message === "expired token") {
                window.location.href = "/projet-forum/home";
            } else if (dataTypeError === 'pseudo') {
                elPseudo.classList.add('inputError');
                elPseudoLabel.textContent = error.message;
                elPseudoLabel.style.color = "#FF4242";
            } else if (dataTypeError === 'password') {
                elPassword.classList.add('inputError');
                elPasswordLabel.textContent = error.message;
                elPasswordLabel.style.color = "#FF4242";
            } else if (dataTypeError === 'email') {
                elEmail.classList.add('inputError');
                elEmailLabel.textContent = error.message;
                elEmailLabel.style.color = "#FF4242";
            } else {
                // faire un redirection ? ajouter un message ?
                console.log(error.message);
            }


        }
    }

})



function validateFormInscription() {

    //Vérification du pseudo
    if (elPseudo.value === "") {
        elPseudo.classList.add('inputError');
        elPseudo.placeholder = "Ce champs est requis";
        return false;
    }
    if (elPseudo.value.length < 4 || elPseudo.value.length > 50) {
        elPseudo.classList.add('inputError');
        elPseudoLabel.textContent = "Votre pseudo doit contenir  entre 4 et 50 caractères ";
        elPseudoLabel.style.color = "#FF4242";
        return false;
    }
    if (!elPseudo.value.match(regexPseudo)) {
        elPseudo.classList.add('inputError');
        elPseudoLabel.textContent = "Votre pseudo ne doit pas contenir de caractères spéciaux";
        elPseudoLabel.style.color = "#FF4242";
        return false;
    }
    //Vérification de l'email
    if (elEmail.value === "") {
        elEmail.classList.add('inputError');
        elEmail.placeholder = "Ce champs est requis";
        return false;
    }
    if (!elEmail.value.match(regexMail)) {
        elEmail.classList.add('inputError');
        elEmailLabel.textContent = "Format d'email incorrect";
        elEmailLabel.style.color = "#FF4242";
        return false;
    }
    //Vérification du password
    if (elPassword.value === "") {
        elPassword.classList.add('inputError');
        elPasswordLabel.style.color = "#FF4242";
        elPasswordLabel.textContent = "Ce champs est requis";
        return false;
    }
    if (!elPassword.value.match(regexPassword)) {
        elPassword.classList.add('inputError');
        elPasswordLabel.style.color = "#FF4242";
        elPasswordLabel.textContent = "Le mot de passe doit contenir entre 8 et 50 caractères dont au moins 2 caractères spéciaux et une majuscule";
        return false;
    }
    if (elPassword.value !== elConfirmPassword.value) {
        elPassword.classList.add('inputError');
        elConfirmPassword.classList.add('inputError');
        elPasswordLabel.style.color = "#FF4242";
        elPasswordLabel.textContent = "Les mots de passe ne sont pas identiques";
        return false;
    }
    //Vérification de la checkbox
    if (!elCheckbox.checked) {
        elAlertAccept.style.visibility = "visible";
        return false;
    }
    return true
}
function inputDefault() {

    this.classList.remove("inputError");
    switch (this.className) {
        case 'inputPseudoInscription':
            elPseudoLabel.textContent = "Pseudo (entre 4 et 50 cacartères)";
            elPseudoLabel.style.color = "#EEEEEE";
            elPseudo.placeholder = "Pseudo...";
            break;

        case 'inputEmailInscription':
            elEmailLabel.textContent = "Email : exemple@domaine.com";
            elEmailLabel.style.color = "#EEEEEE";
            elEmail.placeholder = "Email...";
            break;

        case 'inputPasswordInscription':
            elConfirmPassword.classList.remove("inputError");
            elPasswordLabel.textContent = "Mot de passe (entre 8 et 50 caractères dont 2 caractères spéciaux et une majuscule)";
            elPasswordLabel.style.color = "#EEEEEE";
            elPassword.placeholder = "Mot de passe...";
            break;
        case 'inputConfirmPassword':
            elPassword.classList.remove("inputError");
            elPasswordLabel.textContent = "Mot de passe (entre 8 et 50 caractères dont 2 caractères spéciaux et une majuscule)";
            elPasswordLabel.style.color = "#EEEEEE";
            elPassword.placeholder = "Mot de passe...";
            break;
        case 'accept':
            elAlertAccept.style.visibility = "hidden";
            break;

        default:
            break;
    }
}