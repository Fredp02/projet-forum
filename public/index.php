<?php
session_start();

define("URL", str_replace("public/index.php", "", (isset($_SERVER['HTTPS']) ? "https" : "http") .
  "://" . $_SERVER['HTTP_HOST'] . $_SERVER["PHP_SELF"]));


require '../vendor/autoload.php';
// require_once '../vendor/ezyang/htmlpurifier/library/HTMLPurifier.auto.php';

//parce que j'utilise ces classes dans ce fichier.
//quand on fait appel à une classe, même avec un autoload, les "use" doivent être présent.

use Controllers\Services\Securite;
use Controllers\Services\Toolbox;
use Controllers\User\UserController;
use Controllers\Visiteur\VisiteurController;
use Models\Visiteur\Categorys\CategorysModel;

$visiteurController = new VisiteurController();
$userController = new UserController();


try {
  if (empty($_GET['page'])) {
    // dump('le get est vide');
    $page = "accueil";
  } else {

    /**
     * Sinon c'est que l'url du site est de ce type : http://monsite/materiel/guitare-classique
     * le $_GET['page'] faudra dans cet exemple "compte/profil".
     * On fait donc un "explode" pour avoir ce tableau : 
     * ['public''materiel', 'guitare-classique"]
     * [   0    ,     1    ,       2   ]
     * ['public''accueil']
     * [   0    ,    1  ]
     */
    $url = explode("/", filter_var($_GET['page'], FILTER_SANITIZE_URL));
    // dump($_GET['page']);
    $page = $url[1];
    // dump($page);
  }
  switch ($page) {

    case "accueil":
      /**
       * dans le cas ou le $url[2] vaut xxxxxx.4 alors ce sera une liste de topics à afficher en fonction de l'ID de la sous-catégorie. SINON ce sera la page d'accueil à afficher
       * URL[2] se présente sous cette forme : "guitare-classique.4"
       * "4" correspond à l'id de la sous catégorie.
       */

      if (isset($url[2]) && !empty($url[2])) {
        $sousCategoryURL = htmlspecialchars($url[2]);
        $visiteurController->displayTopicsList($sousCategoryURL);
      } else {
        //sinon par defaut c'est la page d'accueil avec la liste des catégories et sous catégories associées.
        $visiteurController->accueil();
      }
      break;


      // case "connexion":
      //   if (!Securite::isConnected()) {
      //     $visiteurController->connexionView();
      //   } else {
      //     header("Location: " . URL);
      //     exit;
      //   }

      //   break;
    case "sujet":
      if (isset($url[2]) && !empty($url[2])) {
        $topicUrl = htmlspecialchars($url[2]);
        $visiteurController->displayTopic($topicUrl);
      } else {
        throw new Exception("La page n'existe pas");
      }
      break;
    case  "connexion":
      if (!Securite::isConnected()) {
        $visiteurController->connexionView();
      } else {
        header("Location: " . URL);
        exit;
      }
      break;
    case "validationLogin":
      if (!empty($_POST['pseudo']) && !empty($_POST['password'])) {
        $pseudo = htmlspecialchars($_POST['pseudo']);
        $password = htmlspecialchars($_POST['password']);
        // $pseudo = html_entity_decode($_POST['pseudo']);
        // $password = html_entity_decode($_POST['password']);

        //s'il y a un second paramètre dans la route (validationLogin/pageLogin) alors la soumission du formulaire de connexion vient de la page login, et pas celle de la aside du template
        // $pageLogin = (isset($url[2]) && !empty($url[2]) && $url[2] === 'pageLogin') ? true : false;
        $userController->validationLogin($pseudo, $password);
      } else {
        throw new Exception("La page n'existe pas");
      }
      break;

      //! les 4 "case" suivant gèrent la réinitialisation du password en cas d'oublie : 
    case "forgot":
      //On vérifie s'il est connecté pour autoriser l'affichage de la page "forgot"
      if (!Securite::isConnected()) {
        $userController->forgotView();
      } else {
        header("Location: " . URL);
        exit;
      }
      break;

    case "sendEmailPassForgot":
      //une fois le formulaire soumis, on gère l'envoi ou non du mail de réinitialisation de mot de passe
      if (!empty($_POST['passwordForgot'])) {
        $email = htmlspecialchars($_POST['passwordForgot']);
        $userController->sendEmailPassForgot($email);
      } else {
        throw new Exception("La page n'existe pas");
      }

      break;
    case "reinitialiserPassword":
      //page lorsqu'on clique sur le lien de l'email reçu, avec le token en GET. C'est un formulaire.

      //On considère que le user n'est pas censé être connecté pour accéder au formulaire de réinitialisation
      if (!Securite::isConnected()) {
        //si l'url est sous ce format : monsite/reinitialiserPassword/xxxx
        //"xxxx" correspond au token. 
        //la vérification du token se fera plus tard dans le case "validationResetPassword".
        if ((isset($url[2]) && !empty($url[2]))) {
          $jwt = $url[2];
          $userController->reinitialiserPassword($jwt);
        } else {
          throw new Exception("La page n'existe pas");
        }
      } else {
        header("Location: " . URL);
        exit;
      }

      break;
    case "validationResetPassword":

      //une fois le formulaire soumis, on gère l'envoi ou non le reset du password en fonction de la validité du token
      if (isset($url[2]) && !empty($url[2])) {
        $tokenToVerify = $url[2];
        if (!empty($_POST['nouveauPassword']) && !empty($_POST['confirmPassword'])) {
          $nouveauPassword = htmlspecialchars($_POST['nouveauPassword']);
          $confirmPassword = htmlspecialchars($_POST['confirmPassword']);
          $userController->validationResetPassword($tokenToVerify, $nouveauPassword, $confirmPassword);
        }
      } else {
        throw new Exception("La page n'existe pas");
      }
      break;

    case "deconnexion":
      $userController->logout();
      break;
    case "inscription":
      if (!Securite::isConnected()) {
        $visiteurController->inscription();
      } else {
        header("Location: " . URL);
        exit;
      }

      break;
    case "validationInscription":
      if (!empty($_POST['pseudo']) && !empty($_POST['emailInscription']) && !empty($_POST['password']) && !empty($_POST['confirmPassword'])) {

        $pseudo = htmlspecialchars($_POST['pseudo']);
        $emailInscription = htmlspecialchars($_POST['emailInscription']);
        $password = htmlspecialchars($_POST['password']);
        $confirmPassword =  htmlspecialchars($_POST['confirmPassword']);
        // $pseudo = html_entity_decode($_POST['pseudo']);
        // $emailInscription = html_entity_decode($_POST['emailInscription']);
        // $password = html_entity_decode($_POST['password']);
        // $confirmPassword =  html_entity_decode($_POST['confirmPassword']);

        $userController->validationInscription(trim($pseudo), trim($emailInscription), trim($password), trim($confirmPassword));
      } else {
        throw new Exception("La page n'existe pas");
      }
      break;
    case "verifToken":

      if (isset($url[2]) && !empty($url[2])) {
        $tokenToVerify = $url[2];
        $userController->activatingAccount($tokenToVerify);
        header("Location: " . URL);
        exit;
      } else {
        throw new Exception("La page n'existe pas");
      }
      break;
    case "returnToken":
      if (isset($url[2]) && !empty($url[2])) {
        $userId = $url[2];
        $userController->returnToken($userId);
      } else {
        throw new Exception("La page n'existe pas");
      }
      break;
    case "validationEditEmail":
      if (isset($url[2]) && !empty($url[2])) {
        $tokenToVerify = $url[2];
        $userController->validationEditEmail($tokenToVerify);
        header("Location: " . URL);
        exit;
      } else {
        throw new Exception("La page n'existe pas");
      }
      break;

    case 'createTopic':
      if (Securite::isConnected()) {
        $userController->createTopic();
      } else {
        $visiteurController->connexionView();
      }
      break;
    case "uploadImage":
      if (isset($_FILES['image']) && $_FILES['image']['error'] == 0 && !empty($_POST['topicID'])) {
        $userController->uploadImage($_FILES['image'], $_POST['topicID']);
      } else {
        echo 'erreur';
        exit;
      }
      break;

    case "validationResponseSujet":
      if (!empty($_POST['inputResponse']) && !empty($_POST['topicID'])) {
        // $inputResponse = htmlspecialchars($_POST['inputResponse']);
        $escapedResponse = htmlspecialchars($_POST['inputResponse']);
        $userController->validationResponseSujet($escapedResponse, $_POST['topicID']);
      } else {
        throw new Exception("La page n'existe pas");
      }
      break;
    case "compte":
      if (!Securite::isConnected()) {
        $message = "Pour accéder à votre profil, veuillez vous connecter.";
        Toolbox::ajouterMessageAlerte($message, 'rouge');
        header("Location: " . URL);
        exit;
      } else {
        switch ($url[2]) {
          case 'profil':
            $userController->profil();
            break;
          case 'datasFormProfil':
            //données envoyées à la page profil pour alimenter les formulaires.
            $userController->datasFormProfil();
            break;
          case 'avatar':

            if (isset($_POST['tokenCSRF']) && $_POST['tokenCSRF'] === $_SESSION['tokenCSRF']) {
              if (isset($_FILES['avatarPhoto']) && $_FILES['avatarPhoto']['error'] == 0) {
                $userController->editAvatar($_FILES['avatarPhoto']);
              } else {
                $message = "Une erreur est survenue...";
                Toolbox::ajouterMessageAlerte($message, 'rouge');
                header("Location: " . URL . "compte/profil");
                exit;
              }
            } else {
              Toolbox::ajouterMessageAlerte("Une erreur est survenue, veuillez vous reconnecter", 'rouge');
              unset($_SESSION['profil']);
              unset($_SESSION['tokenCSRF']);
              Toolbox::dataJson(false, "expired token");
              exit;
            }
            break;

          case 'editEmail':
            if (isset($_POST['tokenCSRF']) && $_POST['tokenCSRF'] === $_SESSION['tokenCSRF']) {
              // Le jeton anti-CSRF est valide, traiter les données du formulaire
              if (!empty($_POST['email'])) {
                $userController->editEmail(htmlspecialchars($_POST['email']));
              }
            } else {
              Toolbox::ajouterMessageAlerte("Une erreur est survenue, veuillez vous reconnecter", 'rouge');
              unset($_SESSION['profil']);
              unset($_SESSION['tokenCSRF']);
              Toolbox::dataJson(false, "expired token");
              exit;
            }
            break;

          case 'password':
            if (isset($_POST['tokenCSRF']) && $_POST['tokenCSRF'] === $_SESSION['tokenCSRF']) {
              if (!empty($_POST['ancienPassword']) && !empty($_POST['nouveauPassword']) && !empty($_POST['confirmPassword'])) {
                $ancienPassword = htmlspecialchars($_POST['ancienPassword']);
                $nouveauPassword = htmlspecialchars($_POST['nouveauPassword']);
                $confirmPassword = htmlspecialchars($_POST['confirmPassword']);
                $userController->changePassword($ancienPassword, $nouveauPassword, $confirmPassword);
              }
            } else {
              Toolbox::ajouterMessageAlerte("Une erreur est survenue, veuillez vous reconnecter", 'rouge');
              unset($_SESSION['profil']);
              unset($_SESSION['tokenCSRF']);
              Toolbox::dataJson(false, "expired token");
              exit;
            }

            break;

          case 'about':
            if (isset($_POST['tokenCSRF']) && $_POST['tokenCSRF'] === $_SESSION['tokenCSRF']) {
              if (!empty($_POST['guitare']) && !empty($_POST['emploi']) && !empty($_POST['ville'])) {
                $guitare = htmlspecialchars($_POST['guitare']);
                $emploi = htmlspecialchars($_POST['emploi']);
                $ville = htmlspecialchars($_POST['ville']);
                $userController->editAbout(trim($guitare), trim($emploi), trim($ville));
              } else {
                throw new Exception("La page n'existe pas");
              }
            } else {
              Toolbox::ajouterMessageAlerte("Une erreur est survenue, veuillez vous reconnecter", 'rouge');
              unset($_SESSION['profil']);
              unset($_SESSION['tokenCSRF']);
              Toolbox::dataJson(false, "expired token");
              exit;
            }

            break;
          case 'supprimerCompte':
            $userController->supprimerCompte();
            break;
          case 'validerSupprimerCompte':
            $userController->validerSupprimerCompte();
            header("Location: " . URL);
            exit;

            break;

          default:
            throw new Exception("La page n'existe pas");
            break;
        }
      }


    default:
      throw new Exception("La page n'existe pas");
  }
} catch (Exception $e) {
  $visiteurController->pageErreur($e->getMessage());
}
