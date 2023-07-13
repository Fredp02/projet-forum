<div class="reset">

    <div class="contentReset">
        <div class="alertPassword"></div>
        <h1>Réinitialisation du mot de passe</h1>
        <div class="divFormPassword">
            <form method="POST" class="formPassword" action="<?= URL ?>validationResetPassword/<?= $jwt ?>"
                onsubmit="return validateFormReset()">

                <div class="divNouveauPassword">
                    <label for="nouveauPassword">Nouveau mot de passe : <br>(entre 8 et 50 caractères dont 2 caractères
                        spéciaux et une majuscule)</label>
                    <input type="password" name="nouveauPassword" id="nouveauPassword" class="nouveauPassword"
                        placeholder="Mot de passe...">
                </div>
                <div class="divConfirmPassword">
                    <label for="confirmPassword">Confirmer le mot de passe :</label>
                    <input type="password" name="confirmPassword" id="confirmPassword" class="confirmPassword"
                        placeholder="Confirmez...">
                </div>
                <div>
                    <input type="hidden" name="tokenCSRF" value="<?= $tokenCSRF;  ?>" />
                </div>

                <div class="divBtnPassword">
                    <button type="submit" class="btnResetPassword">Valider</button>

                </div>

            </form>

        </div>
    </div>
</div>