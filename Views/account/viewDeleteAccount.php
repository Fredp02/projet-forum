<div class="suppression">
    <div class="contentSuppression">
        <h1>Supprimer mon compte</h1>
        <h3>Attention cette action est irréversible</h3>
        <form action="" method="post">
            <div>
                <input type="hidden" class="aboutCSRF" name="tokenCSRF" value="<?= $tokenCSRF; ?>">
            </div>

            <div>
                <button type="submit" class="btnSuppression">Supprimer
                    définitivement</button>
            </div>
        </form>
    </div>
</div>
<div class="cancelSupp">
    <a href="?controller=account"><button class="btnCancel">Annuler</button></a>
</div>