<?php

namespace Controller;

use Entities\Users;
use Models\UsersModel;
use Controllers\MainController;
use Controllers\Services\Toolbox;
use Controllers\Services\Securite;
use Controllers\Services\JWTService\JWTService;

class ForgotPassController extends MainController
{
    private $usersModel;
    // private $message; 
    // private $messageModel;
    // private $user; //getter-setter de l'entité user

    public function __construct()
    {
        $this->usersModel = new UsersModel();
        // $this->messageModel = new MessagesModel();
        // $this->message = new Messages();
        // $this->user = new User();
    }

    public function forgotPass()
    {

        // l'utilisateur ne doit pas être connecté pour acceder au services d'oublie de mot de passe.
        if (!Securite::isConnected()) {

            if ((isset($url[2]) && !empty($url[2]))) {

                //si paramètre GET + requete POST : validationResetPassword
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    if (!empty($_POST['nouveauPassword']) && !empty($_POST['confirmPassword'])) {
                        if (Securite::verifCSRF()) {
                            $tokenToVerify = $url[2];
                            $nouveauPassword = htmlspecialchars($_POST['nouveauPassword']);
                            $confirmPassword = htmlspecialchars($_POST['confirmPassword']);
                            $this->validationResetPassword($tokenToVerify, $nouveauPassword, $confirmPassword);
                        } else {
                            Toolbox::ajouterMessageAlerte("Session expirée, veuillez recommencer", 'rouge');
                            header("Location: " . URL . "home");
                            exit;
                        }
                    } else {
                        $tokenToVerify = $url[2];
                        Toolbox::ajouterMessageAlerte("Les deux champs son requis", 'rouge');
                        header("Location: " . URL . "reinitialiserPassword/" . $tokenToVerify);
                        exit;
                    }
                } else {
                    //si paramètre GET uniquement, c'est la VUE
                    $jwt = $url[2];
                    $this->reinitialiserPassword($jwt);
                }

                // Si requête POST seule (pas de GET) : envoie du mail
            } elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['passwordForgot'])) {
                $email = htmlspecialchars($_POST['passwordForgot']);
                $this->sendEmailPassForgot($email);
            } else {
                // Si pas de paramètre GET ni de requête POST, c'est la vue
                $this->forgotView();
            }
        }
    }

    private function validationResetPassword($tokenToVerify, $nouveauPassword, $confirmPassword)
    {
        $regexpPassword = "/^(?=.*[A-Z])(?=.*[!@#$%^&*()_+\-=\[\]{};':\"\\|,.<>\/?].*[!@#$%^&*()_+\-=\[\]{};':\"\\|,.<>\/?])[a-zA-Z0-9!@#$%^&*()_+\-=\[\]{};':\"\\|,.<>\/?]{8,50}$/";

        $jwt = new JWTService();
        if ($jwt->isValid($tokenToVerify) && !$jwt->isExpired($tokenToVerify) && $jwt->check($tokenToVerify, SECRET)) {
            $page = 'reinitialiserPassword/' . $tokenToVerify;
            if (!preg_match($regexpPassword, $nouveauPassword)) {
                $this->messageAlert("Le mot de passe doit contenir entre 8 et 50 caractères dont au moins 2 caractères spéciaux et une majuscule", $page);
            } else if ($nouveauPassword !== $confirmPassword) {
                $this->messageAlert("Les mots de passe ne sont pas identiques", $page);
            } else {
                $payload = $jwt->getPayload($tokenToVerify);
                $payload['userID'];
                //ici on pourrait ajouter une couche de sécurité pour re-vérifier les champs nouveauPassword et confirmPassword
                $infosUser = $this->usersModel->getUserBy('userID', $payload['userID']);
                $pseudo = $infosUser->pseudo;
                $userId = $infosUser->userID;
                $created_at = $infosUser->userDate;

                // $user = new Users($pseudo, $created_at, $userId);
                $user = new Users($pseudo, $created_at, $userId);
                $user->setPassword(password_hash($nouveauPassword, PASSWORD_DEFAULT));
                $resultat = $this->usersModel->updatePassword($user);
                if ($resultat) {
                    Toolbox::ajouterMessageAlerte("Mot de passe réinitialisé avec succès", "vert");
                    header("Location: " . URL . "accueil");
                    exit;
                } else {
                    Toolbox::ajouterMessageAlerte("Une erreur est survenue", "rouge");
                    header("Location: " . URL . "accueil");
                    exit;
                }
            }
        } else {
            Toolbox::ajouterMessageAlerte("Token invalide ou expiré", "rouge");
            header("Location: " . URL . "accueil");
            exit;
        }
    }

    private function messageAlert($message, $page)
    {
        Toolbox::ajouterMessageAlerte($message, 'rouge');
        header("Location: " . URL . $page);
        exit;
    }

    private function reinitialiserPassword($jwt)
    {
        // $jwt = new JWTService();
        // if ($jwt->isValid($tokenToVerify) && !$jwt->isExpired($tokenToVerify) && $jwt->check($tokenToVerify, SECRET)) {
        //     $payload = $jwt->getPayload($tokenToVerify);
        //     $userId = $payload['userID'];
        // }
        $data_page = [
            "pageDescription" => "Page de réinitialisation de mot de passe",
            "pageTitle" => "Réinitialiser mot de passe",
            "view" => "../Views/Utilisateur/viewResetPassword.php",
            "template" => "../Views/common/template.php",
            "css" => "public/style/resetPasswordStyle.css",
            "script" => "public/js/validFormResetPassword.js",
            'tokenCSRF' => $_SESSION['tokenCSRF'],
            "jwt" => $jwt
        ];
        $this->render($data_page);
    }

    private function sendEmailPassForgot($email)
    {
        $userInfos = $this->usersModel->getUserBy('email', $email);

        if ($userInfos) {
            $userId = $userInfos->userID;
            $pseudo = $userInfos->pseudo;
            $cheminTemplate = '../Views/templateMail/templateForgotPassword.html';
            $token = $this->createToken($userId, $pseudo, $email);
            $route = URL . "reinitialiserPassword/" . $token;
            $sujet = 'Réinitialisation du mot de passe sur Guitare Forum';

            if (Toolbox::sendMail($pseudo, $email, $route, $sujet, $cheminTemplate)) {
                $message = "Un mail a été envoyé sur votre boite mail afin de réinitialiser votre mot de passe !";
                Toolbox::ajouterMessageAlerte($message, 'vert');
                Toolbox::dataJson(true, "email envoyé");
            } else {
                $message = "Une erreur est survenue, veuillez contacter l'administrateur";
                Toolbox::dataJson(false, $message);
            }
        } else {
            Toolbox::dataJson(false, "Adresse email inconnue");
            // Toolbox::ajouterMessageAlerte("Adresse email inconnue", "rouge");
        }
    }

    private function createToken($userId, $pseudo = null, $email = null)
    {
        $header = [
            'typ' => 'JWT',
            'alg' => 'HS256'
        ];
        $payload = [
            'userID' => $userId,
            'pseudo' => $pseudo,
            'email' => $email
        ];
        $jwt = new JWTService();

        //Si "email" n'est pas null, c'est donc un token destiné à l'envoi d'un mail. Dans ce cas précis le token aura une durée de validité de 3h. Si "email" reste null, ce sera un token de connexion d'une durée de validité de 86400s -> 24h.
        $validity = $email ? 10800 : 86400;
        return $jwt->generate($header, $payload, SECRET, $validity);
    }

    private function forgotView()
    {

        $data_page = [
            "pageDescription" => "Page : j'ai oublié mon de mot de passe",
            "pageTitle" => "Page pour réinitialiser le mot de passe",
            "view" => "../Views/Utilisateur/viewForgot.php",
            "template" => "../Views/common/template.php",
            "css" => "public/style/forgotStyle.css",
            "script" => "public/js/forgot.js",
            "tokenCSRF" => $_SESSION["tokenCSRF"]
        ];
        $this->render($data_page);
    }
}
