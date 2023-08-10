<?php

namespace Controllers;

// use vendor\mrfakename\PHPSearch\PHPSearch;
// use Models\PHPSearch;
use Models\SearchModel;
use Controllers\Services\Toolbox;
use Controllers\Services\Securite;
// use Wamania\Snowball\StemmerManager;


class SearchController extends MainController
{
    private $searchModel;

    public function __construct()
    {
        $this->searchModel = new SearchModel();
    }


    public function index()
    {

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (Securite::verifCSRF()) {

                if (!empty($_POST['inputSearch'])) {

                    //on nettoie la chaine en supprimant les "mots vides" et en utilisant un stemmer
                    $string = Toolbox::cleanSearch(htmlspecialchars($_POST['inputSearch']));

                    $result = $this->searchModel->defaultSearch($string);
                    dd($result);
                }
            } else {
                Toolbox::ajouterMessageAlerte("Session expirée, veuillez recommencer", 'rouge');
                unset($_SESSION['profil']);
                unset($_SESSION['tokenCSRF']);
            }
        }

        $data_page = [
            "pageDescription" => "Recherche sur guitare forum",
            "pageTitle" => "Recherche",
            "view" => "../Views/search.php",
            "template" => "../Views/common/template.php",
            "css" => "./style/searchStyle.css",
            // "script" => "./js/validFormRegister.js",
            "tokenCSRF" => $_SESSION['tokenCSRF']
        ];

        $this->render($data_page);
    }
}
