<div class="forgot">
    <h1>Mot de passe oublié</h1>
    <!-- onsubmit="return ckeckFormEmail()" -->
    <form action="" class="formForgot">
        <label for="emailPasswordForgot" class="emailLabelForgot">Entrez votre email :
            exemple@domaine.com</label>
        <input type="email" class="inputEmailForgot" id="emailPasswordForgot" name="passwordForgot"
            placeholder="Email..." require>
        <div>
            <input type="hidden" name="tokenCSRF" value="<?= $tokenCSRF;  ?>" />
        </div>
        <button class="btnForgot">Recevoir un email</button>
    </form>

</div>