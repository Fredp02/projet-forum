<?php

namespace Controllers;

use Models\UsersModel;
use Models\CategorysModel;
use Controllers\Services\Toolbox;

class CategoryController extends MainController
{
    private $categorysModel;
    private $usersModel;

    public function __construct()
    {
        $this->categorysModel = new CategorysModel();
        $this->usersModel = new UsersModel();
    }

    public function index($parentCatID)
    {

        $getDetailsParentCat = $this->categorysModel->getDetailsParentCat($parentCatID);
        if ($getDetailsParentCat) {
            $data_page = [
                "pageDescription" => "Catégorie",
                "pageTitle" => "Catégorie",
                "view" => "../Views/viewCategory.php",
                "css" => "./style/homeStyle.css",
                "template" => "../Views/common/template.php",
                'getDetailsParentCat' => $getDetailsParentCat,
                "tokenCSRF" => $_SESSION['tokenCSRF']
            ];
            $this->render($data_page);
        } else {
            header('Location:index.php');
        }
    }
}
