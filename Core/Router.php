<?php

namespace Core;

use Exception;
use Controllers\HomeController;
use Controllers\LoginController;
use Controllers\TopicController;
use Controllers\LogoutController;
use Controllers\Services\Toolbox;
use Controllers\AccountController;
use Controllers\Services\Securite;
use Controllers\SousCatController;
use Controllers\RegisterController;
use Controllers\User\UserController;
use Controllers\ForgotPassController;
use Controllers\Visiteur\VisiteurController;

class Router
{
    private object $visiteurController;
    private object $userController;
    private object $homeController;
    private object $sousCatController;
    private object $topicController;
    private object $loginController;
    private object $logoutController;
    private object $forgotPassController;
    private object $registerController;
    private object $accountController;

    public function __construct()
    {
        $this->visiteurController = new VisiteurController();
        $this->userController = new UserController();
        $this->homeController = new HomeController();
        $this->sousCatController = new SousCatController();
        $this->topicController = new TopicController();
        $this->loginController = new LoginController();
        $this->logoutController = new LogoutController();
        $this->forgotPassController = new ForgotPassController();
        $this->registerController = new RegisterController();
        $this->accountController = new AccountController();
    }
    public function routes()
    {
        try {
            if (empty($_GET['page'])) {
                // dump('le get est vide');
                $page = "home";
            } else {

                /**
                 * Sinon c'est que l'url du site est de ce type : http://monsite/materiel/guitare-classique
                 * le $_GET['page'] faudra dans cet exemple "compte/profil".
                 * On fait donc un "explode" pour avoir ce tableau : 
                 * ['public''materiel', 'guitare-classique"]
                 * [   0    ,     1    ,       2   ]
                 * ['public''home']
                 * [   0    ,    1  ]
                 */
                $url = explode("/", filter_var($_GET['page'], FILTER_SANITIZE_URL));
                // dump($_GET['page']);
                $page = $url[1];
                // dump($page);
            }

            switch ($page) {

                case "home":
                    $this->homeController->home();
                    break;
                case "sousCat":
                    if (isset($url[2]) && !empty($url[2])) {
                        $this->sousCatController->sousCat($url[2]);
                    } else {
                        throw new Exception("La page n'existe pas");
                    }
                    break;
                case "topic":
                    if (isset($url[2]) && !empty($url[2])) {
                        $this->topicController->topic($url[2]);
                    } else {
                        throw new Exception("La page n'existe pas");
                    }
                    break;
                case  "login":
                    $this->loginController->login();
                    break;
                case "logout":
                    $this->logoutController->logout();
                    break;
                case "forgotPass":
                    $this->forgotPassController->forgotPass();
                    break;
                case "register":
                    $this->registerController->register();
                    break;
                case  "account":
                    if (isset($url[2]) && !empty($url[2])) {
                        $this->accountController->account($url[2]);
                    } else {
                        throw new Exception("La page n'existe pas");
                    }
                    break;



                    //! en cours de codage
                case 'createTopic':
                    if (Securite::isConnected()) {
                        $this->userController->createTopicView();
                    } else {
                        $this->visiteurController->connexionView();
                    }
                    break;



                case "uploadImage":
                    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0 && !empty($_POST['topicID'])) {
                        $this->userController->uploadImage($_FILES['image'], $_POST['topicID']);
                    } else {
                        echo 'erreur';
                        exit;
                    }
                    break;

                case "validationResponseSujet":
                    if (!empty($_POST['tokenCSRF']) && hash_equals($_SESSION['tokenCSRF'], $_POST['tokenCSRF'])) {
                        if (Securite::isConnected()) {
                            if (!empty($_POST['inputResponse']) && !empty($_POST['topicID'])) {
                                /**
                                 * !on fait la même vérif ici que celle de JS pour les réponses vides :
                                 * on retire toutes les balises vide par "". Si au final la chaine est completement vide alors c'est que la soumission ne contient rien.
                                 */
                                $contenuDeVerification = preg_replace('/<[^>]*>/', '', $_POST['inputResponse']);
                                if ($contenuDeVerification) {
                                    $escapedResponse = htmlspecialchars($_POST['inputResponse']);
                                    $this->userController->validationResponseSujet($escapedResponse, $_POST['topicID']);
                                } else {
                                    Toolbox::dataJson(false, "Veuillez entrer du contenu avant de poster votre réponse");
                                    exit;
                                }
                            } else {
                                throw new Exception("La page n'existe pas");
                            }
                        } else {
                            Toolbox::dataJson(false, "noConnected");
                            exit;
                        }
                    } else {
                        Toolbox::ajouterMessageAlerte("Session expirée, veuillez recommencer", 'rouge');
                        unset($_SESSION['profil']);
                        unset($_SESSION['tokenCSRF']);
                        Toolbox::dataJson(false, "expired token");
                        exit;
                    }


                    break;








                default:
                    throw new Exception("La page n'existe pas");
            }
        } catch (Exception $e) {
            $this->homeController->pageErreur($e->getMessage());
        }
    }
}
