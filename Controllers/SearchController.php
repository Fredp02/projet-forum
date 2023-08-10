<?php

namespace Controllers;

// use vendor\mrfakename\PHPSearch\PHPSearch;
// use Models\PHPSearch;
use Entities\Categorys;
use Models\SearchModel;
use Models\CategorysModel;
use Controllers\Services\Toolbox;
use Controllers\Services\Securite;

// use Wamania\Snowball\StemmerManager;


class SearchController extends MainController
{
    private $searchModel;
    private $categorysModel;

    public function __construct()
    {
        $this->searchModel = new SearchModel();
        $this->categorysModel = new CategorysModel();
    }


    public function index()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Récupération des données du formulaire
            $keywords = isset($_POST['keywords']) ? htmlspecialchars($_POST['keywords']) : '';
            $titleOnly = isset($_POST['checkboxTitleOnly']) ? true : false;
            $postedBy = isset($_POST['postedBy']) ? htmlspecialchars($_POST['postedBy']) : '';
            $dateFrom = isset($_POST['dateFrom']) ? htmlspecialchars($_POST['dateFrom']) : '';
            $dateTo = isset($_POST['dateTo']) ? htmlspecialchars($_POST['dateTo']) : '';
            $selectForum = isset($_POST['selectForum']) ? htmlspecialchars($_POST['selectForum']) : '';
            $sort = isset($_POST['sort']) ? htmlspecialchars($_POST['sort']) : '';

            // Validation des données
            if (empty($keywords)) {
                // Le champ "Mots clés" est vide
                // Afficher un message d'erreur
                echo "Veuillez entrer des mots clés pour effectuer une recherche.";
            } else {
                // Le champ "Mots clés" n'est pas vide
                // Échappement des données pour éviter les attaques d'injection SQL


                // Construire la requête SQL en fonction des critères de recherche spécifiés
                $query = "SELECT * FROM table WHERE ";
                if ($titleOnly) {
                    // Recherche dans le titre seulement
                    $query .= "title LIKE '%$keywords%'";
                } else {
                    // Recherche dans tous les champs pertinents
                    $query .= "(title LIKE '%$keywords%' OR content LIKE '%$keywords%' OR author LIKE '%$keywords%')";
                }
                if (!empty($postedBy)) {
                    // Filtre par auteur
                    $query .= " AND author='$postedBy'";
                }
                if (!empty($dateFrom) && !empty($dateTo)) {
                    // Filtre par date
                    $query .= " AND date BETWEEN '$dateFrom' AND '$dateTo'";
                }
                if (!empty($selectForum)) {
                    // Filtre par forum
                    $query .= " AND forum='$selectForum'";
                }
                if (!empty($sort)) {
                    // Tri des résultats
                    $query .= " ORDER BY $sort";
                }

                // Exécution de la requête SQL et affichage des résultats
                $result = mysqli_query($conn, $query);
                if (!$result) {
                    die('Invalid query: ' . mysqli_error($conn));
                }
                while ($row = mysqli_fetch_assoc($result)) {
                    // Affichage des résultats
                    // ...
                }
            }
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (Securite::verifCSRF()) {

                if (!empty($_POST['keywords'])) {

                    //on nettoie la chaine en supprimant les "mots vides" et en utilisant un stemmer
                    $string = Toolbox::cleanSearch(htmlspecialchars($_POST['keywords']));

                    $result = $this->searchModel->defaultSearch($string);
                    dd($result);
                }
            } else {
                Toolbox::ajouterMessageAlerte("Session expirée, veuillez recommencer", 'rouge');
                unset($_SESSION['profil']);
                unset($_SESSION['tokenCSRF']);
            }
        }
        $categorysList = $this->categorysModel->getCategorysOrderByParent();

        $data_page = [
            "pageDescription" => "Recherche sur guitare forum",
            "pageTitle" => "Recherche",
            "view" => "../Views/search.php",
            "template" => "../Views/common/template.php",
            "css" => "./style/searchStyle.css",
            'categorysList' => $categorysList,
            // "script" => "./js/validFormRegister.js",
            "tokenCSRF" => $_SESSION['tokenCSRF']
        ];

        $this->render($data_page);
    }
}
