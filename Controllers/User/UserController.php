<?php

namespace Controllers\User;



use SplFileInfo;
use Entities\Users;
use Models\UsersModel;
use Models\MessagesModel;
use Controllers\MainController;
use Controllers\Services\Toolbox;
use Controllers\Services\JWTService\JWTService;
use Controllers\Services\Securite;
use Entities\Messages;

include '../Controllers\Services\JWTService\configJWT.php';

// require_once '/path/to/HTMLPurifier.auto.php';
class UserController extends MainController
{


    private $usersModel;
    private $message; //getter-setter de l'entité messages
    private $messageModel;
    // private $user; //getter-setter de l'entité user

    public function __construct()
    {
        $this->usersModel = new UsersModel();
        $this->messageModel = new MessagesModel();
        $this->message = new Messages();
        // $this->user = new User();
    }



    /**
     * le user clic sur le bouton envoyer.
     * j'appel la route sendEmailPassForgot, et je lui envoie un email avec un nouveau template email, et un liens vers une page pour réinitilisiez son mot de passe. dans ce lien, il y aura un token jwt pour stocker son id et pseudo
     */
    // public function forgotView()
    // {

    //     $data_page = [
    //         "pageDescription" => "Page : j'ai oublié mon de mot de passe",
    //         "pageTitle" => "Page pour réinitialiser le mot de passe",
    //         "view" => "../Views/Utilisateur/viewForgot.php",
    //         "template" => "../Views/common/template.php",
    //         "css" => "public/style/forgotStyle.css",
    //         "script" => "public/js/forgot.js",
    //         "tokenCSRF" => $_SESSION["tokenCSRF"]
    //     ];
    //     $this->render($data_page);
    // }

    // public function sendEmailPassForgot($email)
    // {
    //     $userInfos = $this->usersModel->getUserBy('email', $email);

    //     if ($userInfos) {
    //         $userId = $userInfos->userID;
    //         $pseudo = $userInfos->pseudo;
    //         $cheminTemplate = '../Views/templateMail/templateForgotPassword.html';
    //         $token = $this->createToken($userId, $pseudo, $email);
    //         $route = URL . "reinitialiserPassword/" . $token;
    //         $sujet = 'Réinitialisation du mot de passe sur Guitare Forum';

    //         if (Toolbox::sendMail($pseudo, $email, $route, $sujet, $cheminTemplate)) {
    //             $message = "Un mail a été envoyé sur votre boite mail afin de réinitialiser votre mot de passe !";
    //             Toolbox::ajouterMessageAlerte($message, 'vert');
    //             Toolbox::dataJson(true, "email envoyé");
    //         } else {
    //             $message = "Une erreur est survenue, veuillez contacter l'administrateur";
    //             Toolbox::dataJson(false, $message);
    //         }
    //     } else {
    //         Toolbox::dataJson(false, "Adresse email inconnue");
    //         // Toolbox::ajouterMessageAlerte("Adresse email inconnue", "rouge");
    //     }
    // }


    //page lorsqu'on clique sur le lien de l'email reçu, avec le token en GET. C'est un formulaire.
    // public function reinitialiserPassword($jwt)
    // {
    //     // $jwt = new JWTService();
    //     // if ($jwt->isValid($tokenToVerify) && !$jwt->isExpired($tokenToVerify) && $jwt->check($tokenToVerify, SECRET)) {
    //     //     $payload = $jwt->getPayload($tokenToVerify);
    //     //     $userId = $payload['userID'];
    //     // }
    //     $data_page = [
    //         "pageDescription" => "Page de réinitialisation de mot de passe",
    //         "pageTitle" => "Réinitialiser mot de passe",
    //         "view" => "../Views/Utilisateur/viewResetPassword.php",
    //         "template" => "../Views/common/template.php",
    //         "css" => "public/style/resetPasswordStyle.css",
    //         "script" => "public/js/validFormResetPassword.js",
    //         'tokenCSRF' => $_SESSION['tokenCSRF'],
    //         "jwt" => $jwt
    //     ];
    //     $this->render($data_page);
    // }
    // public function validationResetPassword($tokenToVerify, $nouveauPassword, $confirmPassword)
    // {
    //     $regexpPassword = "/^(?=.*[A-Z])(?=.*[!@#$%^&*()_+\-=\[\]{};':\"\\|,.<>\/?].*[!@#$%^&*()_+\-=\[\]{};':\"\\|,.<>\/?])[a-zA-Z0-9!@#$%^&*()_+\-=\[\]{};':\"\\|,.<>\/?]{8,50}$/";



    //     $jwt = new JWTService();
    //     if ($jwt->isValid($tokenToVerify) && !$jwt->isExpired($tokenToVerify) && $jwt->check($tokenToVerify, SECRET)) {
    //         $page = 'reinitialiserPassword/' . $tokenToVerify;
    //         if (!preg_match($regexpPassword, $nouveauPassword)) {
    //             $this->messageAlert("Le mot de passe doit contenir entre 8 et 50 caractères dont au moins 2 caractères spéciaux et une majuscule", $page);
    //         } else if ($nouveauPassword !== $confirmPassword) {
    //             $this->messageAlert("Les mots de passe ne sont pas identiques", $page);
    //         } else {
    //             $payload = $jwt->getPayload($tokenToVerify);
    //             $payload['userID'];
    //             //ici on pourrait ajouter une couche de sécurité pour re-vérifier les champs nouveauPassword et confirmPassword
    //             $infosUser = $this->usersModel->getUserBy('userID', $payload['userID']);
    //             $pseudo = $infosUser->pseudo;
    //             $userId = $infosUser->userID;
    //             $created_at = $infosUser->userDate;

    //             // $user = new Users($pseudo, $created_at, $userId);
    //             $user = new Users($pseudo, $created_at, $userId);
    //             $user->setPassword(password_hash($nouveauPassword, PASSWORD_DEFAULT));
    //             $resultat = $this->usersModel->updatePassword($user);
    //             if ($resultat) {
    //                 Toolbox::ajouterMessageAlerte("Mot de passe réinitialisé avec succès", "vert");
    //                 header("Location: " . URL . "accueil");
    //                 exit;
    //             } else {
    //                 Toolbox::ajouterMessageAlerte("Une erreur est survenue", "rouge");
    //                 header("Location: " . URL . "accueil");
    //                 exit;
    //             }
    //         }
    //     } else {
    //         Toolbox::ajouterMessageAlerte("Token invalide ou expiré", "rouge");
    //         header("Location: " . URL . "accueil");
    //         exit;
    //     }
    // }

    // public function logout()
    // {

    //     unset($_SESSION['profil']);
    //     unset($_SESSION['tokenCSRF']);
    //     session_destroy();
    //     // setcookie(Securite::COOKIE_NAME, "", time() - 3600);
    //     header("Location: " . URL . "accueil");
    //     exit;
    // }
    // public function validationInscription($pseudo, $email, $password, $confirmPassword)
    // {
    //     $regexpPseudo = "/^[a-zA-Z0-9éèêëàâäôöûüçî ]+$/";
    //     $regexpEmail = "/^\w+([-+.']\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/";
    //     $regexpPassword = "/^(?=.*[A-Z])(?=.*[!@#$%^&*()_+\-=\[\]{};':\"\\|,.<>\/?].*[!@#$%^&*()_+\-=\[\]{};':\"\\|,.<>\/?])[a-zA-Z0-9!@#$%^&*()_+\-=\[\]{};':\"\\|,.<>\/?]{8,50}$/";

    //     if ($this->usersModel->getUserBy('pseudo', $pseudo)) {
    //         Toolbox::dataJson(false, "Pseudo déjà utilisé", 'pseudo');
    //     } else if ($this->usersModel->getUserBy('email', $email)) {
    //         Toolbox::dataJson(false, "Adresse email déjà utilisée", 'email');

    //         //ensuite j'effectue les mêmes vérifications javascript mais ici côté serveur pour ajouter une couche suplémentaire de sécurité, dans le cas où un utilisateur malveillant perce la sécurité javascript.

    //         //pseudo
    //     } else if (strlen($pseudo) < 4 || strlen($pseudo) > 50) {
    //         $this->messageAlert("Votre pseudo doit contenir  entre 4 et 50 caractères", "inscription");
    //     } else if (!preg_match($regexpPseudo, $pseudo)) {
    //         $this->messageAlert("Votre pseudo ne doit pas contenir de caractères spéciaux", "inscription");
    //         //email            
    //     } else if ($email == '') {
    //         $this->messageAlert("Le champs email est requis", "inscription");
    //     } else if (!preg_match($regexpEmail, $email)) {
    //         $this->messageAlert("Le format d'email n'est pas correct", 'inscription');
    //         //password
    //     } else if ($password == '') {
    //         $this->messageAlert("Le champs mot de passe est requis", "inscription");
    //     } else if (!preg_match($regexpPassword, $password)) {
    //         $this->messageAlert("Le mot de passe doit contenir entre 8 et 50 caractères dont au moins 2 caractères spéciaux et une majuscule", "inscription");
    //     } else if ($password !== $confirmPassword) {
    //         $this->messageAlert("Les mots de passe ne sont pas identiques", 'inscription');
    //     } else {
    //         /**
    //          * !Pourquoi je passe au constructeur le pseudo, la date actuelle (l'id sera null par defaut) ?
    //          * *parce que je pars du principe que tout ce qui ne sera pas concerné par une modification en bdd (userid, pseudo et date de création du compte) n'auront pas de setter. Donc, il faut les initialiser au construct.
    //          * *c’est une bonne pratique de passer les propriétés qui ne seront jamais modifiées dans le constructeur. Cela permet de garantir l’immuabilité de ces propriétés, et de simplifier la création des objets. On peux aussi rendre ces propriétés privées ou protégées, pour empêcher leur accès direct depuis l’extérieur de la classe. Et donc utiliser des getters pour les lire, mais pas de setters pour les écrire.
    //          * 
    //          * *Ce n’est pas obligatoire ...Cependant, l’avantage du constructeur est qu’il permet de garantir que l’objet user est créé avec un état valide et cohérent. Si on n'utilise pas de constructeur, on doit s'assurer que les propriétés de l’objet user sont bien initialisées avant de les utiliser, ce qui peut être source d’erreurs ou d’oublis. De plus, le constructeur rend le code plus clair et plus concis, car on a pas à appeler plusieurs setters pour créer un objet user.
    //          * *...l'inconvénient c'est qu'il y a plus de paramètre lors de l'instanciation.
    //          * 
    //          * * dernière remarque, je passe l'id à null par défaut car j'instancie la classe User avant l'enregistrement en BDD, donc je ne connais pas à l'avance l'id. le construct prend par defaut "null". Par contre à une prochaine instanciation, lorsque le user sera déjà enregistré, on pourra passer en 3ème paramètre son identifiant, qui ne prend plus la valeur "null", mais celle passé en paramètre. 
    //          */
    //         $user = new Users($pseudo, Toolbox::creerDateActuelle());
    //         $user->setEmail($email);
    //         $user->setPassword(password_hash($password, PASSWORD_DEFAULT));
    //         $user->setGuitare('Non renseigné');
    //         $user->setVille('Non renseigné');
    //         $user->setEmploi('Non renseigné');
    //         $user->setAvatar('avatarDefault.jpg');

    //         $resultat = $this->usersModel->inscription($user);
    //         if ($resultat) {
    //             $userId = $this->usersModel->lastInsertId();
    //             $filePath = 'images/profils/' . $userId;
    //             //Si ce dossier n'existe pas, il faut le créer
    //             if (!file_exists($filePath)) {
    //                 mkdir($filePath, 0777, true);
    //             }
    //             //je copie l'avatar de base que je place dans le dossier du User. Je fais une copie pour éviter des problèmes que j'ai rencontré (problème d'affiche, et lorsqu'il fallait changer l'avatar pour un autre, problème de filepath...)
    //             $source = 'images/profils/avatarDefault.jpg';
    //             $destination = $filePath . '/avatarDefault.jpg';
    //             copy($source, $destination);

    //             $token = $this->createToken($userId, $pseudo, $email);
    //             $cheminTemplate = '../Views/templateMail/templateInscription.html';
    //             $route = URL . "verifToken/" . $token;
    //             $sujet = 'Validation inscription Guitare Forum';

    //             if (Toolbox::sendMail($pseudo, $email, $route, $sujet, $cheminTemplate)) {
    //                 $message = "Votre compte a été créé avec succès ! Un mail de validation a été envoyé sur votre boite mail !";
    //                 Toolbox::ajouterMessageAlerte($message, 'vert');
    //             } else {
    //                 $message = "Votre compte a été créé, mais un problème est survenu lors de l'envoi du mail de confirmation. Veuillez contacter l'administrateur du site.";
    //                 Toolbox::ajouterMessageAlerte($message, 'rouge');
    //             }
    //             Toolbox::dataJson(true);
    //         } else {
    //             Toolbox::ajouterMessageAlerte("Problème rencontré", "rouge");
    //             Toolbox::dataJson(false, "Problème rencontré");
    //         }
    //     }
    // }
    // public function activatingAccount($tokenToVerify)
    // {
    //     $jwt = new JWTService();
    //     if ($jwt->isValid($tokenToVerify) && !$jwt->isExpired($tokenToVerify) && $jwt->check($tokenToVerify, SECRET)) {
    //         $payload = $jwt->getPayload($tokenToVerify);
    //         /**
    //          * *je pourrais passer par la class User et faire en sorte que la méthode "activatingUser()" utilise un getter pour récupérer l'id.
    //          * *Mais je décide autrement car je serais obligé instancier la classe User avec 3 paramètres : le pseudo, la date de création, et userId. Le userId je le connais, mais pas les deux autres. Pour les connaitre deux solutions :
    //          * * passer par une méthode du model pour récpérer les info du user en fonction de son ID, OU lors de la création du token, de passer dans le payload d'autres infomations : le pseudo et la date de création du compte. dans le cas, pas besoin de faire une requête sql.
    //          * *Donc au final je décide de faire au plus simple, je passe en paramètre l'id via la variable $payload['userID']
    //          */
    //         if ($this->usersModel->activatingUser($payload['userID'])) {
    //             Toolbox::ajouterMessageAlerte("Votre compte a été validé avec succès, vous pouvez maintenant vous connecter", "vert");
    //         }
    //     } else {
    //         Toolbox::ajouterMessageAlerte("token NON valide", "rouge");
    //     }
    // }


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
    // public function returnToken($userId)
    // {
    //     $user = $this->usersModel->getUserBy('userID', $userId);
    //     //si le user existe et qu'il n'est pas encore valide
    //     if ($user && !$user->isValid) {

    //         $pseudo = $user->pseudo;
    //         $email = $user->email;
    //         $token = $this->createToken($userId, $pseudo, $email);
    //         $cheminTemplate = '../Views/templateMail/templateInscription.html';
    //         $sujet = 'Validation inscription Guitare Forum';
    //         if (Toolbox::sendMail($pseudo, $email, $token, $sujet, $cheminTemplate)) {
    //             $message = "Un mail de validation a été envoyé sur votre boite mail !";
    //             Toolbox::ajouterMessageAlerte($message, 'vert');
    //         } else {
    //             $message = "Problème rencontré. Veuillez contacter l'administrateur du site.";
    //             Toolbox::ajouterMessageAlerte($message, 'rouge');
    //         }
    //     } else {
    //         //si le $user existé (mais que le compte était déjà validé)
    //         $message =  $user ? "Compte déjà validé" : 'Erreur';
    //         Toolbox::ajouterMessageAlerte($message, 'rouge');
    //         header("Location: " . URL);
    //         exit;
    //     }
    // }
    public function profil()
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
            "view" => "../Views/Utilisateur/viewProfil.php",
            "template" => "../views/common/template.php",
            "css" => "public/style/profilStyle.css",
            "script" => "public/js/profil.js",
            'tokenCSRF' => $tokenCSRF,
            "userDatas" => $userDatas
        ];
        $this->render($data_page);
    }

    public function datasFormProfil()
    {
        $pseudo = $_SESSION["profil"]["pseudo"];
        $user = $this->usersModel->getUserinfo($pseudo);

        $userDatasForm = [
            'email' => html_entity_decode($user->email),
            'guitare' => html_entity_decode($user->guitare),
            'ville' => html_entity_decode($user->ville),
            'emploi' => html_entity_decode($user->emploi),
        ];
        Toolbox::dataJson(true, "reponse ok", $userDatasForm);
        exit;
    }

    public function editAvatar($dataPhoto)
    {

        $token = $_SESSION['tokenCSRF'];
        // $jwt = new JWTService();
        // if ($jwt->isValid($token) && !$jwt->isExpired($token) && $jwt->check($token, SECRET)) {

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
            header("Location: " . URL . "compte/profil");
            exit;
        }
        //on ajoute une couche de sécurité, peut-être inutile
        if (!preg_match($regexExtention, $dataPhoto['name'])) {
            Toolbox::ajouterMessageAlerte("Le fichier n'est pas une image valide. Extensions autorisées : png, gif ou jpeg(jpg)", 'rouge');
            header("Location: " . URL . "compte/profil");
            exit;
        }

        //on vérifie sa taille
        if ($dataPhoto['size'] > 153600) {
            Toolbox::ajouterMessageAlerte("Le poids de l'image doit être inférieure à 150ko", 'rouge');
            header("Location: " . URL . "compte/profil");
            exit;
        }
        $infoSize = getimagesize($dataPhoto['tmp_name']);

        //et on vérifie ses dimensions
        if ($infoSize[0] > 200 || $infoSize[1] > 200) {
            Toolbox::ajouterMessageAlerte("Le fichier doit avoir une largeur et une hauteur maximale de 200 pixels", 'rouge');
            header("Location: " . URL . "compte/profil");
            exit;
        }

        //si l'image respecte les conditions ...
        $userData = $this->usersModel->getUserinfo($_SESSION['profil']['pseudo']);

        $userId = $userData->userID;
        $ancienAvatar = $userData->avatar;
        // On initialise le chemin du dossier dans lequel la photo sera enregistrée
        $filePath = 'images/profils/' . $userId;

        //on va renommer la photo avec un identifiant unique.
        //2 paramètres avec uniqid : un préfix (ici ce sera $userId), et un booleen à true pour qu'il génère d'autre chiffres après afin de maximiser l'unicité du renommage. et on rajoute l'extension
        $nouvelAvatar = uniqid($userId, true) . '.' . $extension;

        //on instancie l'objet user
        $user = new Users($userData->pseudo, $userData->userDate, $userId);

        //on lui attribue le nom du nouvel avatar
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

            //si déplacement du nouvel avatar fail :
        } else {
            Toolbox::dataJson(false, "Problème rencontré lors de l'enregistrement de l'image");
            exit;
        }

        // Si aucun "exit" précédent

        //on créer les données que JS va récupérer
        $data = [
            'userId' => $userId,
            'avatar' => $_SESSION['profil']['avatar']
        ];
        //et on envoie la réponse en json
        Toolbox::dataJson(true, "Avatar ok", $data);
        exit;
        // } else {
        //     Toolbox::ajouterMessageAlerte("Session expirée, veuillez vous reconnecter", 'rouge');
        //     unset($_SESSION['profil']);
        //     unset($_SESSION['tokenCSRF']);
        //     // session_destroy();
        //     Toolbox::dataJson(false, "expired token");
        //     exit;
        // }
    }

    public function editEmail($nouveauEmail)
    {
        // $token = $_SESSION['tokenCSRF'];
        // $jwt = new JWTService();
        // if ($jwt->isValid($token) && !$jwt->isExpired($token) && $jwt->check($token, SECRET)) {
        $user = $this->usersModel->getUserinfo($_SESSION['profil']['pseudo']);
        $userId = $user->userID;
        $pseudo = $user->pseudo;

        //ici on peut récupérer son email "avant modification". Cela pourrait nous permettre d'envoyer un email à cette adresse en indiquant que si il n'est pas à l'origine de cette modification, il peut contacter l'administrateur du site.
        $userAncienEmail = $user->email;

        $token = Securite::createTokenJWT($userId, $pseudo, $nouveauEmail);
        $cheminTemplate = '../Views/templateMail/templateEditEmail.html';
        $route = URL . "validationEditEmail/" . $token;
        $sujet = 'Validation adresse email sur Guitare Forum';
        if (Toolbox::sendMail($pseudo, $nouveauEmail, $route, $sujet, $cheminTemplate)) {
            $message = "Un email de validation a été envoyé sur cette adresse email. Ce mail sera valide pendant 3h";
            Toolbox::dataJson(true, $message);
            exit;
        } else {
            Toolbox::dataJson(false, 'Une erreur est survenue');
            exit;
        }
        // } else {
        //     Toolbox::ajouterMessageAlerte("Session expirée, veuillez vous reconnecter", 'rouge');
        //     unset($_SESSION['profil']);
        //     unset($_SESSION['tokenCSRF']);
        //     Toolbox::dataJson(false, "expired token");
        //     exit;
        // }
    }

    //c'est la méthode qui est appélée dans la route qui se trouve dans l'email reçu lors de la demande de changement d'email.
    public function validationEditEmail($tokenToVerify)
    {
        $jwt = new JWTService();
        if ($jwt->isValid($tokenToVerify) && !$jwt->isExpired($tokenToVerify) && $jwt->check($tokenToVerify, SECRET)) {
            $payload = $jwt->getPayload($tokenToVerify);
            /**
             * *je pourrais passer par la class User et faire en sorte que la méthode "editEmailUser()" utilise un getter pour récupérer l'id.
             * *Pour la vérification du token, j'ai proceder autrement, ici, je vais passer par la classe User afin d'exploiter toutes les possibilités.
             */
            $infosUser = $this->usersModel->getUserBy('userID', $payload['userID']);
            $pseudo = $infosUser->pseudo;
            $created_at = $infosUser->userDate;
            $userId = $infosUser->userID;

            $user = new Users($pseudo, $created_at, $userId);
            $user->setEmail($payload['email']);
            if ($this->usersModel->editEmailUser($user)) {
                Toolbox::ajouterMessageAlerte("Adresse email modifiée avec succès", "vert");
            }
        } else {
            Toolbox::ajouterMessageAlerte("token NON valide", "rouge");
        }
    }

    public function changePassword($ancienPassword, $nouveauPassword, $confirmPassword)
    {

        // $token = $_SESSION['tokenCSRF'];
        // $jwt = new JWTService();
        // if ($jwt->isValid($token) && !$jwt->isExpired($token) && $jwt->check($token, SECRET)) {

        $regexpPassword = "/^(?=.*[A-Z])(?=.*[!@#$%^&*()_+\-=\[\]{};':\"\\|,.<>\/?].*[!@#$%^&*()_+\-=\[\]{};':\"\\|,.<>\/?])[a-zA-Z0-9!@#$%^&*()_+\-=\[\]{};':\"\\|,.<>\/?]{8,50}$/";

        $pseudo = $_SESSION['profil']['pseudo'];
        if ($this->usersModel->verifLogin($pseudo, $ancienPassword)) {

            if (!preg_match($regexpPassword, $nouveauPassword)) {
                Toolbox::ajouterMessageAlerte("Le mot de passe doit contenir entre 8 et 50 caractères dont au moins 2 caractères spéciaux et une majuscule", 'rouge');
                header("Location: " . URL . "profil");
                exit;
            }
            if ($nouveauPassword !== $confirmPassword) {
                Toolbox::ajouterMessageAlerte("Les mots de passe ne correspondent pas", 'rouge');
                header("Location: " . URL . "profil");
                exit;
            }
            $userData = $this->usersModel->getUserinfo($pseudo);
            $user = new Users($userData->pseudo, $userData->userDate, $userData->userID);
            $user->setPassword(password_hash($nouveauPassword, PASSWORD_DEFAULT));
            $resultat = $this->usersModel->updatePassword($user);
            if ($resultat) {
                Toolbox::dataJson(true, "Mot de passe modifié avec succès");
                exit;
            } else {
                Toolbox::dataJson(false, "Erreur recontrée");
                exit;
            }
        } else {
            Toolbox::dataJson(false, "Mot de actuel incorrect");
            exit;
        }
        // } else {
        //     Toolbox::ajouterMessageAlerte("Session expirée, veuillez vous reconnecter", 'rouge');
        //     unset($_SESSION['profil']);
        //     unset($_SESSION['tokenCSRF']);
        //     Toolbox::dataJson(false, "expired token");
        //     exit;
        // }
    }

    public function editAbout($guitare, $emploi, $ville)
    {
        // $token = $_SESSION['tokenCSRF'];
        // $jwt = new JWTService();
        // if ($jwt->isValid($token) && !$jwt->isExpired($token) && $jwt->check($token, SECRET)) {

        $userData = $this->usersModel->getUserinfo($_SESSION['profil']['pseudo']);
        $user = new Users($userData->pseudo, $userData->userDate, $userData->userID);
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
        // } else {
        //     Toolbox::ajouterMessageAlerte("Session expirée, veuillez vous reconnecter", 'rouge');
        //     unset($_SESSION['profil']);
        //     unset($_SESSION['tokenCSRF']);
        //     Toolbox::dataJson(false, "expired token");
        //     exit;
        // }
    }



    public function supprimerCompte()
    {


        $data_page = [
            "pageDescription" => "Page de suppression du compte",
            "pageTitle" => "Supprimer mon compte",
            "view" => "../Views/Utilisateur/viewSupprimerCompte.php",
            "template" => "../views/common/template.php",
            "css" => "public/style/supprimerCompteStyle.css",
            // "script" => "public/js/profil.js",

        ];
        $this->render($data_page);
    }
    public function validerSupprimerCompte()
    {
        $userId = $_SESSION['profil']['userID'];
        $user = $this->usersModel->getUserBy('userID', $userId);
        $imageAvatar = 'images/profils/' . $userId . '/' . $user->avatar;
        unlink($imageAvatar);
        rmdir('images/profils/' . $userId);
        if ($this->usersModel->supprimerUser($userId)) {
            Toolbox::ajouterMessageAlerte("Votre compte a été supprimé  avec succès", 'vert');
            unset($_SESSION['profil']);
        } else {
            Toolbox::ajouterMessageAlerte("Une erreur est survenue lors de la suppression", 'rouge');
            header("Location: " . URL . "accueil");
        }
    }
    public function uploadImage($datasImage, $topicID)
    {
        //je crée un tableau avec les types mime autorisés
        $typeMime = [
            "jpg" => "image/jpg",
            "jpeg" => "image/jpeg",
            "gif" => "image/gif",
            "png" => "image/png"
        ];


        //on verifie le type mime du fichier
        if (!in_array($datasImage['type'], $typeMime)) {
            Toolbox::dataJson(false, "Le fichier n'est pas une image valide. Extensions autorisées : png, gif ou jpeg(jpg)", $topicID);
            exit;
        }

        //on vérifie sa taille
        if ($datasImage['size'] > 307200) {
            Toolbox::dataJson(false, "Le poids de l'image doit être inférieure à 300ko");
            exit;
        }

        $filePath = 'images/topics/' . $topicID;
        //Si ce dossier n'existe pas, il faut le créer
        if (!file_exists($filePath)) {
            mkdir($filePath, 0777, true);
        }

        // $datasImage['type'] = image/xxx
        // $array = explode("/", $datasImage['type'])[1];
        $extension = explode("/", $datasImage['type'])[1];
        $imageRename = uniqid($topicID, true) . '.' . $extension;

        //on déplace l'image des "temporaire" dans le dossier du user
        $moveImage = move_uploaded_file($datasImage['tmp_name'], $filePath . '/' . $imageRename);
        if ($moveImage) {
            $imageURL = 'public/' . $filePath . '/' . $imageRename;
            $dataImage = [
                'url' => $imageURL
            ];
            Toolbox::dataJson(true, "Image enregistrée avec succès", $dataImage);
            exit;
        } else {
            Toolbox::dataJson(false, "Problème rencontré lors de l'enregistrement");
            exit;
        }
    }

    public function validationResponseSujet($escapedResponse, $topicID)
    {
        // !véfifier le token csrf avant

        $userID = $_SESSION['profil']['userID'];
        if (is_numeric($topicID)) {

            //j'initialise les setters :
            $this->message->setMessageText($escapedResponse);
            $this->message->setUserID($userID);
            $this->message->setTopicID($topicID);

            //je fais appel à mon messageModel pour enregistrer les données en lui injectant "$this->message" qui correspond à l'instance de "new Message()" :
            if ($this->messageModel->createMessage($this->message)) {
                //!ensuite : 
                $decodedResponse = html_entity_decode($escapedResponse);
                // $test = html_entity_decode("&lt;p&gt;");
                $config = \HTMLPurifier_Config::createDefault();
                $purifier = new \HTMLPurifier($config);
                $clean_html = $purifier->purify($decodedResponse);

                $data = [
                    'reponseTopic' => $clean_html,
                    'topicID' => $topicID,
                    'dataUser' => $_SESSION['profil']
                ];
                //et on envoie la réponse en json
                Toolbox::dataJson(true, "données reçues, ok !", $data);
                exit;
            } else {
                Toolbox::dataJson(false, "Une erreur s'est produite");
                exit;
            }
        } else {
            Toolbox::dataJson(false, "Une erreur s'est produite");
            exit;
        }
    }


    private function messageAlert($message, $page)
    {
        Toolbox::ajouterMessageAlerte($message, 'rouge');
        header("Location: " . URL . $page);
        exit;
    }

    public function createTopicView()
    {
        $data_page = [
            "pageDescription" => "Page de réinitialisation de mot de passe",
            "pageTitle" => "Réinitialiser mot de passe",
            "view" => "../Views/Utilisateur/viewResetPassword.php",
            "template" => "../Views/common/template.php",
            "css" => "public/style/resetPasswordStyle.css",
            "script" => "public/js/validFormResetPassword.js",
            // "jwt" => $jwt
        ];
        $this->render($data_page);
    }


    // public function validationLogin($pseudo, $password, $previousURL)
    // {
    //     /**
    //      * Les données que vous stockez dans la session sont relativement petites, statiques et fréquemment utilisées, ce qui justifie de les stocker dans la session. Cependant, les données sont aussi modifiables par l’utilisateur, ce qui implique de les mettre à jour dans la session et dans la base de données. De plus, les données sont liées à d’autres informations, comme les messages du forum, ce qui peut rendre la requête plus complexe ou coûteuse.
    //      * 
    //      * Dans ce cas, vous pouvez utiliser une combinaison de la session, de la base de données et du cache pour optimiser les performances de votre application. Par exemple, vous pouvez stocker dans la session les données qui ne changent pas souvent ou qui sont essentielles pour l’authentification, comme l’id ou le pseudo de l’utilisateur. Vous pouvez stocker dans la base de données les données qui changent souvent ou qui sont sensibles, comme l’email ou le mot de passe de l’utilisateur. Vous pouvez utiliser un cache pour stocker temporairement les données qui sont complexes ou coûteuses à récupérer depuis la base de données, comme les messages du forum liés à l’utilisateur.


    //      */

    //     if ($this->usersModel->verifLogin($pseudo, $password)) {

    //         $user = $this->usersModel->getUserinfo($pseudo);

    //         if ($user->is_valid) {

    //             //du coup je décide d'enregistrer un minium d'info pour ne pas surcharger le serveur, avec des sessions qui pourrait contenir trop d'info. je vais privilgier les requête sql pour afficher des infos détaillées, comme les données personelles, et les messages associé à l'utilisateur.
    //             $filepathAvatar = $user->userID . '/' . $user->avatar;
    //             $_SESSION['profil'] = [
    //                 'userID' => $user->userID,
    //                 'pseudo' => $user->pseudo,
    //                 'filepathAvatar' => $filepathAvatar,
    //                 'messagesCount' => $user->messagesCount,
    //                 'userGuitare' => $user->guitare,
    //             ];

    //             // $tokenCSRF = $this->createToken($user->userID);
    //             // $_SESSION['tokenCSRF'] = $tokenCSRF;



    //             Toolbox::dataJson(
    //                 true,
    //                 "Connexion OK",
    //                 $data = [
    //                     'pseudo' => $user->pseudo,
    //                     'filepathAvatar' => $filepathAvatar,
    //                     'id' => $user->userID,
    //                     'previousURL' => $previousURL
    //                 ]
    //             );
    //         } else {
    //             $userId =  $user->userID;
    //             $message = "Compte non validé ! Cliquez sur <a href='" . URL . "returnToken/" . $userId . "'>ce lien</a> pour renvoyer un mail de validation.";
    //             Toolbox::dataJson(false, $message);
    //         }
    //     } else {
    //         $data = [
    //             'pseudo' => $pseudo,
    //             'password' => $password
    //         ];
    //         Toolbox::dataJson(false, "Identifiants incorrects", $data);
    //     }
    // }
}




/**
 * à la base en base d donnée : ../images/profils/avatarDefault.jpg
 * default : avatarDefault.jpg
 * perso userId/photorenommé
 */