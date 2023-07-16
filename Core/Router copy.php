<?php

namespace Core;

use Exception;
use Controllers\Services\Toolbox;
use Controllers\Services\Securite;
use Controllers\User\UserController;
use Controllers\Visiteur\VisiteurController;

class Router
{
    private object $visiteurController;
    private object $userController;

    public function __construct()
    {
        $this->visiteurController = new VisiteurController();
        $this->userController = new UserController();
    }
    public function routes()
    {
        try {
            if (empty($_GET['page'])) {
                $page = "accueil";
            } else {
                $url = explode("/", filter_var($_GET['page'], FILTER_SANITIZE_URL));
                $page = $url[1];
            }
            switch ($page) {
                case "accueil":
                    if (isset($url[2]) && !empty($url[2])) {
                        $sousCategoryURL = htmlspecialchars($url[2]);
                        $this->visiteurController->displayTopicsList($sousCategoryURL);
                    } else {
                        $this->visiteurController->accueil();
                    }
                    break;
                case "sujet":
                    if (isset($url[2]) && !empty($url[2])) {
                        $topicUrl = htmlspecialchars($url[2]);
                        $this->visiteurController->displayTopic($topicUrl);
                    } else {
                        throw new Exception("La page n'existe pas");
                    }
                    break;
                case  "connexion":
                    if (!Securite::isConnected()) {
                        $this->visiteurController->connexionView();
                    } else {
                        header("Location: " . URL);
                        exit;
                    }
                    break;
                case "validationLogin":
                    if (!empty($_POST['tokenCSRF']) && hash_equals($_SESSION['tokenCSRF'], $_POST['tokenCSRF'])) {

                        if (!empty($_POST['pseudo']) && !empty($_POST['password'])) {
                            $pseudo = htmlspecialchars($_POST['pseudo']);
                            $password = htmlspecialchars($_POST['password']);
                            $previousURL = htmlspecialchars($_POST['previousURL'] ?? "");
                            $this->userController->validationLogin($pseudo, $password, $previousURL);
                        } else {
                            throw new Exception("La page n'existe pas");
                        }
                    } else {
                        Toolbox::ajouterMessageAlerte("Session expirée pour cause d'inactivité, veuillez recommencer", 'rouge');
                        unset($_SESSION['profil']);
                        unset($_SESSION['tokenCSRF']);
                        Toolbox::dataJson(false, "expired token");
                        exit;
                    }

                    break;
                case "forgot":
                    if (!Securite::isConnected()) {
                        $this->userController->forgotView();
                    } else {
                        header("Location: " . URL);
                        exit;
                    }
                    break;
                case "sendEmailPassForgot":
                    if (!empty($_POST['tokenCSRF']) && hash_equals($_SESSION['tokenCSRF'], $_POST['tokenCSRF'])) {
                        if (!empty($_POST['passwordForgot'])) {
                            $email = htmlspecialchars($_POST['passwordForgot']);
                            $this->userController->sendEmailPassForgot($email);
                        } else {
                            throw new Exception("La page n'existe pas");
                        }
                    } else {
                        unset($_SESSION['tokenCSRF']);
                        Toolbox::ajouterMessageAlerte("Session expirée, veuillez recommencer", 'rouge');
                        Toolbox::dataJson(false, "expired token");
                        exit;
                    }
                    break;
                case "reinitialiserPassword":
                    if (!Securite::isConnected()) {
                        if ((isset($url[2]) && !empty($url[2]))) {
                            $jwt = $url[2];
                            $this->userController->reinitialiserPassword($jwt);
                        } else {
                            throw new Exception("La page n'existe pas");
                        }
                    } else {
                        header("Location: " . URL);
                        exit;
                    }
                    break;
                case "validationResetPassword":
                    if (!empty($_POST['tokenCSRF']) && hash_equals($_SESSION['tokenCSRF'], $_POST['tokenCSRF'])) {
                        if (isset($url[2]) && !empty($url[2])) {
                            $tokenToVerify = $url[2];
                            if (!empty($_POST['nouveauPassword']) && !empty($_POST['confirmPassword'])) {
                                $nouveauPassword = htmlspecialchars($_POST['nouveauPassword']);
                                $confirmPassword = htmlspecialchars($_POST['confirmPassword']);
                                $this->userController->validationResetPassword($tokenToVerify, $nouveauPassword, $confirmPassword);
                            } else {
                                Toolbox::ajouterMessageAlerte("Les deux champs son requis", 'rouge');
                                header("Location: " . URL . "reinitialiserPassword/" . $tokenToVerify);
                                exit;
                            }
                        } else {
                            throw new Exception("La page n'existe pas");
                        }
                    } else {
                        Toolbox::ajouterMessageAlerte("Session expirée, veuillez recommencer", 'rouge');
                        header("Location: " . URL . "accueil");
                        exit;
                    }
                    break;
                case "deconnexion":
                    $this->userController->logout();
                    break;
                case "inscription":
                    if (!Securite::isConnected()) {
                        $this->visiteurController->inscription();
                    } else {
                        header("Location: " . URL);
                        exit;
                    }
                    break;
                case "validationInscription":
                    if (!empty($_POST['tokenCSRF']) && hash_equals($_SESSION['tokenCSRF'], $_POST['tokenCSRF'])) {
                        if (!empty($_POST['pseudo']) && !empty($_POST['emailInscription']) && !empty($_POST['password']) && !empty($_POST['confirmPassword'])) {
                            $pseudo = htmlspecialchars($_POST['pseudo']);
                            $emailInscription = htmlspecialchars($_POST['emailInscription']);
                            $password = htmlspecialchars($_POST['password']);
                            $confirmPassword =  htmlspecialchars($_POST['confirmPassword']);
                            $this->userController->validationInscription(trim($pseudo), trim($emailInscription), trim($password), trim($confirmPassword));
                        } else {
                            throw new Exception("La page n'existe pas");
                        }
                    } else {
                        Toolbox::ajouterMessageAlerte("Session expirée, veuillez recommencer", 'rouge');
                        unset($_SESSION['profil']);
                        unset($_SESSION['tokenCSRF']);
                        Toolbox::dataJson(false, "expired token");
                        exit;
                    }
                    break;
                case "verifToken":
                    if (isset($url[2]) && !empty($url[2])) {
                        $tokenToVerify = $url[2];
                        $this->userController->activatingAccount($tokenToVerify);
                        header("Location: " . URL);
                        exit;
                    } else {
                        throw new Exception("La page n'existe pas");
                    }
                    break;
                case "returnToken":
                    if (isset($url[2]) && !empty($url[2])) {
                        $userId = $url[2];
                        $this->userController->returnToken($userId);
                    } else {
                        throw new Exception("La page n'existe pas");
                    }
                    break;
                case "validationEditEmail":
                    if (isset($url[2]) && !empty($url[2])) {
                        $tokenToVerify = $url[2];
                        $this->userController->validationEditEmail($tokenToVerify);
                        header("Location: " . URL);
                        exit;
                    } else {
                        throw new Exception("La page n'existe pas");
                    }
                    break;
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
                case "compte":
                    if (!Securite::isConnected()) {
                        $message = "Pour accéder à votre profil, veuillez vous connecter.";
                        Toolbox::ajouterMessageAlerte($message, 'rouge');
                        header("Location: " . URL);
                        exit;
                    } else {
                        switch ($url[2]) {
                            case 'profil':
                                $this->userController->profil();
                                break;
                            case 'datasFormProfil':
                                $this->userController->datasFormProfil();
                                break;
                            case 'avatar':

                                if (!empty($_POST['tokenCSRF']) && hash_equals($_SESSION['tokenCSRF'], $_POST['tokenCSRF'])) {
                                    if (isset($_FILES['avatarPhoto']) && $_FILES['avatarPhoto']['error'] == 0) {
                                        $this->userController->editAvatar($_FILES['avatarPhoto']);
                                    } else {
                                        $message = "Une erreur est survenue...";
                                        Toolbox::ajouterMessageAlerte($message, 'rouge');
                                        header("Location: " . URL . "compte/profil");
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
                            case 'editEmail':
                                if (!empty($_POST['tokenCSRF']) && hash_equals($_SESSION['tokenCSRF'], $_POST['tokenCSRF'])) {
                                    if (!empty($_POST['email'])) {
                                        $this->userController->editEmail(htmlspecialchars($_POST['email']));
                                    }
                                } else {
                                    Toolbox::ajouterMessageAlerte("Session expirée, veuillez recommencer", 'rouge');
                                    unset($_SESSION['profil']);
                                    unset($_SESSION['tokenCSRF']);
                                    Toolbox::dataJson(false, "expired token");
                                    exit;
                                }
                                break;
                            case 'password':
                                if (!empty($_POST['tokenCSRF']) && hash_equals($_SESSION['tokenCSRF'], $_POST['tokenCSRF'])) {
                                    if (!empty($_POST['ancienPassword']) && !empty($_POST['nouveauPassword']) && !empty($_POST['confirmPassword'])) {
                                        $ancienPassword = htmlspecialchars($_POST['ancienPassword']);
                                        $nouveauPassword = htmlspecialchars($_POST['nouveauPassword']);
                                        $confirmPassword = htmlspecialchars($_POST['confirmPassword']);
                                        $this->userController->changePassword($ancienPassword, $nouveauPassword, $confirmPassword);
                                    }
                                } else {
                                    Toolbox::ajouterMessageAlerte("Session expirée, veuillez recommencer", 'rouge');
                                    unset($_SESSION['profil']);
                                    unset($_SESSION['tokenCSRF']);
                                    Toolbox::dataJson(false, "expired token");
                                    exit;
                                }
                                break;
                            case 'about':
                                if (!empty($_POST['tokenCSRF']) && hash_equals($_SESSION['tokenCSRF'], $_POST['tokenCSRF'])) {
                                    if (!empty($_POST['guitare']) && !empty($_POST['emploi']) && !empty($_POST['ville'])) {
                                        $guitare = htmlspecialchars($_POST['guitare']);
                                        $emploi = htmlspecialchars($_POST['emploi']);
                                        $ville = htmlspecialchars($_POST['ville']);
                                        $this->userController->editAbout(trim($guitare), trim($emploi), trim($ville));
                                    } else {
                                        throw new Exception("La page n'existe pas");
                                    }
                                } else {
                                    Toolbox::ajouterMessageAlerte("Session expirée, veuillez recommencer", 'rouge');
                                    unset($_SESSION['profil']);
                                    unset($_SESSION['tokenCSRF']);
                                    Toolbox::dataJson(false, "expired token");
                                    exit;
                                }
                                break;
                            case 'supprimerCompte':
                                $this->userController->supprimerCompte();
                                break;
                            case 'validerSupprimerCompte':
                                $this->userController->validerSupprimerCompte();
                                header("Location: " . URL);
                                exit;
                                break;
                            default:
                                throw new Exception("La page n'existe pas");
                                break;
                        }
                    }
                default:
                    throw new Exception("La page n'existe pas");
            }
        } catch (Exception $e) {
            $this->visiteurController->pageErreur($e->getMessage());
        }
    }
}
