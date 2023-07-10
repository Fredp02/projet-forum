<?php
$avatar = $userDatas['userID'] . '/' . $userDatas['avatar'];
?>
<div class="profil">
    <div class="contentFormAvatar">
        <div class="divFormAvatar">
            <label for="formAvatar">Personnaliser ma photo</label>
            <div>
                <p>
                    Fichiers acceptés : JPG, PNG, GIF. 200x200 pixels maximum, 150Ko maximum
                </p>
            </div>
            <div class="alerteAvatar">
                <span></span>
            </div>
            <form class="formulaireAvatar" method="post" enctype="multipart/form-data">
                <input type="file" name="avatarPhoto" id="formAvatar">
                <input type="hidden" name="tokenCSRF" value="<?= $tokenCSRF; ?>">
            </form>
            <button class="hideDivFormAvatar">Annuler</button>
        </div>
    </div>

    <h1><?= htmlspecialchars($userDatas['pseudo']); ?></h1>
    <div class="contentAvatar">
        <div class="divAvatar">
            <img src="../images/profils/<?= $avatar; ?>" alt="photo de profil de l'utilisateur">
            <div>
                <p>Modifier</p>
            </div>
        </div>
    </div>
    <p class="textDateInscription">Inscrit le <?= htmlspecialchars($userDatas['userDate']); ?></p>
    <p class="textRang">Rang : <?= htmlspecialchars($userDatas['role']); ?></p>

    <div class="contentInfos">
        <div class="identifiants">
            <div class="alertIdentifiants"></div>
            <h3>Mes identifiants</h3>
            <div class="contentEmail">
                <div class="divEmail">
                    <div>
                        <span class="spanEmail">Email : <?= htmlspecialchars($userDatas['email']); ?></span>
                    </div>
                    <div>
                        <button class="displayEditEmail">Editer</button>
                    </div>

                </div>
                <div class="divFormEmail">
                    <form class="formEmail" action="">
                        <div class="divInputEmail">
                            <label for="mail">Mon Email :</label>
                            <input type="email" id="mail" name="email" class="inputEmail"
                                value="<?= ($userDatas['email']); ?>">
                        </div>
                        <div>
                            <input type="hidden" name="tokenCSRF" value="<?= $tokenCSRF; ?>">
                        </div>
                        <div class="divBtnEmail">
                            <button class="btnEditEmail">Modifier</button>
                            <button class="cancelEditEmail">Annuler</button>
                        </div>


                    </form>

                </div>

            </div>
            <div class="contentPassword">
                <div class="divPassword">
                    <div>
                        <span class="spanPassword">Mot de passe : *******</span>
                    </div>
                    <div>
                        <button class="displayEditPassword">Editer</button>
                    </div>


                </div>
                <div class="divFormPassword">
                    <form class="formPassword" action="">
                        <div class="divAncienPassword">
                            <label for="ancienPassword">Ancien mot de passe :</label>
                            <input type="password" name="ancienPassword" id="ancienPassword">
                        </div>
                        <div class="divNouveauPassword">
                            <label for="nouveauPassword">Nouveau mot de passe :</label>
                            <input type="password" name="nouveauPassword" id="nouveauPassword">
                        </div>
                        <div class="divConfirmPassword">
                            <label for="confirmPassword">Confirmer le mot de passe :</label>
                            <input type="password" name="confirmPassword" id="confirmPassword">
                        </div>
                        <div>
                            <input type="hidden" name="tokenCSRF" value="<?= $tokenCSRF; ?>">
                        </div>
                        <div class="divBtnPassword">
                            <button type="submit" class="btnEditPassword">Modifier</button>
                            <button class="cancelEditPassword">Annuler</button>
                        </div>

                    </form>

                </div>
            </div>
        </div>
        <div class="about">

            <div class="alertAbout"></div>

            <div class="divAbout">
                <div class="topAbout">
                    <h3>A propos de moi</h3>
                    <button class="displayEditAbout">Editer</button>
                </div>
                <div class="divMaGuitare">
                    <span class="spanGuitare">Ma guitare : <?= ($userDatas['guitare']) ?? ""; ?></span>
                </div>
                <div class="divEmploi">
                    <span class="spanEmploi">Emploi : <?= ($userDatas['emploi']) ?? ""; ?></span>
                </div>
                <div class="divVille">
                    <span class="spanVille">Ville : <?= ($userDatas['ville']) ?? ""; ?></span>
                </div>


            </div>


            <div class="divFormAbout">
                <h3>A propos de moi</h3>
                <form class="formAbout" action="">
                    <div class="divInputGuitare">
                        <label for="guitare">Ma guitare :</label>
                        <input type="text" id="guitare" name="guitare" class="inputGuitare"
                            value="<?= ($userDatas['guitare']) ?? ""; ?>" />
                    </div>
                    <div class="divInputEmploi">
                        <label for="emploi">Emploi :</label>
                        <input type="text" id="emploi" name="emploi" class="inputEmploi"
                            value="<?= ($userDatas['emploi']) ?? ""; ?>" />
                    </div>
                    <div class="divInputVille">
                        <label for="ville">Ville :</label>
                        <input type="text" id="ville" name="ville" class="inputVille"
                            value="<?= ($userDatas['ville']) ?? ""; ?>" />
                    </div>
                    <div>
                        <input type="hidden" name="tokenCSRF" value="<?= $tokenCSRF; ?>">
                    </div>
                    <div class="divBtnAbout">
                        <button class="btnEditAbout">Modifier</button>
                        <button class="cancelEditAbout">Annuler</button>
                    </div>

                </form>
            </div>
        </div>

    </div>
    <a href="<?= URL; ?>compte/supprimerCompte"><button class="btnDeleteAccount">Supprimer mon compte</button></a>



</div>