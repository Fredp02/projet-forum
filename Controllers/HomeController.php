<?php

namespace Controllers;

use Models\UsersModel;
use Models\CategorysModel;
use Controllers\Services\Toolbox;

class HomeController extends MainController
{
    private $categorysModel;
    private $usersModel;

    public function __construct()
    {
        $this->categorysModel = new CategorysModel();
        $this->usersModel = new UsersModel();
    }

    public function index()
    {

        $allCategorys = $this->categorysModel->getCategorysList();

        //Ce code parcourt les résultats d’une requête SQL qui sélectionne des informations sur les catégories parentes et les sous-catégories, ainsi que des statistiques sur les sujets et les messages dans ces catégories. Le code utilise une boucle foreach pour parcourir chaque ligne de résultat de la requête, représentée par la variable $row.

        //Pour chaque ligne de résultat, le code crée un tableau associatif $subCategory contenant des informations sur la sous-catégorie représentée par cette ligne. Les informations incluent le nom de la sous-catégorie, sa description, son ID, son URL, le nombre total de sujets et de messages dans cette sous-catégorie, ainsi que des informations sur le dernier message posté dans cette sous-catégorie.

        //Ensuite, le code ajoute le tableau $subCategory au tableau $categories, en utilisant le nom de la catégorie parente comme clé. Cela crée un tableau associatif à deux niveaux, où le premier niveau représente les catégories parentes et le deuxième niveau représente les sous-catégories dans chaque catégorie parente.

        //Si le nom de la catégorie parente n’existe pas encore comme clé dans le tableau $categories, cette ligne de code crée un nouvel index principal pour cette catégorie parente et ajoute le tableau associatif $subCategory comme première valeur sous cet index. Si le nom de la catégorie parente existe déjà comme clé dans le tableau $categories, cette ligne de code ajoute simplement le tableau associatif $subCategory à la “pile” de tableaux existants sous cet index principal.

        $groupedCategories = [];
        $groupedCategories = array_reduce($allCategorys, function ($acc, $row) {
            $subCategory = [
                'ParentID' => $row->parentCategoryID,
                'name' => $row->subCategoryName,
                'description' => $row->subCategoryDesc,
                'id' => $row->subCategoryID,
                'url' => '?controller=topics&action=list&catID=' . $row->subCategoryID,
                // 'url' => 'topicsByCat/' . $row->subCategorySlug . '.' . $row->subCategoryID,
                'totalTopics' => $row->totalTopics,
                'totalMessages' => $row->totalMessages,
                'lastTopicTitle' => $row->lastTopicTitle,
                'lastMessageDate' => Toolbox::convertDate($row->lastMessageDate, 'd MMMM Y'),
                'lastMessageUser' => $row->lastMessageUser
            ];
            $acc[$row->parentCategoryName][] = $subCategory;
            return $acc;
        }, []);

            // //même chose avec forecach : 
            // foreach ($allCategorys as $row) {
            //     $subCategory = [
            //         'name' => $row->subCategoryName,
            //         'description' => $row->subCategoryDesc,
            //         'id' => $row->subCategoryID,
            //         'url' => 'topicsByCat/' . $row->subCategorySlug . '.' . $row->subCategoryID,
            //         'totalTopics' => $row->totalTopics,
            //         'totalMessages' => $row->totalMessages,
            //         'lastTopicTitle' => $row->lastTopicTitle,
            //         'lastMessageDate' => Toolbox::convertDate($row->lastMessageDate, 'd MMMM Y'),
            //         'lastMessageUser' => $row->lastMessageUser
            //     ];
            //     $groupedCategories[$row->parentCategoryName][] = $subCategory;
            // }

            //* Sinon, autre manière plus complexe de récupérer toute les catégories et de les classer en fonction de leur parent, quelque soit le nombre de sous categories
            // ! méthodes utilisées : 
            //! voir méthode getCategorysListWithRECURSIVE() du model
            // ! voir méthode static buildCategoryHierarchy() de la Toolbox

            // on récupère la liste de catégorie et sous catégorie avec un "level"
            //$allCategorys = $this->CategorysModel->getCategorysListWithRECURSIVE();
            //on utilise la méthode pour construire un tableau hérarchique
            // $hierarchy = Toolbox::buildCategoryHierarchy($allCategorys);
        ;

        $data_page = [
            "pageDescription" => "Description de la page d'accueil",
            "pageTitle" => "Accueil Guitare forum",
            "view" => "../Views/viewHome.php",
            "css" => "./style/homeStyle.css",
            "template" => "../Views/common/template.php",
            // 'allCategorys' => $allCategorys,
            "groupedCategories" => $groupedCategories,
            'tokenCSRF' => $_SESSION['tokenCSRF']

        ];




        $this->render($data_page);
    }

    public function pageErreur($msg)
    {
        //on utilise la méthode dans mainController
        parent::pageErreur($msg);
    }
}
