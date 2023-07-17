<?php

namespace Controllers\Visiteur;

use Controllers\MainController;
use Controllers\Services\Toolbox;
use Models\MessagesModel;
use Models\TopicsModel;
use Models\CategorysModel;

class VisiteurController extends MainController
{

    private $categorysModel;
    private $topicsModel;
    private $MessagesModel;
    // private $user;

    public function __construct()
    {
        $this->categorysModel = new CategorysModel();
        $this->topicsModel = new TopicsModel();
        $this->MessagesModel = new MessagesModel();
    }



    // public function connexionView()
    // {
    //     $data_page = [
    //         "pageDescription" => "Page de connexion au site Guitare Forum",
    //         "pageTitle" => "Connexion | Guitare Forum",
    //         "view" => "../Views/Visiteur/viewConnexion.php",
    //         "template" => "../Views/common/template.php",
    //         "css" => "public/style/connexionStyle.css",
    //         "script" => "public/js/validFormPageLogin.js",
    //         "tokenCSRF" => $_SESSION['tokenCSRF'],
    //         "previousURL" => $_SERVER['HTTP_REFERER'],
    //     ];
    //     $this->render($data_page);
    // }

    // public function inscription()
    // {
    //     $data_page = [
    //         "pageDescription" => "Page de création de compte",
    //         "pageTitle" => "Inscription",
    //         "view" => "../Views/Visiteur/viewInscription.php",
    //         "template" => "../Views/common/template.php",
    //         "css" => "public/style/inscriptionStyle.css",
    //         "script" => "public/js/validFormInscription.js",
    //         "tokenCSRF" => $_SESSION['tokenCSRF']
    //     ];
    //     $this->render($data_page);
    // }


}
