<?php

namespace Core;

use Exception;
use Controllers\HomeController;
// use Controllers\LoginController;
// use Controllers\TopicController;
// use Controllers\LogoutController;
// use Controllers\AccountController;
// use Controllers\SousCatController;
// use Controllers\RegisterController;
// use Controllers\ForgotPassController;
// use Controllers\TopicReplyController;

class Router
{
    public function routes()
    {
        /**
         * !! Système de routage basé sur une convention de nommage :
         * Exemple : $_GET['page']) === "public/register", on récupère "register" avec un explode.
         * register sera donc à la fois:
         * - le nom de la route
         * - le nom du controller (registerController)
         * - et le nom de la méthode à appeler
         * 
         * Si existence de paramètres suplémentaires, exemple public/account/"profil", "profil" sera aussi récupére grâce au "explode" en indice [2] et passé en paramètre après l'instanciation de la bonne classe.
         */



        try {
            if (empty($_GET['page'])) {
                $page = "home";
            } else {
                /**
                 * !  Le fichier .htaccess utilise des règles de réécriture pour rediriger les requêtes vers le fichier index.php du dossier public, en passant l’URL demandée en tant que paramètre "page" dans la chaîne de requête
                 *  $_GET['page'] = "public/nom_De_La_Route
                 */
                $paramGet = explode("/", filter_var($_GET['page'], FILTER_SANITIZE_URL));
                $page = $paramGet[1];
            }
            //on construit le chemin du bon controller
            $controllerName = 'Controllers\\' . ucfirst($page) . 'Controller';
            if (!class_exists($controllerName)) {
                throw new Exception("La page n'existe pas");
            }
            $controller = new $controllerName();
            // if (method_exists($controllerName, $page))
            if (isset($paramGet[2]) && !empty($paramGet[2])) {
                $controller->$page($paramGet[2]);
            } else {
                $controller->$page();
            }
        } catch (Exception $e) {
            $homeController = new HomeController();
            $homeController->pageErreur($e->getMessage());
        }
    }
}
