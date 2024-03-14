<?php

namespace Controllers;

use Controllers\Services\Securite;
use Controllers\Traits\VerifPostTrait;
use Entities\Categorys;
use Models\TopicsModel;
use Models\UsersModel;
use Models\CategorysModel;
use Controllers\Services\Toolbox;

class DashboardController extends MainController
{
    use VerifPostTrait;
    private CategorysModel $categorysModel;
    private Categorys $categorys;
    private TopicsModel $topicsModel;
//    private $usersModel;

    public function __construct()
    {
        $this->categorysModel = new CategorysModel();
        $this->categorys = new Categorys();
        $this->topicsModel = new TopicsModel();
//        $this->usersModel = new UsersModel();
    }

    public function index()
    {

        if (Securite::isConnected() && $_SESSION['profil']['roleName'] === 'Administrateur'){
            $data_page = [
                "pageDescription" => "Dashboard",
                "pageTitle" => "Dashboard forum",
                "view" => "../Views/dashboard/dashboardHomeView.php",
                "css" => "./style/dashboard/db.css",
                "bootstrapCSS" => "https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css",
//                "bootstrapJS" => "https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js",
                "template" => "../Views/common/template.php",
                'tokenCSRF' => $_SESSION['tokenCSRF'],

            ];

            $this->render($data_page);
        }else{
            parent::pageErreur('Page inexistante');
        }

    }
    public function categoriesListShow(): void
    {
        if (Securite::isConnected() && $_SESSION['profil']['roleName'] === 'Administrateur'){


            $categories = $this->categorysModel->getAllWithParent();


            $data_page = [
                "pageDescription" => "Dashboard liste des catégories",
                "pageTitle" => "Dashboard forum | liste des catégories",
                "view" => "../Views/dashboard/dashboardCategoriesListeView.php",
                "css" => "./style/dashboard/db.css",
                "bootstrapCSS" => "https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css",

                "template" => "../Views/common/template.php",
                'tokenCSRF' => $_SESSION['tokenCSRF'],
                'categories' => $categories
            ];

            $this->render($data_page);
        }else{
            parent::pageErreur('Page inexistante');
        }
    }

    public function userListShow(): void
    {
        if (Securite::isConnected() && $_SESSION['profil']['roleName'] === 'Administrateur'){
            $data_page = [
                "pageDescription" => "Dashboard liste des Utilisateurs",
                "pageTitle" => "Dashboard forum | liste des Utilisateurs",
                "view" => "../Views/dashboard/dashboardUserListView.php",
                "css" => "./style/dashboard/db.css",
                "bootstrapCSS" => "https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css",
                "template" => "../Views/common/template.php",
                'tokenCSRF' => $_SESSION['tokenCSRF']
            ];

            $this->render($data_page);
        }else{
            parent::pageErreur('Page inexistante');
        }
    }

    public function statisticsShow(): void
    {
        if (Securite::isConnected() && $_SESSION['profil']['roleName'] === 'Administrateur'){
            $data_page = [
                "pageDescription" => "Dashboard statistiques",
                "pageTitle" => "Dashboard forum | Statistiques",
                "view" => "../Views/dashboard/dashboardStatisticstView.php",
                "css" => "./style/dashboard/db.css",
                "bootstrapCSS" => "https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css",
                "template" => "../Views/common/template.php",
                'tokenCSRF' => $_SESSION['tokenCSRF']
            ];

            $this->render($data_page);
        }else{
            parent::pageErreur('Page inexistante');
        }
    }
    public function categoryAdd()
    {
        if (Securite::isConnected() && $_SESSION['profil']['roleName'] === 'Administrateur'){

            if (($_SERVER['REQUEST_METHOD'] === 'POST') && Securite::verifCSRF()) {
                if (!empty($_POST['name']) && !empty($_POST['description'])){
                    if ((empty($_POST['isParent'])  &&  empty($_POST['parentCategorie'])) ||
                        (!empty($_POST['isParent'])  &&  !empty($_POST['parentCategorie']))
                    ){
                        Toolbox::dataJson(false, "Erreur de selection de catégorie");
                        exit;
                    }
                    $parent = null;
                    if (!empty($_POST['parentCategorie'])) {
                        $parent = htmlspecialchars($_POST['parentCategorie']);
                    }
                    $categoryName = htmlspecialchars($_POST['name']);
                    $descriptionCategory = htmlspecialchars($_POST['description']);

                    $this->categorys->setCategoryName($categoryName);
                    $this->categorys->setCategoryDescription($descriptionCategory);
                    $this->categorys->setCategoryIdParent($parent);

                    if ($this->categorysModel->addCategory($this->categorys)){
                        $data = [
                            'name' => $categoryName,
                            'description' => $descriptionCategory,
                            'parent' => $parent
                        ];
                        $message = "Catégorie ajoutée avec succès";
                        Toolbox::dataJson(true, $message , $data);
                        Toolbox::ajouterMessageAlerte($message, 'vert');
                        exit;
                    }
                    Toolbox::dataJson(false, 'Erreur d\'enregistrement');
                    exit;

                }
                Toolbox::dataJson(false, 'Aucune données');
                exit;

            }


            $parentCategories = $this->categorysModel->getParentCategory();
            $data_page = [
                "pageDescription" => "Dashboard, ajouter une catégories",
                "pageTitle" => "Ajouter une catégorie",
                "view" => "../Views/dashboard/dashboardCategoriesForm.php",
                "css" => "./style/dashboard/db.css",
                "script" => "./js/db/form.js",
                "bootstrapCSS" => "https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css",
                "template" => "../Views/common/template.php",
                'tokenCSRF' => $_SESSION['tokenCSRF'],
                'parentCategories' => $parentCategories,
                'action' => 'Ajouter'
            ];

            $this->render($data_page);
        }else{
            parent::pageErreur('Page inexistante');
        }
    }
    public function categoryEdit(string $id)

    {

        if (Securite::isConnected() && $_SESSION['profil']['roleName'] === 'Administrateur'){

            if (($_SERVER['REQUEST_METHOD'] === 'POST') && Securite::verifCSRF()) {
                if (!empty($_POST['name']) && !empty($_POST['description'])){
                    if ((empty($_POST['isParent'])  &&  empty($_POST['parentCategorie'])) ||
                        (!empty($_POST['isParent'])  &&  !empty($_POST['parentCategorie']))
                    ){
                        Toolbox::dataJson(false, "Erreur de selection de catégorie");
                        exit;
                    }
                    $parent = null;
                    if (!empty($_POST['parentCategorie'])) {
                        $parent = htmlspecialchars($_POST['parentCategorie']);
                    }
                    $categoryName = htmlspecialchars($_POST['name']);
                    $descriptionCategory = htmlspecialchars($_POST['description']);

                    if ($id === $parent){
                        Toolbox::dataJson(false, "Erreur, la catégorie ne peux pas être son propre parent");
                        exit;
                    }

                    $this->categorys->setCategoryId(htmlspecialchars($id));
                    $this->categorys->setCategoryName($categoryName);
                    $this->categorys->setCategoryDescription($descriptionCategory);
                    $this->categorys->setCategoryIdParent($parent);
                    $data = [
                        'name' => $categoryName,
                        'description' => $descriptionCategory,
                        'parent' => $parent,
                        'objet' => $this->categorys
                    ];
                    if ($this->categorysModel->editCategory($this->categorys)){
                        $message = "Catégorie modifiée avec succès";
                        Toolbox::ajouterMessageAlerte($message, 'vert');
                        Toolbox::dataJson(true, $message, $data);
                        exit;
                    }
                    Toolbox::dataJson(false, 'Erreur d\'enregistrement', $data);
                    exit;
                }
                Toolbox::dataJson(false, 'Aucune données');
                exit;

            }

            $infoCategory = $this->categorysModel->getInfoCategory(htmlspecialchars($id));
//            dd($infoCategory);
            if($infoCategory){
                $parentCategories = $this->categorysModel->getParentCategory();
//                dd($parentCategories);
                $data_page = [
                    "pageDescription" => "Dashboard, ajouter une catégories",
                    "pageTitle" => "Modifier une catégorie",
                    "view" => "../Views/dashboard/dashboardCategoriesForm.php",
                    "css" => "./style/dashboard/db.css",
                    "script" => "./js/db/form.js",
                    "bootstrapCSS" => "https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css",
                    "template" => "../Views/common/template.php",
                    'tokenCSRF' => $_SESSION['tokenCSRF'],
                    'parentCategories' => $parentCategories,
                    'categoryName' => $infoCategory->categoryName,
                    'categoryID' => $infoCategory->categoryID,
                    'categoryDescription' => $infoCategory->categoryDescription,
                    'categoryParentID' => $infoCategory->categoryParentID,
                    'edit' => true,
                    'action' => 'Modifier'
//                    'isParent'
                ];

                $this->render($data_page);
            }else{
                parent::pageErreur('Page inexistante');
            }
        }else{
            parent::pageErreur('Page inexistante');
        }
    }
    public function categoryDelete($id)
    {
        $id = htmlspecialchars($id);
        $countChildren = $this->categorysModel->categoryHasChildren($id)->countChildren;
//        dd($countChildren > 0);
        if ($countChildren > 0){
            $message = "Suppression impossible, car cette catégorie est une catégorie parente.";
            Toolbox::ajouterMessageAlerte($message, 'rouge');
            header("Location: index.php?controller=dashboard&action=categoriesListShow");
            exit;
        }

        $topics = $this->topicsModel->getListTopicsByCat($id);
        if ($topics) {
            $message = "Suppression impossible, car cette catégorie est liée à au moins un topic.";
            Toolbox::ajouterMessageAlerte($message, 'rouge');
            header("Location: index.php?controller=dashboard&action=categoriesListShow");
            exit;
        }
        if ($this->categorysModel->deleteCategory($id)){
            //Suppression de la catégorie éffectuée avec succès
            $message = "Suppression de la catégorie éffectuée avec succès.";
            Toolbox::ajouterMessageAlerte($message, 'vert');
            header("Location: index.php?controller=dashboard&action=categoriesListShow");
            exit;
        }
    }
}
