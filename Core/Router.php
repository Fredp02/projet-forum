<?php

namespace Core;

use Exception;
use Controllers\HomeController;

class Router
{
    public function routes()
    {
        /**
         * !! Système de routage basé sur une convention de nommage :
         * Exemple : $_GET['page']) === "/register/viewRegister", on récupère "register" avec un explode.
         * register sera donc à la fois:
         * - le nom de la route
         * - le nom du controller (registerController)
         * - et le nom de la méthode à appeler
         * 
         * par contre, il est possible que $_GET['page']) soit sous cette forme forgotPass/resetPassView/$JWT.
         */

        try {
            if (empty($_GET['page'])) {
                $page = "home";
            } else {
                /**
                 * !  Le fichier .htaccess passe l’URL demandée en tant que paramètre "page" dans la chaîne de requête
                 *  $_GET['page'] = "/nom_Du_controller/et-ou-nom_de_la_methode/paramètre_suplementaires
                 */
                $paramGet = explode("/", filter_var($_GET['page'], FILTER_SANITIZE_URL));

                $page = $paramGet[0];
            }
            //on construit le chemin du bon controller
            $controllerName = 'Controllers\\' . ucfirst($page) . 'Controller';
            if (!class_exists($controllerName)) {
                throw new Exception("La page n'existe pas");
            }
            $controller = new $controllerName();
            // if (method_exists($controllerName, $page))
            if (isset($paramGet[1]) && !empty($paramGet[1])) {
                $controller->$page($paramGet[1]);
            } else {
                $controller->$page();
            }
        } catch (Exception $e) {
            $homeController = new HomeController();
            $homeController->pageErreur($e->getMessage());
        }
    }
}
