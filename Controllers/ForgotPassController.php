<?php

namespace Controllers;

use Entities\Users;
use Models\UsersModel;
use Controllers\MainController;
use Controllers\Services\Toolbox;
use Controllers\Services\Securite;
use Controllers\Services\JWTService\JWTService;

class ForgotPassController extends MainController
{
    private $usersModel;

    public function __construct()
    {
        $this->usersModel = new UsersModel();
    }

    public function forgotPass()
    {
        /**
         * ! -> 1
         * * Affichage de la vue pour entrer son mail: $this->forgotView()
         * 
         * ! -> 2 
         * * Envoi du mail : $this->sendEmail($email);
         * Avec un token JWT .
         * 
         * ! -> 3
         * * Affichage de la vue pour entrer le nouveau pass : $this->resetPassView($jwt);
         * le token JWT se trouve en get 
         * 
         * ! -> 4
         * * Vérif et confirmation du reset : $this->validResetPass(...)
         */


        // l'utilisateur ne doit pas être connecté pour acceder au services d'oublie de mot de passe.
        if (!Securite::isConnected()) {
            if (!empty($_GET['page'])) {
                $url = explode("/", filter_var($_GET['page'], FILTER_SANITIZE_URL));
            }
            //si paramètre GET...
            if ((isset($url[2]) && !empty($url[2]))) {
                //... + requete POST : validationResetPassword
                if ($_SERVER['REQUEST_METHOD'] === 'POST') { //! -> 4
                    if (!empty($_POST['nouveauPassword']) && !empty($_POST['confirmPassword'])) {
                        if (Securite::verifCSRF()) {
                            $tokenToVerify = $url[2];
                            $nouveauPassword = htmlspecialchars($_POST['nouveauPassword']);
                            $confirmPassword = htmlspecialchars($_POST['confirmPassword']);
                            $this->validResetPass($tokenToVerify, $nouveauPassword, $confirmPassword);
                        } else {
                            Toolbox::ajouterMessageAlerte("Session expirée, veuillez recommencer", 'rouge');
                            header("Location: " . URL . "home");
                            exit;
                        }
                    } else {
                        $tokenToVerify = $url[2];
                        Toolbox::ajouterMessageAlerte("Les deux champs sont requis", 'rouge');
                        header("Location: " . URL . "forgotPass/" . $tokenToVerify);
                        exit;
                    }
                } else { //! -> 3
                    //si paramètre GET uniquement, c'est la VUE
                    $jwt = $url[2];
                    $this->resetPassView($jwt);
                }

                // Si requête POST seule (pas de GET) : envoie du mail
            } elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['passwordForgot'])) { //! -> 2
                $email = htmlspecialchars($_POST['passwordForgot']);
                $this->sendEmail($email);
            } else {
                // Si pas de paramètre GET ni de requête POST, c'est la vue
                $this->forgotView(); //! -> 1
            }
        } else {
            header("Location: " . URL);
            exit;
        }
    }

    private function validResetPass($tokenToVerify, $nouveauPassword, $confirmPassword)
    {
        $regexpPassword = "/^(?=.*[A-Z])(?=.*[!@#$%^&*()_+\-=\[\]{};':\"\\|,.<>\/?].*[!@#$%^&*()_+\-=\[\]{};':\"\\|,.<>\/?])[a-zA-Z0-9!@#$%^&*()_+\-=\[\]{};':\"\\|,.<>\/?]{8,50}$/";

        $jwt = new JWTService();
        if ($jwt->isValid($tokenToVerify) && !$jwt->isExpired($tokenToVerify) && $jwt->check($tokenToVerify, SECRET)) {

            if (!preg_match($regexpPassword, $nouveauPassword)) {
                $message = "Le mot de passe doit contenir entre 8 et 50 caractères dont au moins 2 caractères spéciaux et une majuscule";
                Toolbox::ajouterMessageAlerte($message, 'rouge');
                header("Location: " . URL . 'forgotPass/' . $tokenToVerify);
                exit;
            } elseif ($nouveauPassword !== $confirmPassword) {
                $message = "Les mots de passe ne sont pas identiques";
                Toolbox::ajouterMessageAlerte($message, 'rouge');
                header("Location: " . URL . 'forgotPass/' . $tokenToVerify);
                exit;
            } else {
                $payload = $jwt->getPayload($tokenToVerify);
                $payload['userID'];
                //ici on pourrait ajouter une couche de sécurité pour re-vérifier les champs nouveauPassword et confirmPassword
                $infosUser = $this->usersModel->getUserBy('userID', $payload['userID']);
                $pseudo = $infosUser->pseudo;
                $userId = $infosUser->userID;
                $created_at = $infosUser->userDate;


                $user = new Users($pseudo, $created_at, $userId);
                $user->setPassword(password_hash($nouveauPassword, PASSWORD_DEFAULT));
                $resultat = $this->usersModel->updatePassword($user);
                if ($resultat) {
                    Toolbox::ajouterMessageAlerte("Mot de passe réinitialisé avec succès", "vert");
                    header("Location: " . URL . "home");
                    exit;
                } else {
                    Toolbox::ajouterMessageAlerte("Une erreur est survenue", "rouge");
                    header("Location: " . URL . "home");
                    exit;
                }
            }
        } else {
            Toolbox::ajouterMessageAlerte("Token invalide ou expiré", "rouge");
            header("Location: " . URL . "home");
            exit;
        }
    }



    private function resetPassView($jwt)
    {
        $data_page = [
            "pageDescription" => "Page de réinitialisation de mot de passe",
            "pageTitle" => "Réinitialiser mot de passe",
            "view" => "../Views/forgotPass/resetPassView.php",
            "template" => "../Views/common/template.php",
            "css" => "public/style/resetPasswordStyle.css",
            "script" => "public/js/validFormResetPassword.js",
            'tokenCSRF' => $_SESSION['tokenCSRF'],
            "jwt" => $jwt
        ];
        $this->render($data_page);
    }

    private function sendEmail($email)
    {
        $userInfos = $this->usersModel->getUserBy('email', $email);

        if ($userInfos) {
            $userId = $userInfos->userID;
            $pseudo = $userInfos->pseudo;
            $cheminTemplate = '../Views/templateMail/templateForgotPassword.html';
            $token = Securite::createTokenJWT($userId, $pseudo, $email);
            $route = URL . "forgotPass/" . $token;
            $sujet = 'Réinitialisation du mot de passe sur Guitare Forum';

            if (Toolbox::sendMail($pseudo, $email, $route, $sujet, $cheminTemplate)) {
                $message = "Un mail a été envoyé sur votre boite mail afin de réinitialiser votre mot de passe !";
                Toolbox::ajouterMessageAlerte($message, 'vert');
                Toolbox::dataJson(true, "email envoyé");
                exit;
            } else {
                $message = "Une erreur est survenue, veuillez contacter l'administrateur";
                Toolbox::dataJson(false, $message);
                exit;
            }
        } else {
            Toolbox::dataJson(false, "Adresse email inconnue");
            exit;
            // Toolbox::ajouterMessageAlerte("Adresse email inconnue", "rouge");
        }
    }

    // private function createToken($userId, $pseudo = null, $email = null)
    // {
    //     $header = [
    //         'typ' => 'JWT',
    //         'alg' => 'HS256'
    //     ];
    //     $payload = [
    //         'userID' => $userId,
    //         'pseudo' => $pseudo,
    //         'email' => $email
    //     ];
    //     $jwt = new JWTService();

    //     //Si "email" n'est pas null, c'est donc un token destiné à l'envoi d'un mail. Dans ce cas précis le token aura une durée de validité de 3h. Si "email" reste null, ce sera un token de connexion d'une durée de validité de 86400s -> 24h.
    //     $validity = $email ? 10800 : 86400;
    //     return $jwt->generate($header, $payload, SECRET, $validity);
    // }

    private function forgotView()
    {

        $data_page = [
            "pageDescription" => "Page : j'ai oublié mon de mot de passe",
            "pageTitle" => "Page pour réinitialiser le mot de passe",
            "view" => "../Views/forgotPass/forgotView.php",
            "template" => "../Views/common/template.php",
            "css" => "public/style/forgotStyle.css",
            "script" => "public/js/forgot.js",
            "tokenCSRF" => $_SESSION["tokenCSRF"]
        ];
        $this->render($data_page);
    }
}
