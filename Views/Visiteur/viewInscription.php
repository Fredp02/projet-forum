<div class="inscription">
    <div class="contentInscription">
        <div class="topContentInscription">
            <h1>Inscription</h1>

            <!-- <div class="messageInscription rouge">
                <p>Pseudo déjà utilisé</p>
            </div> -->
        </div>

        <!-- onsubmit="return validateFormInscription()" -->
        <form class="formInscription">
            <div class="">
                <label for="pseudo" class="pseudoLabel">Pseudo (entre 4 et 50 cacartères)</label>
                <input type="text" class="inputPseudoInscription" id="pseudo" name="pseudo" placeholder="Pseudo..."
                    require>
            </div>
            <div class="">
                <label for="emailInscription" class="emailLabel">Email : exemple@domaine.com</label>
                <input type="email" class="inputEmailInscription" id="emailInscription" name="emailInscription"
                    placeholder="Email..." require>
            </div>
            <div class="">
                <label for="password" class="passwordLabel">Mot de passe (entre 8 et 50 caractères dont 2 caractères
                    spéciaux et une majuscule)</label>
                <input type="password" class="inputPasswordInscription" id="password" name="password"
                    placeholder="Mot de passe..." require>
            </div>
            <div class="">
                <label for="confirmPassword" class="passwordLabel">Confirmez votre mot de passe</label>
                <input type="password" class="inputConfirmPassword" id="confirmPassword" name="confirmPassword"
                    placeholder="Mot de passe..." require>
            </div>
            <div class="">
                <div class="alertAccept">
                    <span>Vous devez accepter les conditions générale d'utilisation</span>
                </div>

                <input type="checkbox" class="accept" id="accept" name="accept">
                <label for="accept" class="acceptLabel">Je déclare avoir pris connaissance et accepter les
                    conditions
                    générales
                    d'utilisation de Guitare Forum *</label>
            </div>


        </form>
        <button class="btnInscription">Inscription</button>

    </div>

</div>