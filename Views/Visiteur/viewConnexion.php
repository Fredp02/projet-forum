<div class="connexion">
    <div class="contentConnexion">
        <div class="headerPageLogin">
            <div>
                <h2>Se connecter</h2>
            </div>
            <div class="divMessagePagelogin">
                <p class="messagePagelogin"></p>
            </div>
        </div>
        <form method="POST" class="formPageLogin">
            <div class="">
                <input type="text" class="inputPageLogin" name="pseudo" placeholder="Pseudo" required />
            </div>
            <div class="">
                <input type="password" class="inputPasswordPageLogin" name="password" placeholder="Mot de passe" required />
            </div>
            <div class="">
                <input type="hidden" name="previousURL" value="<?= $_SERVER['HTTP_REFERER']; ?>" />
            </div>

            <div class="divBtnPageLogin">
                <button class="btnPageLogin">Connexion</button>
            </div>
        </form>
        <a href="forgot" class="linkForgotPageLogin">Mot de passe oublié ?</a>

        <div class="footerPageLogin">

            <a href="<?= URL . 'inscription'; ?>">Créer un compte</a>
        </div>

    </div>

</div>