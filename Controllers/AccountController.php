<?php

namespace Controllers;

use SplFileInfo;
use Entities\Users;
use Models\UsersModel;
use Controllers\Services\Toolbox;
use Controllers\Services\Securite;
use Controllers\Services\JWTService\JWTService;

include 'Services\JWTService\configJWT.php';

class AccountController extends MainController
{
    private $usersModel;

    public function __construct()
    {
        $this->usersModel = new UsersModel();
    }

    public function account($action)
    {
        if (Securite::isConnected()) {
            $this->$action();
        } else {
            header("Location: " . URL . "home");
            exit;
        }
    }

    private function profil()
    {

        $tokenCSRF = $_SESSION["tokenCSRF"];
        $pseudo = $_SESSION['profil']['pseudo'];
        $user = $this->usersModel->getUserinfo($pseudo);

        $userDatas = [
            'userID' => $user->userID,
            'pseudo' => $user->pseudo,
            'userDate' => $user->userDate,
            'role' => $user->roleName,
            'email' => $user->email,
            'guitare' => $user->guitare,
            'ville' => $user->ville,
            'emploi' => $user->emploi,
            'avatar' => $user->avatar
        ];

        $data_page = [
            "pageDescription" => "Page du profil",
            "pageTitle" => "Profil",
            "view" => "../Views/account/viewProfil.php",
            "template" => "../views/common/template.php",
            "css" => "/style/profilStyle.css",
            "script" => "/js/profil.js",
            'tokenCSRF' => $tokenCSRF,
            "userDatas" => $userDatas
        ];
        $this->render($data_page);
    }
    private function datasFormProfil()
    {
        $userID = $_SESSION["profil"]["userID"];
        $user = $this->usersModel->getUserById($userID);

        $userDatasForm = [
            'email' => html_entity_decode($user->email),
            'guitare' => html_entity_decode($user->guitare),
            'ville' => html_entity_decode($user->ville),
            'emploi' => html_entity_decode($user->emploi),
        ];
        Toolbox::dataJson(true, "reponse ok", $userDatasForm);
        exit;
    }
    private function editAvatar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!empty($_POST['tokenCSRF']) && hash_equals($_SESSION['tokenCSRF'], $_POST['tokenCSRF'])) {
                if (isset($_FILES['avatarPhoto']) && $_FILES['avatarPhoto']['error'] == 0) {
                    $dataPhoto = $_FILES['avatarPhoto'];
                    //je crée un tableau avec les types mime autorisés
                    $typeMime = [
                        "jpg" => "image/jpg",
                        "jpeg" => "image/jpeg",
                        "gif" => "image/gif",
                        "png" => "image/png"
                    ];

                    //on récupère les info de la photo
                    $infoFichier = new SplFileInfo($dataPhoto['name']);
                    //récupération de l'extension du fichier (en minuscule).
                    $extension = strtolower($infoFichier->getExtension());

                    $regexExtention = '/^.*\.(jpeg|jpg|gif|png)$/i'; //le "i" c'est insensible à la casse

                    //on verifie le type mime du fichier
                    if (!in_array($dataPhoto['type'], $typeMime)) {
                        Toolbox::ajouterMessageAlerte("Le fichier n'est pas une image valide. Extensions autorisées : png, gif ou jpeg(jpg)", 'rouge');
                        header("Location: " . URL . "account/profil");
                        exit;
                    }
                    //on ajoute une couche de sécurité, peut-être inutile
                    if (!preg_match($regexExtention, $dataPhoto['name'])) {
                        Toolbox::ajouterMessageAlerte("Le fichier n'est pas une image valide. Extensions autorisées : png, gif ou jpeg(jpg)", 'rouge');
                        header("Location: " . URL . "account/profil");
                        exit;
                    }

                    //on vérifie sa taille
                    if ($dataPhoto['size'] > 153600) {
                        Toolbox::ajouterMessageAlerte("Le poids de l'image doit être inférieure à 150ko", 'rouge');
                        header("Location: " . URL . "account/profil");
                        exit;
                    }
                    $infoSize = getimagesize($dataPhoto['tmp_name']);

                    //et on vérifie ses dimensions
                    if ($infoSize[0] > 200 || $infoSize[1] > 200) {
                        Toolbox::ajouterMessageAlerte("Le fichier doit avoir une largeur et une hauteur maximale de 200 pixels", 'rouge');
                        header("Location: " . URL . "account/profil");
                        exit;
                    }

                    //si l'image respecte les conditions ...
                    $userData = $this->usersModel->getUserById($_SESSION['profil']['userID']);

                    $userId = $userData->userID;
                    $ancienAvatar = $userData->avatar;
                    // On initialise le chemin du dossier dans lequel la photo sera enregistrée
                    $filePath = 'images/profils/' . $userId;

                    //on va renommer la photo avec un identifiant unique.
                    //2 paramètres avec uniqid : un préfix (ici ce sera $userId), et un booleen à true pour qu'il génère d'autre chiffres après afin de maximiser l'unicité du renommage. et on rajoute l'extension
                    $nouvelAvatar = uniqid($userId, true) . '.' . $extension;

                    //on instancie l'objet user
                    // $user = new Users($userData->pseudo, $userData->userDate, $userId);
                    $user = new Users();

                    //on lui attribue le nom du nouvel avatar
                    $user->setUserId($userId);
                    $user->setAvatar($nouvelAvatar);

                    //on déplace l'image des "temporaire" dans le dossier du user
                    $moveAvatar = move_uploaded_file($dataPhoto['tmp_name'], $filePath . '/' . $nouvelAvatar);

                    //si déplacement du nouvel avatar ok
                    if ($moveAvatar) {

                        //si enregistrement en bdd du nouvel avatar ok
                        if ($this->usersModel->modifAvatarProfil($user)) {
                            //on supprime l'ancien avatar
                            unlink($filePath . DIRECTORY_SEPARATOR . $ancienAvatar);
                            //on met à jour la session avec le nouvel avatar
                            $_SESSION['profil']['avatar'] = $nouvelAvatar;
                        } else {
                            Toolbox::dataJson(false, "Problème rencontré lors de l'enregistrement de l'image");
                            exit;
                        }

                        //si déplacement du nouvel avatar échoué :
                    } else {
                        Toolbox::dataJson(false, "Problème rencontré lors de l'enregistrement de l'image");
                        exit;
                    }

                    // Si aucun "exit" précédent

                    //on créé les données que JS va récupérer
                    $data = [
                        'userId' => $userId,
                        'avatar' => $_SESSION['profil']['avatar']
                    ];
                    //et on envoie la réponse en json
                    Toolbox::dataJson(true, "Avatar ok", $data);
                    exit;
                } else {
                    $message = "Une erreur est survenue...";
                    Toolbox::dataJson(false, $message);
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
        } else {
            header("Location: " . URL . "home");
            exit;
        }
    }
    private function editEmail()
    {
        $tokenJWT = explode("/", filter_var($_GET['page'], FILTER_SANITIZE_URL))[2] ?? "";


        //!Si requete POST : envoie du mail avec tokenJWT
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!empty($_POST['tokenCSRF']) && hash_equals($_SESSION['tokenCSRF'], $_POST['tokenCSRF'])) {
                // Le jeton anti-CSRF est valide, traiter les données du formulaire
                if (!empty($_POST['email']) && filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {

                    $user = $this->usersModel->getUserById($_SESSION['profil']['userID']);
                    $userId = $user->userID;
                    $pseudo = $user->pseudo;

                    //ici on peut récupérer son email "avant modification". Cela pourrait nous permettre d'envoyer un email à cette adresse en indiquant que si il n'est pas à l'origine de cette modification, il peut contacter l'administrateur du site :  
                    // $userAncienEmail = $user->email;

                    $nouveauEmail = htmlspecialchars($_POST['email']);
                    $token = Securite::createTokenJWT($userId, $pseudo, $nouveauEmail);
                    $cheminTemplate = '../Views/templateMail/templateEditEmail.html';
                    $route = URL . "account/editEmail/" . $token;
                    $sujet = 'Validation adresse email sur Guitare Forum';
                    if (Toolbox::sendMail($pseudo, $nouveauEmail, $route, $sujet, $cheminTemplate)) {
                        $message = "Un email de validation a été envoyé sur cette adresse email. Ce mail sera valide pendant 3h";
                        Toolbox::dataJson(true, $message);
                        exit;
                    } else {
                        Toolbox::dataJson(false, 'Une erreur est survenue');
                        exit;
                    }
                } else {
                    Toolbox::dataJson(false, "Erreur : données vides");
                    exit;
                }
            } else {
                Toolbox::ajouterMessageAlerte("Session expirée, veuillez recommencer)", 'rouge');
                unset($_SESSION['profil']);
                unset($_SESSION['tokenCSRF']);
                Toolbox::dataJson(false, "expired token");
                exit;
            }
        } elseif ($tokenJWT !== '') { //!Si paramètre en GET : on vérifie le JWT et on modifie l'email
            $jwt = new JWTService();
            if ($jwt->isValid($tokenJWT) && !$jwt->isExpired($tokenJWT) && $jwt->check($tokenJWT, SECRET)) {
                $payload = $jwt->getPayload($tokenJWT);
                $infosUser = $this->usersModel->getUserById($payload['userID']);
                $userId = $infosUser->userID;

                $user = new Users();
                $user->setUserId($userId);
                $user->setEmail($payload['email']);
                if ($this->usersModel->editEmailUser($user)) {
                    Toolbox::ajouterMessageAlerte("Adresse email modifiée avec succès", "vert");
                    header("Location: " . URL . "home");
                    exit;
                }
            } else {
                Toolbox::ajouterMessageAlerte("token NON valide", "rouge");
                header("Location: " . URL . "home");
                exit;
            }
        } else { //!sinon redirection
            header("Location: " . URL . "home");
            exit;
        }
    }
    private function editPassword()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['tokenCSRF']) && hash_equals($_SESSION['tokenCSRF'], $_POST['tokenCSRF'])) {



            if (!empty($_POST['ancienPassword']) && !empty($_POST['nouveauPassword']) && !empty($_POST['confirmPassword'])) {
                $ancienPassword = htmlspecialchars($_POST['ancienPassword']);
                $nouveauPassword = htmlspecialchars($_POST['nouveauPassword']);
                $confirmPassword = htmlspecialchars($_POST['confirmPassword']);

                $regexpPassword = "/^(?=.*[A-Z])(?=.*[!@#$%^&*()_+\-=\[\]{};':\"\\|,.<>\/?].*[!@#$%^&*()_+\-=\[\]{};':\"\\|,.<>\/?])[a-zA-Z0-9!@#$%^&*()_+\-=\[\]{};':\"\\|,.<>\/?]{8,50}$/";

                $userID = $_SESSION['profil']['userID'];
                $userData = $this->usersModel->getUserById($userID);
                $userPassBDD = $userData->password;
                if (Securite::verifPassword($ancienPassword, $userPassBDD)) {

                    if (!preg_match($regexpPassword, $nouveauPassword)) {
                        Toolbox::dataJson(false, "Le mot de passe doit contenir entre 8 et 50 caractères dont au moins 2 caractères spéciaux et une majuscule");
                        exit;
                    }
                    if ($nouveauPassword !== $confirmPassword) {
                        Toolbox::dataJson(false, "Les mots de passe ne correspondent pas");
                        exit;
                    }
                    $user = new Users();
                    $user->setUserId($userData->userID);
                    $user->setPassword(password_hash($nouveauPassword, PASSWORD_DEFAULT));

                    if ($this->usersModel->updatePassword($user)) {
                        Toolbox::dataJson(true, "Mot de passe modifié avec succès");
                        exit;
                    } else {
                        Toolbox::dataJson(false, "Erreur recontrée lors de la modification du mot de passe");
                        exit;
                    }
                } else {
                    Toolbox::dataJson(false, "Mot de actuel incorrect");
                    exit;
                }
            } else {
                Toolbox::dataJson(false, "Erreur : données vides");
                exit;
            }
        } else {
            Toolbox::ajouterMessageAlerte("Session expirée, veuillez recommencer", 'rouge');
            unset($_SESSION['profil']);
            unset($_SESSION['tokenCSRF']);
            Toolbox::dataJson(false, "expired token");
            exit;
        }
    }

    private function editAbout()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['tokenCSRF']) && hash_equals($_SESSION['tokenCSRF'], $_POST['tokenCSRF'])) {
            if (!empty($_POST['guitare']) && !empty($_POST['emploi']) && !empty($_POST['ville'])) {
                $guitare = trim(htmlspecialchars($_POST['guitare']));
                $emploi = trim(htmlspecialchars($_POST['emploi']));
                $ville = trim(htmlspecialchars($_POST['ville']));


                $userData = $this->usersModel->getUserById($_SESSION['profil']['userID']);
                $user = new Users();
                $user->setUserId($userData->userID);
                $user->setGuitare($guitare);
                $user->setEmploi($emploi);
                $user->setVille($ville);

                $resultat = $this->usersModel->UpdateAboutUser($user);
                if ($resultat) {
                    //! voir méthode UpdateAboutUser() pour plus d'info
                    $message = $resultat === 2 ? "Mise à jour des informations réalisées avec succès" : "Aucune modifications effectuées : valeur identique";
                    $data = [
                        'guitare' => html_entity_decode($guitare),
                        'emploi' => html_entity_decode($emploi),
                        'ville' => html_entity_decode($ville)
                    ];
                    Toolbox::dataJson(true, $message, $data);
                    exit;
                } else {
                    Toolbox::dataJson(false, "Problème rencontré lors de la mise à jour des informations");
                    exit;
                }
            } else {
                Toolbox::dataJson(false, "Erreur : données vides");
                exit;
            }
        } else {
            Toolbox::ajouterMessageAlerte("Session expirée, veuillez recommencer", 'rouge');
            unset($_SESSION['profil']);
            unset($_SESSION['tokenCSRF']);
            Toolbox::dataJson(false, "expired token");
            exit;
        }
    }

    private function deleteAccount()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            if (!empty($_POST['tokenCSRF']) && hash_equals($_SESSION['tokenCSRF'], $_POST['tokenCSRF'])) {
                $userId = $_SESSION['profil']['userID'];
                $user = $this->usersModel->getUserById($userId);
                $imageAvatar = 'images/profils/' . $userId . '/' . $user->avatar;
                unlink($imageAvatar);
                rmdir('images/profils/' . $userId);
                if ($this->usersModel->deleteAccount($userId)) {
                    Toolbox::ajouterMessageAlerte("Votre compte a été supprimé  avec succès", 'vert');
                    unset($_SESSION['profil']);
                    header("Location: " . URL . "home");
                    exit;
                } else {
                    Toolbox::ajouterMessageAlerte("Une erreur est survenue lors de la suppression", 'rouge');
                    header("Location: " . URL . "home");
                    exit;
                }
            } else {
                Toolbox::ajouterMessageAlerte("Session expirée, veuillez recommencer", 'rouge');
                unset($_SESSION['profil']);
                unset($_SESSION['tokenCSRF']);
                header("Location: " . URL . "home");
                exit;
            }
        }


        $tokenCSRF = $_SESSION["tokenCSRF"];
        $data_page = [
            "pageDescription" => "Page de suppression du compte",
            "pageTitle" => "Supprimer mon compte",
            "view" => "../Views/account/viewDeleteAccount.php",
            "template" => "../views/common/template.php",
            "css" => "/style/deleteAccountStyle.css",
            'tokenCSRF' => $tokenCSRF

        ];
        $this->render($data_page);
    }
}
