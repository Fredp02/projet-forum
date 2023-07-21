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

            $controllerName = 'Controllers\\' . ucfirst($controller) . 'Controller';

            //on intialise l'action
            if (isset($_GET['action'])) {
                $action = ucfirst(array_shift($_GET));
            } else {
                $action = '';
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
