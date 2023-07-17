<?php

namespace Controllers;

use Models\CategorysModel;
use Controllers\Services\Toolbox;

class HomeController extends MainController
{
    private $categorysModel;

    public function __construct()
    {
        $this->categorysModel = new CategorysModel();
    }

    public function home()
    {
        $allCategorys = $this->categorysModel->getCategorysList();

        // $categories = [];
        // foreach ($allCategorys as $row) {
        //     $parentCategoryName = $row['categoryName'];
        //     if (!isset($categories[$parentCategoryName])) {
        //         $categories[$parentCategoryName] = [];
        //     }
        //     if (isset($row['subCategoryName'])) {
        //         $subCategory = [
        //             'name' => $row['subCategoryName'],
        //             'totalTopics' => $row['totalTopics'],
        //             'totalMessages' => $row['totalMessages'],
        //             'latestTopicTitle' => $row['latestTopicTitle'],
        //             'latestMessageDate' => $row['latestMessageDate'],
        //             'latestMessageUser' => $row['latestMessageUser']
        //         ];
        //         $categories[$parentCategoryName][] = $subCategory;
        //     }
        // }
        $resultatsRegroupes = array_reduce($allCategorys, function ($accumulateur, $ligne) {
            // On vérifie si la catégorie parente existe déjà dans le tableau des résultats
            if (!isset($accumulateur[$ligne->parentCategoryName])) {
                // Si ce n'est pas le cas, on crée une nouvelle entrée pour cette catégorie
                $accumulateur[$ligne->parentCategoryName] = [];
            }

            // On crée un tableau pour stocker les informations de la sous-catégorie
            $subCategory = [
                'name' => $ligne->subCategoryName,
                'description' => $ligne->subCategoryDesc,
                'id' => $ligne->subCategoryID,
                'url' => 'sousCat/' . $ligne->subCategorySlug . '.' . $ligne->subCategoryID,
                'totalTopics' => $ligne->totalTopics,
                'totalMessages' => $ligne->totalMessages,
                'lastTopicTitle' => $ligne->lastTopicTitle,
                'lastMessageDate' => Toolbox::convertDate($ligne->lastMessageDate, 'd MMMM Y'),
                'lastMessageUser' => $ligne->lastMessageUser
            ];
            // On ajoute la sous-catégorie au tableau des sous-catégories pour cette catégorie parente
            $accumulateur[$ligne->parentCategoryName][] = $subCategory;
            // On retourne le tableau des résultats mis à jour
            return $accumulateur;
        }, []);

        //même résultat que le array_reduce
        // $groupedResults = [];
        // foreach ($allCategorys as $row) {
        //     $category = $row->parentCategoryName;
        //     $subcategory = $row->subCategoryName;
        //     // if ($subcategory !== null) {
        //     if (!isset($groupedResults[$category])) {
        //         $groupedResults[$category] = [];
        //     }
        //     // $groupedResults[$category][] = $subcategory;
        //     $groupedResults[$category][$subcategory][] = $row->totalTopics;
        //     $groupedResults[$category][$subcategory][] = $row->totalMessages;
        //     // }
        // }

        //* Sinon, autre manière plus complexe de récupérer toute les catégories et de les classer en fonction de leur parent, quelque soit le nombre de sous categories
        // ! méthodes utilisées : 
        //! voir méthode getCategorysListWithRECURSIVE() du model
        // ! voir méthode static buildCategoryHierarchy() de la Toolbox

        // on récupère la liste de catégorie et sous catégorie avec un "level"
        //$allCategorys = $this->CategorysModel->getCategorysListWithRECURSIVE();
        //on utilise la méthode pour construire un tableau hérarchique
        // $hierarchy = Toolbox::buildCategoryHierarchy($allCategorys);


        $data_page = [
            "pageDescription" => "Description de la page d'accueil",
            "pageTitle" => "Accueil Guitare forum",
            "view" => "../Views/viewHome.php",
            "css" => "public/style/homeStyle.css",
            "template" => "../Views/common/template.php",
            'allCategorys' => $allCategorys,
            "categorysList" => $resultatsRegroupes
        ];
        $this->render($data_page);
    }
}
