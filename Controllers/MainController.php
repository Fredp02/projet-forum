<?php

namespace Controllers;


abstract class MainController
{
    //     private $mainManager;

    //     public function __construct()
    //     {
    //         $this->mainManager = new MainManager();
    //     }

    protected function render($data)
    {
        /**
         * extract($data) : 
         * génère les variable suivante : $page_description, $page_title, $view, et $template
         * elle viennent du tableau $data_page de la méthode accueil ou page erreur ou les autres
         */

        extract($data);
        ob_start(); //on lance la temporisation

        require_once($view); // on inclue la page demandé enregisté dans la variable "$view" qui change selon la méthode qui appel cette fonction genererPage

        $page_content = ob_get_clean(); //dans $page_content on enregistre ce qui se situe entre l'ob_start et le ob_get_clean

        require_once($template); //on inclue la page demandé en fonction  du contenu de la variable $template selon la méthode qui appel cette fonction genererPage


        //on sécurise la variable $page_content avant l'affichage
        $page_content = htmlspecialchars($page_content);
    }



    protected function pageErreur($msg)
    {
        $data_page = [
            "page_description" => "Page inexistante",
            "page_title" => "Page d'erreur",
            "msg" => $msg,
            "view" => "../Views/erreur.view.php",
            "template" => "../Views/common/template.php",
            "css" => "/style/errorStyle.css"
        ];
        $this->render($data_page);
    }
}
