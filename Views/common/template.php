<?php


use Controllers\Services\Securite; ?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="Forum de discussion autour de la guitare" />
    <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@400;700&family=Roboto:wght@400;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="<?= $quillSnowCSS ?? ''; ?>" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="<?= $quillImageCSS ?? ''; ?>">
    <link rel="stylesheet" type="text/css" href="<?= $quillEmojiCSS ?? ''; ?>">
    <link rel="stylesheet" href="<?= $bootstrapCSS ?? '' ?>" />
    <link rel="stylesheet" href="./style/mainStyle.css" />
    <link rel="stylesheet" href="<?= $css ?? '' ?>" />

    <script src="./js/app.js" defer></script>

    <script src="<?= $quillJS ?? ''; ?>" defer></script>
    <script src="<?= $quillImageJS ?? ''; ?>" defer></script>
    <script src="<?= $quillEmojiJS ?? ''; ?>" defer></script>
    <script type="module" src="<?= $script ?? ''; ?>"></script>
    <script src="<?= $bootstrapJS ?? '' ?>"></script>
    <title><?= htmlspecialchars($pageTitle); ?></title>
</head>

<body>
    <header>
        <div class="banner"></div>
        <?php if (!empty($_SESSION['alert'])) : ?>
            <div class="blocMessage <?= Securite::htmlPurifier($_SESSION['alert']['couleur']) ?>" id="message">
                <div class="textMessageBanner">
                    <p><?= Securite::htmlPurifier($_SESSION['alert']['message']) ?></p>
                </div>
            </div>
        <?php endif;
        unset($_SESSION['alert']);
        ?>
        <nav class="navPosition">

            <div class="conteneurNav">
                <a href="index.php" class="headerTitle">
                    <div class="textHeaderTitle">
                        <span>Guitare</span><span>forum</span>
                    </div>
                </a>
                <div class="search">
                    <form action="?controller=search" method="POST">
                        <input type="text" name="keywords" class="inputSearch" placeholder="Rechercher..." />
                        <button class="button" type="submit">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </button>

                        <!-- <a href="index.php?controller=seach">
                            <button class="button">
                                <i class="fa-solid fa-gear"></i>
                            </button>
                        </a> -->
                        <input type="hidden" name="tokenCSRF" value="<?= $tokenCSRF; ?>" />
                    </form>
                </div>

                <div class="headerLinks">
                    <?php if (!Securite::isConnected()) : ?>
                        <a href="?controller=login" class="linkLogin">Connexion</a>
                    <?php else : ?>
                        <a href="?controller=account" class="linkProfil">
                            <?= htmlspecialchars($_SESSION['profil']['pseudo']); ?></a>
                        <?php if ($_SESSION['profil']['roleName'] === 'Administrateur') : ?>
                        <a href="?controller=dashboard" class="logout">DASHBOARD</a>
                        <?php endif; ?>
                        <a href="?controller=logout" class="logout">Déconnexion</a>
                    <?php endif; ?>

                </div>
            </div>
        </nav>
    </header>

    <div class="conteneur">


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
                            <input type="password" class="inputPassword" name="password" placeholder="Mot de passe" required />
                        </div>
                        <div>
                            <input type="hidden" class="tokenCSRF" name="tokenCSRF" value="<?= $tokenCSRF; ?>" />
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
                        <img src="./images/profils/<?= $_SESSION['profil']['filepathAvatar']; ?>" alt="photo de profil de l'utilisateur">
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