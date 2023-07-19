<?php

namespace Controllers;

use Entities\Users;
use Models\UsersModel;
use Controllers\MainController;
use Controllers\Services\Toolbox;
use Controllers\Services\Securite;
use Controllers\Services\JWTService\JWTService;

include 'Services\JWTService\configJWT.php';

class RegisterController extends MainController
{
    private $usersModel;

    public function __construct()
    {
        $this->usersModel = new UsersModel();
    }

    public function register($action)
    {

        /**
         * !       [0]            [1]                   [2] 
         * ! 1 -> register      /viewRegister
         * ! 2 -> register      /sendMail
         * ! 2bis -> register   /returnToken           /jfgdkfjgbdlkfgn
         * ! 3 -> register      /accountActivation     /jgbdfkjgndfgb
         */

        if (!Securite::isConnected()) {


            $this->$action();
        } else {
            header("Location: " . URL . "home");
            exit;
        }
    }

    private function viewRegister()
    {
        $data_page = [
            "pageDescription" => "Page de création de compte",
            "pageTitle" => "Inscription",
            "view" => "../Views/account/viewRegister.php",
            "template" => "../Views/common/template.php",
            "css" => "/style/registerStyle.css",
            "script" => "/js/validFormRegister.js",
            "tokenCSRF" => $_SESSION['tokenCSRF']
        ];
        $this->render($data_page);
    }

    private function sendMail()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!empty($_POST['pseudo']) && !empty($_POST['emailInscription']) && !empty($_POST['password']) && !empty($_POST['confirmPassword'])) {
                if (Securite::verifCSRF()) {
                    $pseudo = trim(htmlspecialchars($_POST['pseudo']));
                    $email = trim(htmlspecialchars($_POST['emailInscription']));
                    $password = trim(htmlspecialchars($_POST['password']));
                    $confirmPassword =  trim(htmlspecialchars($_POST['confirmPassword']));
                    //on crée le compte

                    $regexpPseudo = "/^[a-zA-Z0-9éèêëàâäôöûüçî ]+$/";
                    // $regexpEmail = "/^\w+([-+.']\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/";
                    $regexpPassword = "/^(?=.*[A-Z])(?=.*[!@#$%^&*()_+\-=\[\]{};':\"\\|,.<>\/?].*[!@#$%^&*()_+\-=\[\]{};':\"\\|,.<>\/?])[a-zA-Z0-9!@#$%^&*()_+\-=\[\]{};':\"\\|,.<>\/?]{8,50}$/";

                    if ($this->usersModel->getUserBy('pseudo', $pseudo)) {
                        Toolbox::dataJson(false, "Pseudo déjà utilisé", 'pseudo');
                        exit;
                    } else if ($this->usersModel->getUserBy('email', $email)) {
                        Toolbox::dataJson(false, "Adresse email déjà utilisée", 'email');
                        exit;
                    } else if (strlen($pseudo) < 4 || strlen($pseudo) > 50) {
                        Toolbox::dataJson(false, "Votre pseudo doit contenir  entre 4 et 50 caractères", 'pseudo');
                        exit;
                    } else if (!preg_match($regexpPseudo, $pseudo)) {
                        Toolbox::dataJson(false, "Votre pseudo ne doit pas contenir de caractères spéciaux", 'pseudo');
                        exit;
                    } else if ($email == '') {
                        Toolbox::dataJson(false, "Le champs email est requis", 'email');
                        exit;
                    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        Toolbox::dataJson(false, "Le format d'email n'est pas correct", 'email');
                        exit;
                    } else if ($password == '') {
                        Toolbox::dataJson(false, "Le champs mot de passe est requis", 'password');
                        exit;
                    } else if (!preg_match($regexpPassword, $password)) {
                        Toolbox::dataJson(false, "Le mot de passe doit contenir entre 8 et 50 caractères dont au moins 2 caractères spéciaux et une majuscule", 'password');
                        exit;
                    } else if ($password !== $confirmPassword) {
                        Toolbox::dataJson(false, "Les mots de passe ne sont pas identiques", 'password');
                        exit;
                    } else {
                        /**
                         * !Pourquoi je passe au constructeur le pseudo, la date actuelle (l'id sera null par defaut) ?
                         * *parce que je pars du principe que tout ce qui ne sera pas concerné par une modification en bdd (userid, pseudo et date de création du compte) n'auront pas de setter. Donc, il faut les initialiser au construct.
                         * *c’est une bonne pratique de passer les propriétés qui ne seront jamais modifiées dans le constructeur. Cela permet de garantir l’immuabilité de ces propriétés, et de simplifier la création des objets. On peux aussi rendre ces propriétés privées ou protégées, pour empêcher leur accès direct depuis l’extérieur de la classe. Et donc utiliser des getters pour les lire, mais pas de setters pour les écrire.
                         * 
                         * *Ce n’est pas obligatoire ...Cependant, l’avantage du constructeur est qu’il permet de garantir que l’objet user est créé avec un état valide et cohérent. Si on n'utilise pas de constructeur, on doit s'assurer que les propriétés de l’objet user sont bien initialisées avant de les utiliser, ce qui peut être source d’erreurs ou d’oublis. De plus, le constructeur rend le code plus clair et plus concis, car on a pas à appeler plusieurs setters pour créer un objet user.
                         * *...l'inconvénient c'est qu'il y a plus de paramètre lors de l'instanciation.
                         * 
                         * * dernière remarque, je passe l'id à null par défaut car j'instancie la classe User avant l'enregistrement en BDD, donc je ne connais pas à l'avance l'id. le construct prend par defaut "null". Par contre à une prochaine instanciation, lorsque le user sera déjà enregistré, on pourra passer en 3ème paramètre son identifiant, qui ne prend plus la valeur "null", mais celle passé en paramètre. 
                         */
                        $user = new Users($pseudo, Toolbox::creerDateActuelle());
                        $user->setEmail($email);
                        $user->setPassword(password_hash($password, PASSWORD_DEFAULT));
                        $user->setGuitare('Non renseigné');
                        $user->setVille('Non renseigné');
                        $user->setEmploi('Non renseigné');
                        $user->setAvatar('avatarDefault.jpg');

                        $resultat = $this->usersModel->inscription($user);
                        if ($resultat) {
                            $userId = $this->usersModel->lastInsertId();
                            $filePath = 'images/profils/' . $userId;
                            //Si ce dossier n'existe pas, il faut le créer
                            if (!file_exists($filePath)) {
                                mkdir($filePath, 0777, true);
                            }
                            //je copie l'avatar de base que je place dans le dossier du User. Je fais une copie pour éviter des problèmes que j'ai rencontré (problème d'affiche, et lorsqu'il fallait changer l'avatar pour un autre, problème de filepath...)
                            $source = 'images/profils/avatarDefault.jpg';
                            $destination = $filePath . '/avatarDefault.jpg';
                            copy($source, $destination);

                            $token = Securite::createTokenJWT($userId, $pseudo, $email);
                            $cheminTemplate = '../Views/templateMail/templateInscription.html';
                            $route = URL . "register/accountActivation/" . $token;
                            $sujet = 'Activation de votre compte sur Guitare Forum';

                            if (Toolbox::sendMail($pseudo, $email, $route, $sujet, $cheminTemplate)) {
                                $message = "Votre compte a été créé avec succès ! Un mail d'activation a été envoyé sur votre boite mail !";
                                Toolbox::ajouterMessageAlerte($message, 'vert');
                            } else {
                                $message = "Votre compte a été créé, mais un problème est survenu lors de l'envoi du mail d'activation'. Veuillez contacter l'administrateur du site.";
                                Toolbox::ajouterMessageAlerte($message, 'rouge');
                            }
                            Toolbox::dataJson(true);
                        } else {
                            Toolbox::ajouterMessageAlerte("Problème rencontré", "rouge");
                            Toolbox::dataJson(false, "Problème rencontré");
                        }
                    }
                } else {
                    unset($_SESSION['profil']);
                    unset($_SESSION['tokenCSRF']);
                    Toolbox::dataJson(false, "expired token");
                    exit;
                }
            } else {
                Toolbox::dataJson(false, "Erreur recontrée");
                exit;
            }
        } else {
            Toolbox::dataJson(false, "Erreur transmission POST");
            exit;
        }
    }

    private function returnToken()
    {
        $url = explode("/", filter_var($_GET['page'], FILTER_SANITIZE_URL));

        if (isset($url[2]) && !empty($url[2])) {
            $userId = $url[2];
            $user = $this->usersModel->getUserBy('userID', $userId);
            //si le user existe et qu'il n'est pas encore valide
            if ($user && !$user->isValid) {

                $pseudo = $user->pseudo;
                $email = $user->email;
                $token = Securite::createTokenJWT($userId, $pseudo, $email);
                $route = URL . "register/accountActivation/" . $token;
                $cheminTemplate = '../Views/templateMail/templateInscription.html';
                $sujet = 'Validation inscription Guitare Forum';

                if (Toolbox::sendMail($pseudo, $email, $route, $sujet, $cheminTemplate)) {
                    $message = "Un mail de validation a été envoyé sur votre boite mail !";
                    Toolbox::ajouterMessageAlerte($message, 'vert');
                    header("Location: " . URL);
                    exit;
                } else {
                    $message = "Problème rencontré. Veuillez contacter l'administrateur du site.";
                    Toolbox::ajouterMessageAlerte($message, 'rouge');
                    header("Location: " . URL);
                    exit;
                }
            } else {
                //si le $user existé (mais que le compte était déjà validé)
                $message =  $user ? "Compte déjà validé" : 'Erreur';
                Toolbox::ajouterMessageAlerte($message, 'rouge');
                header("Location: " . URL);
                exit;
            }
        } else {

            header("Location: " . URL . "home");
            exit;
        }
    }

    private function accountActivation()
    {
        $url = explode("/", filter_var($_GET['page'], FILTER_SANITIZE_URL));

        if (isset($url[2]) && !empty($url[2])) {
            $tokenToVerify = $url[2];

            $jwt = new JWTService();
            if ($jwt->isValid($tokenToVerify) && !$jwt->isExpired($tokenToVerify) && $jwt->check($tokenToVerify, SECRET)) {
                $payload = $jwt->getPayload($tokenToVerify);
                /**
                 * *je pourrais passer par la class User et faire en sorte que la méthode "activatingUser()" utilise un getter pour récupérer l'id.
                 * *Mais je décide autrement car je serais obligé instancier la classe User avec 3 paramètres : le pseudo, la date de création, et userId. Le userId je le connais, mais pas les deux autres. Pour les connaitre deux solutions :
                 * * passer par une méthode du model pour récpérer les info du user en fonction de son ID, OU lors de la création du token, de passer dans le payload d'autres infomations : le pseudo et la date de création du compte. dans le cas, pas besoin de faire une requête sql.
                 * *Donc au final je décide de faire au plus simple, je passe en paramètre l'id via la variable $payload['userID']
                 */
                if ($this->usersModel->activatingUser($payload['userID'])) {
                    Toolbox::ajouterMessageAlerte("Votre compte a été validé avec succès, vous pouvez maintenant vous connecter", "vert");
                }
            } else {
                Toolbox::ajouterMessageAlerte("token NON valide", "rouge");
            }

            header("Location: " . URL);
            exit;
        } else {
            Toolbox::ajouterMessageAlerte("Token absent", 'rouge');
            header("Location: " . URL . "home");
            exit;
        }
    }
}
