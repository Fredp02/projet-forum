<?php

namespace Core;

use Exception;
use Controllers\HomeController;

class Router
{
    public function routes()
    {

        // ! index.php?controller=register&action=registerView 
        // $_GET = [
        //     'controller' => 'register',
        //     'action' => 'registerView'
        // ];
        // ! index.php?controller=forgotPass&action=sendEmail= 
        // $_GET = [
        //     'controller' => 'register',
        //     'action' => 'registerView'
        // ];
        try {

            //on intialise le controller
            if (isset($_GET['controller'])) {

                $controller = ucfirst(array_shift($_GET));
            } else {
                $controller = 'Home';
            }

            $controllerName = 'Controllers\\' . $controller . 'Controller';
            // dd('je suis ici ' . $controllerName);
            //on intialise l'action
            if (isset($_GET['action'])) {

                $action = array_shift($_GET);
            } else {
                $action = 'index';
                // dd('je suis ici ');
            }

            $controller = new $controllerName();

            // if (method_exists($controllerName, $page))
            if (method_exists($controller, $action)) {
                (isset($_GET)) ? call_user_func_array([$controller, $action], $_GET) : $controller->$action();
            } else {
                http_response_code(404);
                echo "la page recherchée n'existe pas";
            }
        } catch (Exception $e) {
            $homeController = new HomeController();
            $homeController->pageErreur($e->getMessage());
        }
    }
}
