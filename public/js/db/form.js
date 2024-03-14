const elButton = document.querySelector('.btnForm');
const elName = document.querySelector('.name');
const elDescription = document.querySelector('.description');
const elCheckbox = document.querySelector('.form-check-input');
const elSelect = document.querySelector('.form-select');
const elForm = document.querySelector('.dbCategoriesList form');
const elAlertForm = document.querySelector('.alertForm');

// console.log(window.location.search)
// let url = '';
// if (window.location.search.includes('categoryAdd')){
//     url = "?controller=dashboard&action=categoryAdd"
// }else if (window.location.search.includes('categoryEdit')){
//     url = "?controller=dashboard&action=categoryEdit"
// }

elButton.addEventListener('click', async (e) => {

    e.preventDefault();
    // if (elName.value === "") elInputEmploi.value = "Non renseigné";
    // if (elDescription.value === "") elInputGuitare.value = "Non renseigné";

    if(validateForm()){
        const form = new FormData(elForm);
        try {
            const response = await fetch(window.location.search, {
                method: "POST",
                body: form,
            });
            if (!response.ok) throw new Error(`Une erreur est survenue: ${response.status}`);
            const resultat = await response.json();
            if (!resultat.boolean) throw new Error(resultat.message);

            window.location = '?controller=dashboard&action=categoriesListShow';


        } catch (error) {
            if (error.message === "expired token") {
                window.location.href = 'index.php';
            } else {
                elAlertForm.style.backgroundColor = "#FF4242";
                elAlertForm.textContent = error.message;
                setTimeout(() => {
                    elAlertForm.style.backgroundColor = "";
                    elAlertForm.textContent = "";
                }, 8000);
            }
        }
    }
})

function validateForm() {

    //Vérification du pseudo
    if (elName.value === "") {
        elName.classList.add('is-invalid');
        return false;
    }
    //Vérification de l'email
    if (elDescription.value === "") {
        elDescription.classList.add('is-invalid');
        return false;
    }
    if(elSelect.value === '' && !elCheckbox.checked){
        elSelect.classList.add('is-invalid');
        return false;
    }
    return true
}

elCheckbox.addEventListener('change', function() {
    // Vérifiez si la case à cocher est cochée
    if (elCheckbox.checked) {
        elSelect.setAttribute('disabled', true);
        elSelect.style.opacity = "0.3";
        // Faites quelque chose lorsque la case est cochée (true)
    } else {
        elSelect.removeAttribute('disabled');
        elSelect.style.opacity = "1";    }
});