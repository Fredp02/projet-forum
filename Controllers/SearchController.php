<?php

namespace Controllers;

// use vendor\mrfakename\PHPSearch\PHPSearch;
// use Models\PHPSearch;
use Exception;
use Entities\Categorys;
use Models\SearchModel;
use Models\CategorysModel;
use Controllers\Services\Toolbox;
use Controllers\Services\Securite;
use Controllers\Services\QueryBuilder;
use Models\UsersModel;

// use Wamania\Snowball\StemmerManager;


class SearchController extends MainController
{
    private $searchModel;
    private $categorysModel;
    private $usersModel;

    public function __construct()
    {
        $this->searchModel = new SearchModel();
        $this->categorysModel = new CategorysModel();
        $this->usersModel = new UsersModel();
    }


    public function index()
    {


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

    public function build($key = null, $title = null, $author = null, $from = null, $to = null, $select = null, $order = null, $sort = null)
    {

        try {

            if (empty($key)) {
                // Le champ "Mots clés" est vide
                throw new Exception('Vous devez entrer un mot clé pour effectuer une recherche');
            } else {
                // Récupération des données du formulaire
                $key = isset($_GET['key']) ? htmlspecialchars($_GET['key']) : '';
                $title = isset($_GET['title']) ? true : false;
                $author = isset($_GET['author']) ? htmlspecialchars($_GET['author']) : '';
                $from = isset($_GET['from']) ? htmlspecialchars($_GET['from']) : '';
                $to = isset($_GET['to']) ? htmlspecialchars($_GET['to']) : '';
                $select = isset($_GET['select']) ? $_GET['select'] : '';
                $order = isset($_GET['order']) ? htmlspecialchars($_GET['order']) : '';
                $sort = isset($_GET['sort']) ? htmlspecialchars($_GET['sort']) : '';


                if ($author && !$this->usersModel->getUserByPseudo($author)) {
                    throw new Exception('Ce membre n\'a pas été trouvé');
                }


                //on nettoie la chaine en supprimant les "mots vides" et en utilisant un stemmer
                $string = Toolbox::cleanSearch($key);

                $initQuery = new QueryBuilder($title, $author, $from, $to, $select, $order, $sort);
                $query = $initQuery->create();

                // Exécution de la requête SQL et affichage des résultats
                $result = $this->searchModel->search($query, $string);
                dd($result);
            }
        } catch (Exception $e) {
            Toolbox::ajouterMessageAlerte($e->getMessage(), 'rouge');
            header('Location:index.php?controller=search');
            exit;
        }


        // if ($_SERVER['REQUEST_METHOD'] === 'POST') {


        //     if (!empty($_POST['key'])) {

        //         //on nettoie la chaine en supprimant les "mots vides" et en utilisant un stemmer
        //         $string = Toolbox::cleanSearch(htmlspecialchars($_POST['key']));

        //         $result = $this->searchModel->defaultSearch($string);
        //         dd($result);
        //     }
        // } else {
        //     Toolbox::ajouterMessageAlerte("Session expirée, veuillez recommencer", 'rouge');
        //     unset($_SESSION['profil']);
        //     unset($_SESSION['tokenCSRF']);
        // }
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
