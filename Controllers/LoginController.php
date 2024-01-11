<?php

namespace Controllers;



use Models\UsersModel;
use Controllers\MainController;
use Controllers\Services\Toolbox;
use Controllers\Services\Securite;
use Controllers\Traits\VerifPostTrait;
// use Entities\Messages;

// include '../Controllers\Services\JWTService\configJWT.php';

// require_once '/path/to/HTMLPurifier.auto.php';
class LoginController extends MainController
{

    use VerifPostTrait;
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
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (Securite::verifCSRF()) {
                if (!empty($_POST['pseudo']) && !empty($_POST['password'])) {
                    $pseudo = htmlspecialchars($_POST['pseudo']);
                    $password = htmlspecialchars($_POST['password']);

                    //j'ai placé un input hidden qui contient l'url précédent. 
                    $previousURL = filter_var($_POST['previousURL'] ?? "index.php", FILTER_SANITIZE_URL);
                    $userPassBDD = $this->usersModel->getUserByPseudo($pseudo)->password;

                    if (Securite::verifPassword($password, $userPassBDD)) {

                        $user = $this->usersModel->getUserinfo($pseudo);
//                        var_dump($user);

                        // Toolbox::dataJson(false, 'hahahhala',  $user);
                        // die;
                        if ($user->isValid) {

                            //je décide d'enregistrer un minium d'info pour ne pas surcharger le serveur ????, avec des sessions qui pourrait contenir trop d'info ???. je vais privilgier les requête sql pour afficher des infos détaillées, comme les données personelles, et les messages associé à l'utilisateur.
                            $filepathAvatar = $user->userID . '/' . $user->avatar;
                            $_SESSION['profil'] = [
                                'userID' => $user->userID,
                                'pseudo' => $user->pseudo,
                                'filepathAvatar' => $filepathAvatar,
                                'userGuitare' => $user->guitare,
                                'messagesCount' => $user->messagesCount,
                                'roleName' => $user->roleName,
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
                        }

                        $userID =  $user->userID;
                        $message = "Compte non validé ! Cliquez sur <a href='" . URL . "index.php?controller=register&action=returnToken&userID=" . $userID . "'>CE LIEN</a> pour renvoyer un mail d'activation.";
                        Toolbox::dataJson(false, $message);
                        exit;
                    }

                    Toolbox::dataJson(false, "Identifiants incorrects");
                    exit;
                }

                Toolbox::dataJson(false, "Erreur champs de saisie");
                header("Location:index.php");
                exit;
            }

            Toolbox::ajouterMessageAlerte("Session expirée, veuillez recommencer", 'rouge');

            //Les "unset" peuvent être utiles pour des raisons de sécurité, car cela empêche toute utilisation ultérieure de ces données de session potentiellement compromises. De plus, cela garantit que l’utilisateur doit se reconnecter et obtenir un nouveau jeton CSRF avant de poursuivre,
            unset($_SESSION['profil'], $_SESSION['tokenCSRF']);
            Toolbox::dataJson(false, "expired token");
            exit;
        }

        header("Location:index.php");
        exit;
    }
}
