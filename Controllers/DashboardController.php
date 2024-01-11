<?php

namespace Controllers;

use Controllers\Services\Securite;
use Models\UsersModel;
use Models\CategorysModel;
use Controllers\Services\Toolbox;

class DashboardController extends MainController
{
//    private $categorysModel;
//    private $usersModel;

    public function __construct()
    {
//        $this->categorysModel = new CategorysModel();
//        $this->usersModel = new UsersModel();
    }

    public function index()
    {
//        $_SESSION['profil']['roleName'] === 'Administrateur')
        if (Securite::isConnected() && $_SESSION['profil']['roleName'] === 'Administrateur'){
            $data_page = [
                "pageDescription" => "Dashboard",
                "pageTitle" => "Dashboard forum",
                "view" => "../Views/dashboard/dashboardView.php",
//            "css" => "./style/homeStyle.css",
                "template" => "../Views/common/template.php",
                'tokenCSRF' => $_SESSION['tokenCSRF']
            ];

            $this->render($data_page);
        }else{
            parent::pageErreur('Page inexistante');
        }

    }
}
