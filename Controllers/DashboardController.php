<?php

namespace Controllers;

use Controllers\Services\Securite;
use Models\UsersModel;
use Models\CategorysModel;
use Controllers\Services\Toolbox;

class DashboardController extends MainController
{
    private $categorysModel;
//    private $usersModel;

    public function __construct()
    {
        $this->categorysModel = new CategorysModel();
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
}
