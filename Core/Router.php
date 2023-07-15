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
                // dump('le get est vide');
                $page = "accueil";
            } else {

                /**
                 * Sinon c'est que l'url du site est de ce type : http://monsite/materiel/guitare-classique
                 * le $_GET['page'] faudra dans cet exemple "compte/profil".
                 * On fait donc un "explode" pour avoir ce tableau : 
                 * ['public''materiel', 'guitare-classique"]
                 * [   0    ,     1    ,       2   ]
                 * ['public''accueil']
                 * [   0    ,    1  ]
                 */
                $url = explode("/", filter_var($_GET['page'], FILTER_SANITIZE_URL));
                // dump($_GET['page']);
                $page = $url[1];
                // dump($page);
            }

            switch ($page) {

                case "accueil":

                    /**
                     * dans le cas ou le $url[2] vaut xxxxxx.4 alors ce sera une liste de topics à afficher en fonction de l'ID de la sous-catégorie. SINON ce sera la page d'accueil à afficher
                     * URL[2] se présente sous cette forme : "guitare-classique.4"
                     * "4" correspond à l'id de la sous catégorie.
                     */

                    if (isset($url[2]) && !empty($url[2])) {
                        $sousCategoryURL = htmlspecialchars($url[2]);
                        $this->visiteurController->displayTopicsList($sousCategoryURL);
                    } else {
                        //sinon par defaut c'est la page d'accueil avec la liste des catégories et sous catégories associées.
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

                            //j'ai placé un input hidden qui contient l'url précédent. obligé car lorsque le script va sur cette route 'case "validationLogin":" le contenu de la variable http_referrer c'est la page de connexion, et pas celle encore d'avant. 
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

                    //! les 4 "case" suivant gèrent la réinitialisation du password en cas d'oublie : 
                    /**
                     * * "forgot" c'est la 1ere vue qui permet de saisir son mail
                     * * "sendEmailPassForgot" gère l'envoi du mail de réinitialisation de mot de passe. Vérifie avant si la vue 'forgot avait le bon token'
                     * * "reinitialiserPassword" c'est la 2nd vue qui permet de réinitialiser son mot de pass
                     * * "validationResetPassword" vérifie le tout et valide le changement.
                     */
                case "forgot": //!VUE
                    //On vérifie s'il est connecté pour autoriser l'affichage de la page "forgot"
                    if (!Securite::isConnected()) {
                        $this->userController->forgotView();
                    } else {
                        header("Location: " . URL);
                        exit;
                    }
                    break;

                case "sendEmailPassForgot":
                    //une fois le formulaire soumis, on gère l'envoi du mail de réinitialisation de mot de passe
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
                case "reinitialiserPassword": //!VUE
                    //page lorsqu'on clique sur le lien de l'email reçu, avec le token en GET. C'est un formulaire.

                    //On considère que le user n'est pas censé être connecté pour accéder au formulaire de réinitialisation
                    if (!Securite::isConnected()) {
                        //si l'url est sous ce format : monsite/reinitialiserPassword/xxxx
                        //"xxxx" correspond au token. 
                        //la vérification du token se fera plus tard dans le case "validationResetPassword".
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

                    //on vérifie le tokenCSRF du formulaire
                    if (!empty($_POST['tokenCSRF']) && hash_equals($_SESSION['tokenCSRF'], $_POST['tokenCSRF'])) {

                        //Si tokenCSRF ok, on gère le reset du password en fonction de la validité du tokenJWT
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
                            // $pseudo = html_entity_decode($_POST['pseudo']);
                            // $emailInscription = html_entity_decode($_POST['emailInscription']);
                            // $password = html_entity_decode($_POST['password']);
                            // $confirmPassword =  html_entity_decode($_POST['confirmPassword']);

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

                    //c'est la route qui se trouve dans l'email reçu lors de la demande de changement d'email.
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
                                //données envoyées à la page profil pour alimenter les formulaires.
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
                                    /**
                                     * ! Les "unset" peuvent être utiles pour des raisons de sécurité, car cela empêche toute utilisation ultérieure de ces données de session potentiellement compromises. De plus, cela garantit que l’utilisateur doit se reconnecter et obtenir un nouveau jeton CSRF avant de poursuivre,
                                     */
                                    unset($_SESSION['profil']);
                                    unset($_SESSION['tokenCSRF']);
                                    Toolbox::dataJson(false, "expired token");
                                    exit;
                                }
                                break;

                            case 'editEmail':
                                if (!empty($_POST['tokenCSRF']) && hash_equals($_SESSION['tokenCSRF'], $_POST['tokenCSRF'])) {
                                    // Le jeton anti-CSRF est valide, traiter les données du formulaire
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
