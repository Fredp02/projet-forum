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
    private const LIMITE = 4;

    private SearchModel $searchModel;
    private CategorysModel $categorysModel;
    private UsersModel $usersModel;

    public function __construct()
    {
        $this->searchModel = new SearchModel();
        $this->categorysModel = new CategorysModel();
        $this->usersModel = new UsersModel();

    }


    public function index(): void
    {

        $categorysList = $this->categorysModel->getCategorysOrderByParent();

        $data_page = [
            "pageDescription" => "Recherche sur guitare forum",
            "pageTitle" => "Recherche",
            "view" => "../Views/search/viewSearch.php",
            "template" => "../Views/common/template.php",
            "css" => "./style/searchStyle.css",
            'categorysList' => $categorysList,
            // "script" => "./js/validFormRegister.js",
            "tokenCSRF" => $_SESSION['tokenCSRF']
        ];
        $this->render($data_page);
    }

    public function display($key = null, $title = null, $author = null, $from = null, $to = null, $select = null, $order = null, $sort = null, $numPage = 1): void
    {

        try {

            if (empty($key)) {
                // Le champ "Mots clés" est vide
                throw new Exception('Vous devez entrer un mot clé pour effectuer une recherche');
            }
//            dd($_SERVER);
// Récupération des données du formulaire
            $key = isset($_GET['key']) ? htmlspecialchars($_GET['key']) : '';
            $numPage = isset($_GET['numPage']) ? (int)htmlspecialchars($_GET['numPage']) : 1;
            $queryData = [
                'title' => isset($_GET['title']), //isset($_GET['title']) ? true : false;
                'author' => isset($_GET['author']) ? htmlspecialchars($_GET['author']) : '',
                'from' => isset($_GET['from']) ? htmlspecialchars($_GET['from']) : '',
                'to' => isset($_GET['to']) ? htmlspecialchars($_GET['to']) : '',
                'select' => $_GET['select'] ?? '', //isset($_GET['select']) ? $_GET['select'] : '';
                'order' => isset($_GET['order']) ? htmlspecialchars($_GET['order']) : '',
                'sort' => isset($_GET['sort']) ? htmlspecialchars($_GET['sort']) : '',

                // Je convertis $numPage en "int" dans tous les cas. Cela garantit que c'est un "int" et permettra ensuite d'utiliser la stricte égalité.
            ];

            // $searchData['']
            if ($queryData['author'] && !$this->usersModel->getUserByPseudo($queryData['author'])) {
                throw new Exception('Ce membre n\'a pas été trouvé');
            }
            //on nettoie la chaine en supprimant les "mots vides" et en utilisant un stemmer
            $string = Toolbox::cleanSearch($key);

            //j'initialise la requete sans pagination, ni limite ni offset
            $initQuery = new QueryBuilder($queryData, false, null, null);

            $result = $this->searchModel->search($initQuery->create(), $string);
            //si aucun résultats, on entre dans le catch

            //si on entre manuellement un numero de page dans l'url ou s'il n'y a pas de résultat
            if ($numPage < 1 || !$result) {
                throw new Exception('Aucun résultat');
            }

            $nombreResultatTotal = count($result);
            $paginator = false;
            /**
             * $nombreResultatTotal
             * $numPage
             * LIMTE
             */
            $NBR_LINKS_PAGINATOR = 3;
            if ($nombreResultatTotal > self::LIMITE) {

                //Pagintation true:
                $paginator = true;


                //Je détermine le nombre de résultats visible dans la page
                //ainsi que le nombre total de pages pour gérer l'affichage du chevron droit :
                $nombrePageTotal = (int)ceil($nombreResultatTotal / self::LIMITE);
                // Attention ici, je convertis $nombrePageTotal en "int" car le "ceil" garde la variable en "float". Cela permettra ensuite d'utiliser la stricte égalité.
                if ($numPage > $nombrePageTotal) {
                    throw new Exception('Aucun résultat');
                }
                //on calcule l'offset en fonction du numéro de la page et de la limite
                $offset = ($numPage - 1) * self::LIMITE;
                //et on initialise la requete finale avec la pagination = "true" + limite et offset
                $queryPaginated = new QueryBuilder($queryData, true, self::LIMITE, $offset);

                //Et on lance le model pour récupérer les résultats
                $result = $this->searchModel->search($queryPaginated->create(), $string);


                //On initialise l'uri sans la valeur du numéro de la page. Cela nous permettra dans la vue,
                // de concaténer l'uri avec un numéro de page spécifique à la pagination.
                $baseUri = preg_replace('/&numPage=\d+/', '', $_SERVER['REQUEST_URI']) . '&numPage=';

                $targetPage = -1;
                if ($numPage === 1) {//Si on est sur la page 1, $targetPage = 0
                    $targetPage = 0;
                }
                if ($nombrePageTotal < $NBR_LINKS_PAGINATOR) {
                    $NBR_LINKS_PAGINATOR = $nombrePageTotal;
                } elseif ($numPage === $nombrePageTotal) { //si on est sur la dernière page
                    $targetPage = -2;
                }

            }


            $data_page = [
                "pageDescription" => "Résultat de la recherche",
                "pageTitle" => "Résultat rcherche",
                "view" => "../Views/search/viewDisplaySearch.php",
                "template" => "../Views/common/template.php",
                "css" => "./style/displaySearchStyle.css",
                'result' => $result,
                'numPage' => $numPage,
                'paginator' => $paginator,
                'key' => $key,
                'baseUri' => $baseUri ?? '',
                'targetPage' => $targetPage ?? 0,
                'nombrePageTotal' => $nombrePageTotal ?? 1,
                'nbrLinksPaginator' => $NBR_LINKS_PAGINATOR
            ];
            $this->render($data_page);
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
            "view" => "../Views/search/viewSearch.php",
            "template" => "../Views/common/template.php",
            "css" => "./style/searchStyle.css",
            'categorysList' => $categorysList,
            // "script" => "./js/validFormRegister.js",
            "tokenCSRF" => $_SESSION['tokenCSRF']
        ];

        $this->render($data_page);
    }
}
