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


    public function index()
    {
        $data_page = [
            "pageDescription" => "Page de création de compte",
            "pageTitle" => "Inscription",
            "view" => "../Views/account/viewRegister.php",
            "template" => "../Views/common/template.php",
            "css" => "./style/registerStyle.css",
            "script" => "./js/validFormRegister.js",
            "tokenCSRF" => $_SESSION['tokenCSRF']
        ];
        $this->render($data_page);
    }

    public function validation()
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

                    $regexpPassword = "/^(?=.*[A-Z])(?=.*[!@#$%^&*()_+\-=\[\]{};':\"\\|,.<>\/?].*[!@#$%^&*()_+\-=\[\]{};':\"\\|,.<>\/?])[a-zA-Z0-9!@#$%^&*()_+\-=\[\]{};':\"\\|,.<>\/?]{8,50}$/";

                    if ($this->usersModel->getUserByPseudo($pseudo)) {
                        Toolbox::dataJson(false, "Pseudo déjà utilisé", 'pseudo');
                        exit;
                    } else if ($this->usersModel->getUserByEmail($email)) {
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


                        $user = new Users();
                        $user->setPseudo($pseudo);
                        $user->setEmail($email);
                        $user->setUserDate(Toolbox::creerDateActuelle());
                        $user->setPassword(password_hash($password, PASSWORD_DEFAULT));
                        $user->setGuitare('Non renseigné');
                        $user->setVille('Non renseigné');
                        $user->setEmploi('Non renseigné');
                        $user->setAvatar('avatarDefault.jpg');


                        if ($this->usersModel->inscription($user)) {
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
                            $route = URL . "index.php?controller=register&action=accountActivation&token=" . $token;
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

    public function returnToken($userID)
    {
        $user = $this->usersModel->getUserById($userID);
        //si le user existe et qu'il n'est pas encore valide
        if ($user && !$user->isValid) {

            $pseudo = $user->pseudo;
            $email = $user->email;
            $token = Securite::createTokenJWT($userID, $pseudo, $email);
            $route = URL . "index.php?controller=register&action=accountActivation&token=" . $token;
            $cheminTemplate = '../Views/templateMail/templateInscription.html';
            $sujet = 'Validation inscription Guitare Forum';

            if (Toolbox::sendMail($pseudo, $email, $route, $sujet, $cheminTemplate)) {
                $message = "Un mail de validation a été envoyé sur votre boite mail !";
                Toolbox::ajouterMessageAlerte($message, 'vert');
                header("Location:index.php");
                exit;
            } else {
                $message = "Problème rencontré. Veuillez contacter l'administrateur du site.";
                Toolbox::ajouterMessageAlerte($message, 'rouge');
                header("Location:index.php");
                exit;
            }
        } else {
            //si le $user existé (mais que le compte était déjà validé)
            $message =  $user ? "Compte déjà validé" : 'Erreur';
            Toolbox::ajouterMessageAlerte($message, 'rouge');
            header("Location:index.php");
            exit;
        }
    }

    public function accountActivation($token)
    {
        // $url = explode("/", filter_var($_GET['page'], FILTER_SANITIZE_URL));

        $jwt = new JWTService();
        if ($jwt->isValid($token) && !$jwt->isExpired($token) && $jwt->check($token, SECRET)) {
            $payload = $jwt->getPayload($token);
            $user = new Users;
            $user->setUserId($payload['userID']);
            if ($this->usersModel->accountActivation($user)) {
                Toolbox::ajouterMessageAlerte("Votre compte a été validé avec succès, vous pouvez maintenant vous connecter", "vert");
            } else {
                Toolbox::ajouterMessageAlerte("Problème inatendue lors de l'activation de votre compte", "rouge");
            }
        } else {
            Toolbox::ajouterMessageAlerte("token NON valide", "rouge");
        }

        header("Location:index.php");
        exit;
    }
}
