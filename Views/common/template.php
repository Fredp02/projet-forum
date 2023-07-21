<?php


use Controllers\Services\Securite; ?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="Forum de discussion autour de la guitare" />
    <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@400;700&family=Roboto:wght@400;700&display=swap"
        rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
        integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="<?= $quillSnowCSS ?? ''; ?>" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="<?= $quillImageCSS ?? ''; ?>">
    <link rel="stylesheet" type="text/css" href="<?= $quillEmojiCSS ?? ''; ?>">
    <link rel="stylesheet" href="/style/mainStyle.css" />
    <link rel="stylesheet" href="<?= $css ?? ''; ?>" />
    <script src="/js/app.js" defer></script>

    <script src="<?= $quillJS ?? ''; ?>" defer></script>
    <script src="<?= $quillImageJS ?? ''; ?>" defer></script>
    <script src="<?= $quillEmojiJS ?? ''; ?>" defer></script>
    <script src="<?= $script ?? ''; ?>" defer></script>
    <title><?= htmlspecialchars($pageTitle); ?></title>
</head>

<body>
    <header>
        <div class="banner"></div>

        <nav class="navPosition">
            <?php if (!empty($_SESSION['alert'])) : ?>
            <?php $i = 0; // variable pour compter les messages 
                ?>
            <?php foreach ($_SESSION['alert'] as $alert) : ?>
            <div class="blocMessage <?= htmlspecialchars($alert['couleur']) ?>" id="message<?= htmlspecialchars($i) ?>">
                <div class="textMessageBanner">
                    <p><?= htmlspecialchars($alert['message']) ?></p>
                </div>
            </div>

            <?php $i++; // on incrémente le compteur 
                    ?>
            <?php endforeach; ?>
            <?php endif;
            unset($_SESSION['alert']);
            ?>
            <div class="conteneurNav">
                <a href="index.php" class="headerTitle">
                    <div class="textHeaderTitle">
                        <span>Guitare</span><span>forum</span>
                    </div>
                </a>
                <div class="search">
                    <input type="text" name="inputSearch" class="inputSearch" placeholder="Rechercher..." />
                </div>
                <div class="headerLinks">
                    <?php if (!Securite::isConnected()) : ?>
                    <a href="?controller=login" class="linkLogin">Connexion</a>
                    <?php else : ?>
                    <a href="?controller=account" class="linkProfil">
                        <?= htmlspecialchars($_SESSION['profil']['pseudo']); ?></a>
                    <a href="?controller=logout" class="logout">Déconnexion</a>
                    <?php endif; ?>

                </div>
            </div>
        </nav>
    </header>

    <div class="container">


        <div class="forumContent">
            <?= $page_content; ?>
        </div>


        <aside class="aside">
            <div class="loginAside">
                <?php if (!Securite::isConnected()) : ?>
                <div class="headerLogin">
                    <h2>Se connecter</h2>
                    <div class="loginMessage">
                        <p class="textMessageAside"></p>
                    </div>
                </div>

                <form method="POST" class="formLogin">
                    <div class="">
                        <input type="text" class="inputLogin" name="pseudo" placeholder="Pseudo" required />
                    </div>
                    <div class="">
                        <input type="password" class="inputPassword" name="password" placeholder="Mot de passe"
                            required />
                    </div>
                    <div>
                        <input type="hidden" class="tokenCSRF" name="tokenCSRF"
                            value="<?= $_SESSION['tokenCSRF']; ?>" />
                    </div>

                    <button class="btnLogin">Connexion</button>
                </form>
                <a href="?controller=forgotPass" class="linkForgot">Mot de passe oublié ?</a>

                <div class="footerLogin">

                    <a href="index.php?controller=register">Créer un compte</a>
                </div>
                <?php else : ?>
                <div class="headerLogin">
                    <h2><?= $_SESSION['profil']['pseudo']; ?></h2>
                </div>

                <div class="divAvatar">
                    <img src="/images/profils/<?= $_SESSION['profil']['filepathAvatar']; ?>"
                        alt="photo de profil de l'utilisateur">
                </div>
                <a href="?controller=account" class="linkProfil">Mon profil</a>

                <div class="footerLogin">

                    <a href="?controller=logout" class="logout">Déconnexion</a>
                </div>
                <?php endif; ?>

                <!-- </div> -->
            </div>


        </aside>
    </div>

    <footer>
        <div class="contentFooter">
            <div class="footerNav">
                <ul>
                    <li>Accueil</li>
                    <li>A propos</li>
                    <li>Condition d'utilisation</li>
                    <li>Politique de confidentialité</li>
                    <li>Contacter l'administrateur</li>
                </ul>
            </div>
            <div class="footerStat">
                <div>Qui est en ligne</div>
            </div>
            <div class="footerSocial">logos</div>
        </div>
    </footer>
</body>

</html>