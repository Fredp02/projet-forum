const nav = document.querySelector('nav');
const elLogin = document.querySelector('.loginAside');
const elLinkLogin = document.querySelector('.linkLogin');
const elBtnLogin = document.querySelector('.btnLogin');
const elFormLogin = document.querySelector('.formLogin');
const elLoginMessage = document.querySelector('.loginMessage');
const elTextMessage = document.querySelector('.textMessage');
const elHeaderLinks = document.querySelector('.headerLinks');
const elInputLogin = document.querySelector('.inputLogin');
const elInputPassword = document.querySelector('.inputPassword');
const elContentLogin = document.querySelector('.contentLogin');




window.addEventListener("scroll", () => {
    //hauteur des deux éléments
    headerHeight = document.querySelector('header').offsetHeight
    navHeight = document.querySelector('nav').offsetHeight

    window.pageYOffset >= (headerHeight - navHeight) ?
        nav.classList.replace('navPosition', 'fixed') : nav.classList.replace('fixed', 'navPosition');
});

if (elBtnLogin) {
    elInputLogin.addEventListener("keyup", deleteAlertLogin);
    elInputPassword.addEventListener("keyup", deleteAlertLogin);

    elBtnLogin.addEventListener('click', async (e) => {
        if (checkLoginValidity()) {
            e.preventDefault();
            const formData = new FormData(elFormLogin);
            try {
                // envoi des données au serveur avec la méthode POST
                const response = await fetch("/projet-forum/validationLogin", {
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
                //Si true, on met à jour le DOM
                updateDom(resultat);
            } catch (error) {
                //on affiche un message d'erreur dans le DOM
                console.log(error)
                elLoginMessage.style.display = 'block'
                elLoginMessage.classList.add('rouge');
                elInputLogin.value = "";
                elInputPassword.value = "";
                elTextMessage.innerHTML = error.message;
            }
        }
    })
}
function updateDom(resultat) {
    elLogin.innerHTML = "";
    console.log(resultat.data.pseudo);

    const divHeaderLogin = document.createElement('div');
    divHeaderLogin.classList.add("headerLogin");
    const h2 = document.createElement('h2');
    h2.textContent = resultat.data.pseudo;
    divHeaderLogin.append(h2);

    const divAvatar = document.createElement('div');
    divAvatar.classList.add("divAvatar");
    divAvatar.innerHTML = `<img src="/projet-forum/public/images/profils/${resultat.data.filepathAvatar}" alt="photo de profil de l'utilisateur">`;

    const lienProfil = document.createElement('a');
    lienProfil.classList.add("linkProfil");
    lienProfil.setAttribute("href", "/projet-forum/compte/profil");
    lienProfil.textContent = "Mon profil";

    const divFooterLogin = document.createElement('div');
    divFooterLogin.classList.add("footerLogin");
    const lienlogout = document.createElement('a');
    lienlogout.classList.add("logout");
    lienlogout.setAttribute("href", "/projet-forum/deconnexion");
    lienlogout.textContent = "Déconnexion";
    divFooterLogin.append(lienlogout);

    elLogin.append(divHeaderLogin, divAvatar, lienProfil, divFooterLogin)

    // elHeaderLinks
    elHeaderLinks.innerHTML = "";
    const lienNavProfil = document.createElement('a');
    lienNavProfil.classList.add("linkProfil");
    lienNavProfil.setAttribute("href", "/projet-forum/compte/profil");
    lienNavProfil.textContent = resultat.data.pseudo;

    const lienNavlogout = document.createElement('a');
    lienNavlogout.classList.add("logout");
    lienNavlogout.setAttribute("href", "/projet-forum/deconnexion");
    lienNavlogout.textContent = "Déconnexion";
    elHeaderLinks.append(lienNavProfil, lienNavlogout);

}
let i = 0;
function hideMessage(i) {

    let message = document.getElementById("message" + i); // sélection de la div avec JavaScript pur
    if (message) { // vérification que l'élément existe
        message.classList.add("fade-out"); // ajout de la classe fade-out qui déclenche l'animation
        // ajout d'un écouteur d'événement qui se déclenche à la fin de l'animation
        message.addEventListener("animationend", function () {
            var parent = message.parentNode; // récupération de l'élément parent
            message.remove(); // suppression de l'élément enfant
        });
    }
}
// appel de la fonction avec un délai de 10 secondes
setTimeout(hideMessage, 5000, i); // utilisation de la fonction setTimeout qui exécute une fonction après un certain temps


function inputDefault() {
    elTextMessage.textContent = "";
    elLoginMessage.style.display = 'none'
    elLoginMessage.classList.remove('rouge');
    this.classList.remove("inputError");
    this.previousElementSibling.style.color = "";
}

function checkLoginValidity() {
    // elInputLogin 
    // elInputPassword
    if (elInputLogin.value === "") {
        elTextMessage.innerHTML = "Champ pseudo requis";
        alertLogin();
        return false;
    }
    if (elInputPassword.value === "") {
        elTextMessage.innerHTML = "Champ password requis";
        alertLogin();
        return false;
    }
    return true;


}

function alertLogin() {
    elLoginMessage.style.display = 'block'
    elLoginMessage.classList.add('rouge');
}

function deleteAlertLogin() {
    elLoginMessage.style.display = 'none'
    elLoginMessage.classList.remove('rouge');
}