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

            if (!empty($_GET) && !array_key_exists("controller", $_GET)) {
                throw new Exception('Cette page n\'existe pas');
            }


            //on intialise le controller

            $controller = isset($_GET['controller']) ? ucfirst(array_shift($_GET)) : 'Home';

            $controllerName = 'Controllers\\' . $controller . 'Controller';
            //on intialise l'action
            $action = isset($_GET['action']) ? array_shift($_GET) : 'index';
            if (!class_exists($controllerName)) {
                throw new Exception('Cette page n\'existe pas');
            }
            $controller = new $controllerName();
            if (!method_exists($controller, $action)) {
                throw new Exception('Cette page n\'existe pas');
            }
            (isset($_GET)) ? call_user_func_array([$controller, $action], $_GET) : $controller->$action();
        } catch (Exception $e) {
            $homeController = new HomeController();
            $homeController->pageErreur($e->getMessage());
        }
    }
}
