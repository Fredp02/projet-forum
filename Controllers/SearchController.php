<?php

namespace Controllers;

use Controllers\Services\Toolbox;
use Controllers\Services\Securite;

class SearchController extends MainController
{
    public function index()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (Securite::verifCSRF()) {
                if (!empty($_POST['inputSearch'])) {

                    //! exemple de CHATGPT : 

                    // Inclure la bibliothèque PHPSearch
                    require_once '../vendor\mrfakename\phpsearch\src\PHPSearch\PHPSearch.php';

                    // Créer une instance de PHPSearch
                    $search = new Search();

                    // Définir les options de recherche
                    $search->setDatabaseHost('your_database_host');
                    $search->setDatabaseUsername('your_database_username');
                    $search->setDatabasePassword('your_database_password');
                    $search->setDatabaseName('your_database_name');
                    $search->setTable('your_table_name');
                    $search->setColumns(['column1', 'column2', 'column3']);
                    $search->setSearchQuery($_POST['inputSearch']);

                    // Effectuer la recherche et stocker les résultats dans une variable
                    $results = $search->search();
                }
            } else {
                Toolbox::ajouterMessageAlerte("Session expirée, veuillez recommencer", 'rouge');
                unset($_SESSION['profil']);
                unset($_SESSION['tokenCSRF']);
            }
        } else {
            header("Location:index.php");
            exit;
        }
    }
}
