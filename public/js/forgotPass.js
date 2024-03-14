const elFormForgot = document.querySelector('.formForgot');
// const elForgot = document.querySelector('.forgot');
// const elLinkforgot = document.querySelector('.linkForgot');
const elLabelForgot = document.querySelector('.emailLabelForgot');
const elInputForgot = document.querySelector('.inputEmailForgot');
const elBtnForgot = document.querySelector('.btnForgot');
const regexMail = /^\w+([-+.']\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/;

// MOT DE PASSE OUBLIÉ
elBtnForgot.addEventListener('click', async (e) => {
    e.preventDefault();
    if (ckeckFormEmail()) {

        const formData = new FormData(elFormForgot);
        try {
            // envoi des données au serveur avec la méthode POST
            const response = await fetch("?controller=forgotPass&action=sendEmail", {
                method: "POST",
                body: formData,
            });
            // vérif du statut de la réponse
            // const resultat = await verifFetch(response);
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
            //Si true, on redirige
            elLoginMessage.style.display = 'block'
            window.location = "index.php";

        } catch (error) {
            //on affiche un message d'erreur dans le DOM
            if (error.message === "expired token") {
                window.location = "index.php";
            } else {
                elInputForgot.classList.add('inputError');
                elLabelForgot.style.backgroundColor = "#FF4242";
                elLabelForgot.textContent = error.message;
                elInputForgot.value = "";
            }


        }

    }

})




function ckeckFormEmail() {
    if (elInputForgot.value === "") {
        elInputForgot.classList.add('inputError');
        elLabelForgot.style.backgroundColor = "#FF4242";
        elLabelForgot.textContent = "Le champs est vide";
        elInputForgot.value = "";
        return false;
    }
    if (!elInputForgot.value.match(regexMail)) {
        elInputForgot.classList.add('inputError');
        elLabelForgot.style.backgroundColor = "#FF4242";
        elLabelForgot.textContent = "Format de l'adresse incorrecte";
        elInputForgot.value = "";
        return false;
    }
    return true;
}

elInputForgot.addEventListener("input", inputDefault);

function inputDefault() {
    this.classList.remove("inputError");
    this.previousElementSibling.style.backgroundColor = "";
    this.previousElementSibling.textContent = "Entrez votre email : exemple@domaine.com";


}