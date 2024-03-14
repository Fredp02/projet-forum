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
    public $usersModel;

    public function __construct()
    {
        $this->usersModel = new UsersModel();
    }



    public function index()
    {


        $data_page = [
            "pageDescription" => "Page : j'ai oublié mon de mot de passe",
            "pageTitle" => "Page pour réinitialiser le mot de passe",
            "view" => "../Views/forgotPass/forgotPassView.php",
            "template" => "../Views/common/template.php",
            "css" => "./style/forgotPassStyle.css",
            "script" => "./js/forgotPass.js",
            "tokenCSRF" => $_SESSION["tokenCSRF"]
        ];
        $this->render($data_page);
    }

    public function sendEmail()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['email'])) {
            if (Securite::verifCSRF()) {
                $email = htmlspecialchars($_POST['email']);
                $userInfos = $this->usersModel->getUserByEmail($email);

                if ($userInfos) {
                    $userId = $userInfos->userID;
                    $pseudo = $userInfos->pseudo;
                    $token = Securite::createTokenJWT($userId, $pseudo, $email);
                    $route = URL . "?controller=forgotPass&action=resetPassView&tokenJWT=" . $token;

                    $sujet = 'Réinitialisation du mot de passe sur Guitare Forum';
                    $template = '../Views/templateMail/templateForgotPassword.html';
                    $contentMail = Toolbox::createEmailContent($template, $pseudo, $route);

                    if (Toolbox::sendMail($email, $sujet, $contentMail)) {
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

    public function resetPassView($tokenJWT)
    {

        $data_page = [
            "pageDescription" => "Page de réinitialisation de mot de passe",
            "pageTitle" => "Réinitialiser mot de passe",
            "view" => "../Views/forgotPass/resetPassView.php",
            "template" => "../Views/common/template.php",
            "css" => "./style/resetPasswordStyle.css",
            "script" => "./js/validFormResetPassword.js",
            'tokenCSRF' => $_SESSION['tokenCSRF'],
            "jwt" => $tokenJWT
        ];
        $this->render($data_page);
    }

    public function validResetPass($tokenJWT)
    {


        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['nouveauPassword']) && !empty($_POST['confirmPassword'])) {

            if (Securite::verifCSRF()) {
                $nouveauPassword = htmlspecialchars($_POST['nouveauPassword']);
                $confirmPassword = htmlspecialchars($_POST['confirmPassword']);

                $regexpPassword = "/^(?=.*[A-Z])(?=.*[!@#$%^&*()_+\-=\[\]{};':\"\\|,.<>\/?].*[!@#$%^&*()_+\-=\[\]{};':\"\\|,.<>\/?])[a-zA-Z0-9!@#$%^&*()_+\-=\[\]{};':\"\\|,.<>\/?]{8,50}$/";


                $jwt = new JWTService();

                if ($jwt->isValid($tokenJWT) && !$jwt->isExpired($tokenJWT) && $jwt->check($tokenJWT, SECRET)) {

                    if (!preg_match($regexpPassword, $nouveauPassword)) {
                        $message = "Le mot de passe doit contenir entre 8 et 50 caractères dont au moins 2 caractères spéciaux et une majuscule";
                        Toolbox::ajouterMessageAlerte($message, 'rouge');
                        header("Location:?controller=forgotPass&action=resetPassView&tokenJWT=" . $tokenJWT);
                        exit;
                    } elseif ($nouveauPassword !== $confirmPassword) {
                        $message = "Les mots de passe ne sont pas identiques";
                        Toolbox::ajouterMessageAlerte($message, 'rouge');
                        header("Location:?controller=forgotPass&action=resetPassView&tokenJWT=" . $tokenJWT);
                        exit;
                    } else {
                        $payload = $jwt->getPayload($tokenJWT);
                        $payload['userID'];
                        $infosUser = $this->usersModel->getUserById($payload['userID']);

                        $userId = $infosUser->userID;


                        $user = new Users();
                        $user->setUserId($userId);
                        $user->setPassword(password_hash($nouveauPassword, PASSWORD_DEFAULT));
                        $resultat = $this->usersModel->updatePassword($user);
                        if ($resultat) {
                            Toolbox::ajouterMessageAlerte("Mot de passe réinitialisé avec succès", "vert");
                        } else {
                            Toolbox::ajouterMessageAlerte("Une erreur est survenue", "rouge");
                        }
                    }
                } else {
                    Toolbox::ajouterMessageAlerte("Token invalide ou expiré", "rouge");
                }
            } else {
                Toolbox::ajouterMessageAlerte("Erreur token", 'rouge');
            }
        } else {
            Toolbox::ajouterMessageAlerte("Les deux champs sont requis", 'rouge');
            header("Location:?controller=forgotPass&action=resetPassView&tokenJWT=" . $tokenJWT);
            exit;
        }
        header("Location:index.php");
        exit;
    }
}
