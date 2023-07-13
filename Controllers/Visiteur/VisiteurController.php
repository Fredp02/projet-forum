<?php

namespace Controllers\Visiteur;

use Controllers\MainController;
use Controllers\Services\Toolbox;
use Models\Visiteur\Topics\TopicsModel;
use Models\Visiteur\Messages\MessagesModel;
use Models\Visiteur\Categorys\CategorysModel;

class VisiteurController extends MainController
{

    private $categorysModel;
    private $topicsModel;
    private $MessagesModel;
    // private $user;

    public function __construct()
    {
        $this->categorysModel = new CategorysModel();
        $this->topicsModel = new TopicsModel();
        $this->MessagesModel = new MessagesModel();
    }
    // private $visiteurManager;

    // public function __construct()
    // {
    //     $this->visiteurManager = new VisiteurManager;
    // }

    public function accueil()
    {
        $allCategorys = $this->categorysModel->getCategorysList();

        // $categories = [];
        // foreach ($allCategorys as $row) {
        //     $parentCategoryName = $row['categoryName'];
        //     if (!isset($categories[$parentCategoryName])) {
        //         $categories[$parentCategoryName] = [];
        //     }
        //     if (isset($row['subCategoryName'])) {
        //         $subCategory = [
        //             'name' => $row['subCategoryName'],
        //             'totalTopics' => $row['totalTopics'],
        //             'totalMessages' => $row['totalMessages'],
        //             'latestTopicTitle' => $row['latestTopicTitle'],
        //             'latestMessageDate' => $row['latestMessageDate'],
        //             'latestMessageUser' => $row['latestMessageUser']
        //         ];
        //         $categories[$parentCategoryName][] = $subCategory;
        //     }
        // }
        $resultatsRegroupes = array_reduce($allCategorys, function ($accumulateur, $ligne) {
            // On vérifie si la catégorie parente existe déjà dans le tableau des résultats
            if (!isset($accumulateur[$ligne->parentCategoryName])) {
                // Si ce n'est pas le cas, on crée une nouvelle entrée pour cette catégorie
                $accumulateur[$ligne->parentCategoryName] = [];
            }

            // On crée un tableau pour stocker les informations de la sous-catégorie
            $subCategory = [
                'name' => $ligne->subCategoryName,
                'description' => $ligne->subCategoryDesc,
                'id' => $ligne->subCategoryID,
                'url' => 'accueil/' . $ligne->subCategorySlug . '.' . $ligne->subCategoryID,
                'totalTopics' => $ligne->totalTopics,
                'totalMessages' => $ligne->totalMessages,
                'lastTopicTitle' => $ligne->lastTopicTitle,
                'lastMessageDate' => Toolbox::convertDate($ligne->lastMessageDate, 'd MMMM Y'),
                'lastMessageUser' => $ligne->lastMessageUser
            ];
            // On ajoute la sous-catégorie au tableau des sous-catégories pour cette catégorie parente
            $accumulateur[$ligne->parentCategoryName][] = $subCategory;
            // On retourne le tableau des résultats mis à jour
            return $accumulateur;
        }, []);

        //même résultat que le array_reduce
        // $groupedResults = [];
        // foreach ($allCategorys as $row) {
        //     $category = $row->parentCategoryName;
        //     $subcategory = $row->subCategoryName;
        //     // if ($subcategory !== null) {
        //     if (!isset($groupedResults[$category])) {
        //         $groupedResults[$category] = [];
        //     }
        //     // $groupedResults[$category][] = $subcategory;
        //     $groupedResults[$category][$subcategory][] = $row->totalTopics;
        //     $groupedResults[$category][$subcategory][] = $row->totalMessages;
        //     // }
        // }

        //* Sinon, autre manière plus complexe de récupérer toute les catégories et de les classer en fonction de leur parent, quelque soit le nombre de sous categories
        // ! méthodes utilisées : 
        //! voir méthode getCategorysListWithRECURSIVE() du model
        // ! voir méthode static buildCategoryHierarchy() de la Toolbox

        // on récupère la liste de catégorie et sous catégorie avec un "level"
        //$allCategorys = $this->CategorysModel->getCategorysListWithRECURSIVE();
        //on utilise la méthode pour construire un tableau hérarchique
        // $hierarchy = Toolbox::buildCategoryHierarchy($allCategorys);


        $data_page = [
            "pageDescription" => "Description de la page d'accueil",
            "pageTitle" => "Accueil Guitare forum",
            "view" => "../Views/Visiteur/viewAccueil.php",
            "css" => "public/style/accueilStyle.css",
            "template" => "../Views/common/template.php",
            'allCategorys' => $allCategorys,
            "categorysList" => $resultatsRegroupes
        ];
        $this->genererPage($data_page);
    }

    public function displayTopicsList($sousCategoryURL)
    {
        //en cliquant sur le nom de la sous catégorie depuis la page d'acceuil, il faudra afficher la liste des topics en fonction de l'ID de la sous catégorie

        //je récupère la seconde partie du slug séparée par un "." qui correspond à l'id de la sous catégorie

        $array = explode(".", filter_var($sousCategoryURL, FILTER_SANITIZE_URL));
        if (isset($array[1])) {
            $souscategoryID = $array[1];
            $listTopics = $this->topicsModel->getListTopicsByCat($souscategoryID);
            //si $souscategoryID est un id existant et que la requête à renvoyer un résultat
            if ($listTopics) {
                $infosCategory = $this->categorysModel->getInfoCategory($souscategoryID);

                $data_page = [
                    "pageDescription" => "Catégorie " . $infosCategory->categoryName . " du site Guitare-forum",
                    "pageTitle" => $infosCategory->categoryName . " | Guitare-forum",
                    "view" => "../Views/Visiteur/viewListTopics.php",
                    "css" => "public/style/listTopicsStyle.css",
                    "template" => "../Views/common/template.php",
                    "categoryName" => $infosCategory->categoryName,
                    "categorySlug" => $infosCategory->categorySlug,
                    "categoryID" => $infosCategory->categoryID,
                    "listTopics" => $listTopics
                ];
                $this->genererPage($data_page);
            } else {
                header("Location: " . URL);
            }
        } else {
            header("Location: " . URL);
        }

        // $souscategoryID = explode(".", filter_var($sousCategoryURL, FILTER_SANITIZE_URL))[1];

    }
    public function displayTopic($topicUrl)
    {
        $array = explode(".", filter_var($topicUrl, FILTER_SANITIZE_URL));
        //si l'url avait bien un '.' et que le explode à bien fonctionné
        if (isset($array[1])) {
            $topicID = $array[1];

            $infosTopic = $this->topicsModel->getTopicInfos($topicID);
            //si $topicID est un id existant et que la requête à renvoyer un résultat
            if ($infosTopic) {
                // $messagesTopics = $this->topicsModel->getMessagesByTopic($topicID);
                $messagesTopics = $this->MessagesModel->getMessagesByTopic($topicID);

                // dd($infosTopic);
                $data_page = [
                    "pageDescription" => "Sujet : " . $infosTopic->topicTitle . " du site Guitare-forum",
                    "pageTitle" => $infosTopic->topicTitle . " | Guitare-forum",
                    "view" => "../Views/Visiteur/viewTopic.php",
                    "css" => "public/style/topicStyle.css",
                    //editor quill
                    "quillSnowCSS" => "//cdn.quilljs.com/1.3.6/quill.snow.css",
                    "quillEmojiCSS" => "public/quill/dist/quill-emoji.css",
                    "quillJS" => "//cdn.quilljs.com/1.3.6/quill.js",
                    "quillEmojiJS" => "public/quill/dist/quill-emoji.js",
                    "quillImageJS" => "public/quill/dist/quill.imageUploader.js",
                    "quillImageCSS" => "public/quill/dist/quill.imageUploader.css",
                    //----------
                    "script" => "public/js/responseTopic.js",
                    "template" => "../Views/common/template.php",
                    "tokenCSRF" => $_SESSION["tokenCSRF"],
                    // "categoryName" => $infosTopic->categoryName,
                    // "categorySlug" => $infosTopic->categorySlug,
                    // "categoryID" => $infosTopic->categoryID,
                    "infosTopic" => $infosTopic,
                    'messagesTopics' => $messagesTopics
                ];
                $this->genererPage($data_page);
            } else {
                //si "id" inexistant, on redirige.
                header("Location: " . URL);
            }
        } else {
            header("Location: " . URL);
        }
    }

    public function connexionView()
    {
        $data_page = [
            "pageDescription" => "Page de connexion au site Guitare Forum",
            "pageTitle" => "Connexion | Guitare Forum",
            "view" => "../Views/Visiteur/viewConnexion.php",
            "template" => "../Views/common/template.php",
            "css" => "public/style/connexionStyle.css",
            "script" => "public/js/validFormPageLogin.js",
            "tokenCSRF" => $_SESSION['tokenCSRF'],
            "previousURL" => $_SERVER['HTTP_REFERER'],
        ];
        $this->genererPage($data_page);
    }

    public function inscription()
    {
        $data_page = [
            "pageDescription" => "Page de création de compte",
            "pageTitle" => "Inscription",
            "view" => "../Views/Visiteur/viewInscription.php",
            "template" => "../Views/common/template.php",
            "css" => "public/style/inscriptionStyle.css",
            "script" => "public/js/validFormInscription.js",
            "tokenCSRF" => $_SESSION['tokenCSRF']
        ];
        $this->genererPage($data_page);
    }
    public function profil()
    {
        $data_page = [
            "pageDescription" => "Cette page affiche le détail de votre profil sur Guitare Forum",
            "pageTitle" => "Mon profil",
            "view" => "../Views/Visiteur/viewProfil.php",
            "template" => "Views/common/template.php",
            "css" => "public/style/profilStyle.css",
            // "script" => "public/js/validFormInscription.js"
        ];
        $this->genererPage($data_page);
    }




    public function pageErreur($msg)
    {
        parent::pageErreur($msg);
    }
}
