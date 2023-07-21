const formPageLogin = document.querySelector('.formPageLogin')
const messagePagelogin = document.querySelector('.messagePagelogin')
const inputPageLogin = document.querySelector('.inputPageLogin')
const inputPasswordPageLogin = document.querySelector('.inputPasswordPageLogin')
const btnPageLogin = document.querySelector('.btnPageLogin')


inputPageLogin.addEventListener("keyup", deleteAlertLogin);
inputPasswordPageLogin.addEventListener("keyup", deleteAlertLogin);



btnPageLogin.addEventListener('click', async (e) => {
    e.preventDefault();
    if (validFormPageLogin()) {

        const formDataPageLogin = new FormData(formPageLogin);
        try {

            // envoi des données au serveur avec la méthode POST
            const response = await fetch("index.php?controller=login&action=validationlogin", {
                method: "POST",
                body: formDataPageLogin,
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
            //Si true, on redirige vers l'url avant la connexion
            // console.log(resultat.data.previousURL);
            window.location = resultat.data.previousURL;
        } catch (error) {
            //on affiche un message d'erreur dans le DOM
            if (error.message === "expired token") {
                window.location.href = "index.php";
            } else {
                messagePagelogin.innerHTML = error.message;
                inputPageLogin.value = "";
                inputPasswordPageLogin.value = "";
            }

        }
    }
})



function validFormPageLogin() {
    // elInputLogin 
    // elInputPassword
    if (inputPageLogin.value === "") {
        messagePagelogin.textContent = "Champ pseudo requis";
        return false;
    }
    if (inputPasswordPageLogin.value === "") {
        console.log('salut')
        messagePagelogin.textContent = "Champ password requis";
        return false;
    }
    return true;
}


function deleteAlertLogin() {
    messagePagelogin.textContent = "";
}