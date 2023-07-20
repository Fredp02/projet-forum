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

    public function Login()
    {
        /**
         * Les données que vous stockez dans la session sont relativement petites, statiques et fréquemment utilisées, ce qui justifie de les stocker dans la session. Cependant, les données sont aussi modifiables par l’utilisateur, ce qui implique de les mettre à jour dans la session et dans la base de données. De plus, les données sont liées à d’autres informations, comme les messages du forum, ce qui peut rendre la requête plus complexe ou coûteuse.
         * 
         * Dans ce cas, vous pouvez utiliser une combinaison de la session, de la base de données et du cache pour optimiser les performances de votre application. Par exemple, vous pouvez stocker dans la session les données qui ne changent pas souvent ou qui sont essentielles pour l’authentification, comme l’id ou le pseudo de l’utilisateur. Vous pouvez stocker dans la base de données les données qui changent souvent ou qui sont sensibles, comme l’email ou le mot de passe de l’utilisateur. Vous pouvez utiliser un cache pour stocker temporairement les données qui sont complexes ou coûteuses à récupérer depuis la base de données, comme les messages du forum liés à l’utilisateur.


         */
        if (!Securite::isConnected()) {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {

                if (!empty($_POST['tokenCSRF']) && hash_equals($_SESSION['tokenCSRF'], $_POST['tokenCSRF'])) {

                    if (!empty($_POST['pseudo']) && !empty($_POST['password'])) {
                        $pseudo = htmlspecialchars($_POST['pseudo']);
                        $password = htmlspecialchars($_POST['password']);

                        //j'ai placé un input hidden qui contient l'url précédent. obligé car lorsque le script va sur cette route 'case "validationLogin":" le contenu de la variable http_referrer c'est la page de connexion, et pas celle encore d'avant. 
                        $previousURL = htmlspecialchars($_POST['previousURL'] ?? "");
                        $this->validationLogin($pseudo, $password, $previousURL);
                        exit;
                    }
                } else {
                    Toolbox::ajouterMessageAlerte("Session expirée, veuillez recommencer", 'rouge');
                    unset($_SESSION['profil']);
                    unset($_SESSION['tokenCSRF']);
                    Toolbox::dataJson(false, "expired token");
                    exit;
                }
            } else {
                $this->viewLogin();
            }
        } else {
            header("Location: " . URL);
            exit;
        }
    }
    private function validationLogin($pseudo, $password, $previousURL)
    {
        $userPassBDD = $this->usersModel->getUserByPseudo($pseudo)->password;

        if (Securite::verifPassword($password, $userPassBDD)) {


            $user = $this->usersModel->getUserByPseudo($pseudo);

            // Toolbox::dataJson(false, 'hahahhala',  $user);
            // die;
            if ($user->isValid) {

                //du coup je décide d'enregistrer un minium d'info pour ne pas surcharger le serveur ????, avec des sessions qui pourrait contenir trop d'info ???. je vais privilgier les requête sql pour afficher des infos détaillées, comme les données personelles, et les messages associé à l'utilisateur.
                $filepathAvatar = $user->userID . '/' . $user->avatar;
                $_SESSION['profil'] = [
                    'userID' => $user->userID,
                    'pseudo' => $user->pseudo,
                    'filepathAvatar' => $filepathAvatar,
                    'userGuitare' => $user->guitare,
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
            } else {
                $userId =  $user->userID;
                $message = "Compte non validé ! Cliquez sur <a href='" . URL . "register/returnToken/" . $userId . "'>ce lien</a> pour renvoyer un mail de validation.";
                Toolbox::dataJson(false, $message);
                exit;
            }
        } else {
            $data = [
                'pseudo' => $pseudo,
                'password' => $password
            ];
            Toolbox::dataJson(false, "Identifiants incorrects", $data);
        }
    }
    private function viewLogin()
    {
        $data_page = [
            "pageDescription" => "Page de connexion au site Guitare Forum",
            "pageTitle" => "Connexion | Guitare Forum",
            "view" => "../Views/account/viewLogin.php",
            "template" => "../Views/common/template.php",
            "css" => "/style/loginStyle.css",
            "script" => "/js/validFormPageLogin.js",
            "tokenCSRF" => $_SESSION['tokenCSRF'],
            "previousURL" => $_SERVER['HTTP_REFERER'] ?? "home",
        ];
        $this->render($data_page);
    }
}
