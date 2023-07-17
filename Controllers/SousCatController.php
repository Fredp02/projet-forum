<?php

namespace Controllers;

use Controllers\MainController;
use Models\TopicsModel;
use Models\CategorysModel;

class SousCatController extends MainController
{

    private $categorysModel;
    private $topicsModel;
    // private $MessagesModel;

    public function __construct()
    {
        $this->categorysModel = new CategorysModel();
        $this->topicsModel = new TopicsModel();
        // $this->MessagesModel = new MessagesModel();
    }



    public function sousCat($sousCategoryURL)
    {
        //en cliquant sur le nom de la sous catégorie depuis la page d'acceuil, il faudra afficher la liste des topics en fonction de l'ID de la sous catégorie

        //je récupère la seconde partie du slug séparée par un "." qui correspond à l'id de la sous catégorie

        $array = explode(".", filter_var($sousCategoryURL, FILTER_SANITIZE_URL));
        if (isset($array[1])) {
            $souscategoryID = $array[1];
            $listTopics = $this->topicsModel->getListTopicsByCat($souscategoryID);
            //si $souscategoryID est un id existant et que la requête à renvoyer un résultat
            if ($listTopics) {
                $infosCategory = $this->categorysModel->getInfoCategory($souscategoryID);

                $data_page = [
                    "pageDescription" => "Catégorie " . $infosCategory->categoryName . " du site Guitare-forum",
                    "pageTitle" => $infosCategory->categoryName . " | Guitare-forum",
                    "view" => "../Views/viewSousCat.php",
                    "css" => "public/style/sousCat.css",
                    "template" => "../Views/common/template.php",
                    "categoryName" => $infosCategory->categoryName,
                    "categorySlug" => $infosCategory->categorySlug,
                    "categoryID" => $infosCategory->categoryID,
                    "listTopics" => $listTopics
                ];
                $this->render($data_page);
            } else {
                header("Location: " . URL);
            }
        } else {
            header("Location: " . URL);
        }

        // $souscategoryID = explode(".", filter_var($sousCategoryURL, FILTER_SANITIZE_URL))[1];

    }
}
