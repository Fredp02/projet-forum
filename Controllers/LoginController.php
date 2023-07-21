<?php

namespace Controllers;



use Models\UsersModel;
use Controllers\MainController;
use Controllers\Services\Toolbox;
use Controllers\Services\Securite;
// use Entities\Messages;

// include '../Controllers\Services\JWTService\configJWT.php';

// require_once '/path/to/HTMLPurifier.auto.php';
class LoginController extends MainController
{


    private $usersModel;
    private $message; //getter-setter de l'entité messages
    private $messageModel;
    // private $user; //getter-setter de l'entité user

    public function __construct()
    {
        $this->usersModel = new UsersModel();
        // $this->messageModel = new MessagesModel();
        // $this->message = new Messages();
        // $this->user = new User();
    }
    public function index()
    {
        if (!Securite::isConnected()) {
            $data_page = [
                "pageDescription" => "Page de connexion au site Guitare Forum",
                "pageTitle" => "Connexion | Guitare Forum",
                "view" => "../Views/account/viewLogin.php",
                "template" => "../Views/common/template.php",
                "css" => "./style/loginStyle.css",
                "script" => "./js/validFormPageLogin.js",
                "tokenCSRF" => $_SESSION['tokenCSRF'],
                "previousURL" => $_SERVER['HTTP_REFERER'] ?? "index.php",
            ];
            $this->render($data_page);
        } else {
            header("Location:index.php");
            exit;
        }
    }
    public function validationlogin()
    {

        if (!Securite::isConnected()) {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                if (!empty($_POST['tokenCSRF']) && hash_equals($_SESSION['tokenCSRF'], $_POST['tokenCSRF'])) {

                    if (!empty($_POST['pseudo']) && !empty($_POST['password'])) {
                        $pseudo = htmlspecialchars($_POST['pseudo']);
                        $password = htmlspecialchars($_POST['password']);

                        //j'ai placé un input hidden qui contient l'url précédent. obligé car lorsque le script va sur cette route 'case "validationLogin":" le contenu de la variable http_referrer c'est la page de connexion, et pas celle encore d'avant. 
                        $previousURL = htmlspecialchars($_POST['previousURL'] ?? "");
                        $userPassBDD = $this->usersModel->getUserByPseudo($pseudo)->password;

                        if (Securite::verifPassword($password, $userPassBDD)) {


                            $user = $this->usersModel->getUserinfo($pseudo);

                            // Toolbox::dataJson(false, 'hahahhala',  $user);
                            // die;
                            if ($user->isValid) {

                                //du coup je décide d'enregistrer un minium d'info pour ne pas surcharger le serveur ????, avec des sessions qui pourrait contenir trop d'info ???. je vais privilgier les requête sql pour afficher des infos détaillées, comme les données personelles, et les messages associé à l'utilisateur.
                                $filepathAvatar = $user->userID . '/' . $user->avatar;
                                $_SESSION['profil'] = [
                                    'userID' => $user->userID,
                                    'pseudo' => html_entity_decode($user->pseudo),
                                    'filepathAvatar' => $filepathAvatar,
                                    'userGuitare' => html_entity_decode($user->guitare),
                                    'messagesCount' => html_entity_decode($user->messagesCount),
                                ];

                                Toolbox::dataJson(
                                    true,
                                    "Connexion OK",
                                    $data = [
                                        'pseudo' => $user->pseudo,
                                        'filepathAvatar' => $filepathAvatar,
                                        'id' => $user->userID,
                                        'previousURL' => $previousURL
                                    ]
                                );
                                exit;
                            } else {
                                $userID =  $user->userID;
                                $message = "Compte non validé ! Cliquez sur <a href='" . URL . "index.php?controller=register&action=returnToken&userID=" . $userID . "'>CE LIEN</a> pour renvoyer un mail de validation.";
                                Toolbox::dataJson(false, $message);
                                exit;
                            }
                        } else {
                            Toolbox::dataJson(false, "Identifiants incorrects");
                            exit;
                        }
                    } else {
                        Toolbox::dataJson(false, "Erreur champs de saisie");
                        header("Location:index.php");
                        exit;
                    }
                } else {
                    Toolbox::ajouterMessageAlerte("Erreur token, veuillez recommencer", 'rouge');
                    unset($_SESSION['profil']);
                    unset($_SESSION['tokenCSRF']);
                    Toolbox::dataJson(false, "expired token");
                    header("Location:index.php");
                    exit;
                }
            } else {
                Toolbox::dataJson(false, "Erreur");
                header("Location:index.php");
                exit;
            }
        } else {
            header("Location:index.php");
            exit;
        }
    }
}