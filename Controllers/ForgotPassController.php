<?php

namespace Controllers;

use Entities\Users;
use Models\UsersModel;
use Controllers\MainController;
use Controllers\Services\Toolbox;
use Controllers\Services\Securite;
use Controllers\Services\JWTService\JWTService;

include 'Services\JWTService\configJWT.php';

class ForgotPassController extends MainController
{
    private $usersModel;

    public function __construct()
    {
        $this->usersModel = new UsersModel();
    }

    public function forgotPass($action)
    {

        /**
         * URL  /methode_public  / methode_privée    /  arguments
         * !         [0]            [1]                   [2] 
         * ! 1 -> forgotPass      /forgotPassView
         * ! 2 -> forgotPass      /sendEmail            / $email
         * ! 3 -> forgotPass      /resetPassView        / $JWT
         * ! 4 -> forgotPass      /validResetPass 
         * 
         */
        if (!Securite::isConnected()) {
            $this->$action();
        } else {
            header("Location: " . URL . "home");
            exit;
        }
    }

    private function forgotPassView()
    {

        $data_page = [
            "pageDescription" => "Page : j'ai oublié mon de mot de passe",
            "pageTitle" => "Page pour réinitialiser le mot de passe",
            "view" => "../Views/forgotPass/forgotPassView.php",
            "template" => "../Views/common/template.php",
            "css" => "/style/forgotPassStyle.css",
            "script" => "/js/forgotPass.js",
            "tokenCSRF" => $_SESSION["tokenCSRF"]
        ];
        $this->render($data_page);
    }

    private function sendEmail()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['email'])) {
            if (Securite::verifCSRF()) {
                $email = htmlspecialchars($_POST['email']);
                $userInfos = $this->usersModel->getUserBy('email', $email);

                if ($userInfos) {
                    $userId = $userInfos->userID;
                    $pseudo = $userInfos->pseudo;
                    $cheminTemplate = '../Views/templateMail/templateForgotPassword.html';
                    $token = Securite::createTokenJWT($userId, $pseudo, $email);
                    $route = URL . "forgotPass/resetPassView/" . $token;
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
                }
            } else {
                unset($_SESSION['profil']);
                unset($_SESSION['tokenCSRF']);
                Toolbox::dataJson(false, "expired token");
                exit;
            }
        } else {
            Toolbox::dataJson(false, "Erreur transmission POST");
            exit;
        }
    }

    private function resetPassView()
    {
        $url = explode("/", filter_var($_GET['page'], FILTER_SANITIZE_URL));
        $jwt = $url[2];
        $data_page = [
            "pageDescription" => "Page de réinitialisation de mot de passe",
            "pageTitle" => "Réinitialiser mot de passe",
            "view" => "../Views/forgotPass/resetPassView.php",
            "template" => "../Views/common/template.php",
            "css" => "/style/resetPasswordStyle.css",
            "script" => "/js/validFormResetPassword.js",
            'tokenCSRF' => $_SESSION['tokenCSRF'],
            "jwt" => $jwt
        ];
        $this->render($data_page);
    }

    private function validResetPass()
    {
        //on récupère mes paramètres
        $url = explode("/", filter_var($_GET['page'], FILTER_SANITIZE_URL));
        $tokenToVerify = $url[2];

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['nouveauPassword']) && !empty($_POST['confirmPassword'])) {

            if (Securite::verifCSRF()) {
                $nouveauPassword = htmlspecialchars($_POST['nouveauPassword']);
                $confirmPassword = htmlspecialchars($_POST['confirmPassword']);

                $regexpPassword = "/^(?=.*[A-Z])(?=.*[!@#$%^&*()_+\-=\[\]{};':\"\\|,.<>\/?].*[!@#$%^&*()_+\-=\[\]{};':\"\\|,.<>\/?])[a-zA-Z0-9!@#$%^&*()_+\-=\[\]{};':\"\\|,.<>\/?]{8,50}$/";


                $jwt = new JWTService();
                if ($jwt->isValid($tokenToVerify) && !$jwt->isExpired($tokenToVerify) && $jwt->check($tokenToVerify, SECRET)) {

                    if (!preg_match($regexpPassword, $nouveauPassword)) {
                        $message = "Le mot de passe doit contenir entre 8 et 50 caractères dont au moins 2 caractères spéciaux et une majuscule";
                        Toolbox::ajouterMessageAlerte($message, 'rouge');
                        header("Location: " . URL . 'forgotPass/validResetPass/' . $tokenToVerify);
                        exit;
                    } elseif ($nouveauPassword !== $confirmPassword) {
                        $message = "Les mots de passe ne sont pas identiques";
                        Toolbox::ajouterMessageAlerte($message, 'rouge');
                        header("Location: " . URL . 'forgotPass/validResetPass/' . $tokenToVerify);
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
            } else {
                Toolbox::ajouterMessageAlerte("Erreur token", 'rouge');
                header("Location: " . URL . "home");
                exit;
            }
        } else {
            Toolbox::ajouterMessageAlerte("Les deux champs sont requis", 'rouge');
            header("Location: " . URL . "forgotPass/resetPassView/" . $tokenToVerify);
            exit;
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


}
