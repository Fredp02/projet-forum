<?php

namespace Controllers;

use Entities\Messages;
use Models\TopicsModel;
use Models\MessagesModel;
use Models\CategorysModel;
use Controllers\MainController;
use Controllers\Services\Toolbox;
use Controllers\Services\Securite;
use Entities\Topics;

class TopicsController extends MainController
{

    private $categorysModel;
    private $topicsModel;
    private $messagesModel;
    private $message;
    private $topic;

    public function __construct()
    {
        $this->categorysModel = new CategorysModel();
        $this->topicsModel = new TopicsModel();
        $this->messagesModel = new MessagesModel();
        $this->message = new Messages;
        $this->topic = new Topics;;
    }



    /**
     * Affiche la liste des topics
     *
     * @param  mixed $catID
     * @return void
     */
    public function list($catID)
    {

        //en cliquant sur le nom de la sous catégorie depuis la page d'acceuil, il faudra afficher la liste des topics en fonction de l'ID de la sous catégorie      



        $listTopics = $this->topicsModel->getListTopicsByCat($catID);
        //si $souscategoryID est un id existant et que la requête à renvoyer un résultat
        if ($listTopics) {
            $infosCategory = $this->categorysModel->getInfoCategory($catID);

            $data_page = [
                "pageDescription" => "Catégorie " . $infosCategory->categoryName . " du site Guitare-forum",
                "pageTitle" => $infosCategory->categoryName . " | Guitare-forum",
                "view" => "../Views/topics/viewTopicsList.php",
                "css" => "./style/topicsByCat.css",
                "template" => "../Views/common/template.php",
                "categoryName" => $infosCategory->categoryName,
                "categorySlug" => $infosCategory->categorySlug,
                "categoryID" => $infosCategory->categoryID,
                "listTopics" => $listTopics,
                'categoryParentName' => $infosCategory->categoryParentName,
                'categoryParentID' => $infosCategory->categoryParentID

            ];


            $this->render($data_page);
        } else {
            header("Location:index.php");
        }
    }

    /**
     * Affiche le contenu d'un topic : son titre est les messages associés
     *
     * @param  mixed $threadID
     * @return void
     */
    public function thread($threadID)
    {
        $infosTopic = $this->topicsModel->getTopicInfos($threadID);
        //si $topicID est un id existant et que la requête à renvoyer un résultat
        if ($infosTopic) {
            $messagesTopics = $this->messagesModel->getMessagesByTopic($threadID);

            //une "vue" en plus au compteur
            $this->topic->setTopicID($threadID);
            $this->topicsModel->addViewTopic($this->topic);


            $data_page = [
                "pageDescription" => "Sujet : " . $infosTopic->topicTitle . " du site Guitare-forum",
                "pageTitle" => $infosTopic->topicTitle . " | Guitare-forum",
                "view" => "../Views/topics/viewThread.php",
                "css" => "./style/topicStyle.css",
                "script" => "./js/createMessage.js",
                "template" => "../Views/common/template.php",
                //editor quill
                "quillSnowCSS" => "//cdn.quilljs.com/1.3.6/quill.snow.css",
                "quillEmojiCSS" => "./quill/dist/quill-emoji.css",
                "quillJS" => "//cdn.quilljs.com/1.3.6/quill.js",
                "quillEmojiJS" => "./quill/dist/quill-emoji.js",
                "quillImageJS" => "./quill/dist/quill.imageUploader.js",
                "quillImageCSS" => "./quill/dist/quill.imageUploader.css",
                //----------
                'userID' => $_SESSION['profil']["userID"] ?? "",
                "tokenCSRF" => $_SESSION["tokenCSRF"],
                "infosTopic" => $infosTopic,
                'messagesTopics' => $messagesTopics
            ];
            $this->render($data_page);
        } else {
            //si "id" inexistant, on redirige.
            header("Location:index.php");
        }
    }



    /**
     * Affiche la vue qui permet de créer un topic. Le texte descriptif du premier topic est considéré comme message numéro 1 du topci  , donc géré par les méthodes uploadImage() si besoin et validation(). 
     *
     * @param  mixed $categoryID
     * @return void
     */
    public function createTopicView($categoryID)
    {
        if (Securite::isConnected()) {
            $infoCategory = $this->categorysModel->getInfoCategory($categoryID);
            $data_page = [
                "pageDescription" => "Page de création d'un topic sur Guitare-forum",
                "pageTitle" => "Création topic | Guitare-forum",
                "view" => "../Views/topics/viewCreateTopic.php",
                "css" => "./style/createTopicStyle.css",
                "script" => "./js/createTopic.js",
                "template" => "../Views/common/template.php",
                //editor quill
                "quillSnowCSS" => "//cdn.quilljs.com/1.3.6/quill.snow.css",
                "quillEmojiCSS" => "./quill/dist/quill-emoji.css",
                "quillJS" => "//cdn.quilljs.com/1.3.6/quill.js",
                "quillEmojiJS" => "./quill/dist/quill-emoji.js",
                "quillImageJS" => "./quill/dist/quill.imageUploader.js",
                "quillImageCSS" => "./quill/dist/quill.imageUploader.css",
                //----------
                "infoCategory" => $infoCategory

            ];
            $this->render($data_page);
        } else {
            header("Location:index.php?controller=login");
            exit;
        }
    }



    /**
     * Crée le titre du topic
     *
     * @return void
     */
    public function createTitleTopic()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && Securite::isConnected()) {
            if (!empty($_POST['tokenCSRF']) && hash_equals($_SESSION['tokenCSRF'], $_POST['tokenCSRF'])) {
                if (!empty($_POST['titleTopic'])) {
                    $categoryID = htmlspecialchars($_POST['categoryID']);
                    //ajouter un appel de requete pour savoir si on a un retour d'une catégorie qui à un parent différent de null, sinon on va créer un topic sur une catégorie "parente" générale.
                    //OU BIEN vérifier que le champ "parent" soit différent de null directement depuis l'appel de  getInfoCategory
                    if ($this->categorysModel->getInfoCategory($categoryID)) {
                        //si la catgéorie existe, je créer le titre du topic

                        $this->topic->setTopicTitle(Securite::htmlPurifier($_POST['titleTopic']));
                        $this->topic->setTopicCategoryID($categoryID);
                        $this->topic->setTopicUserID($_SESSION['profil']['userID']);

                        //si réponse du model ok
                        if ($this->topicsModel->createTopic($this->topic)) {
                            $topicID = $this->topicsModel->lastInsertId();
                            Toolbox::dataJson(true, "createTopic ok", [
                                'topicID' => $topicID,
                            ]);
                            exit;
                        } else {
                            Toolbox::dataJson(false, "erreur survenue lors de la création du topic");
                            exit;
                        }
                    } else {
                        Toolbox::dataJson(false, "Catégorie introuvable");
                        exit;
                    }
                } else {
                    Toolbox::dataJson(false, "Erreur : données manquante");
                    exit;
                }
            } else {
                Toolbox::ajouterMessageAlerte("Session expirée, veuillez recommencer", 'rouge');
                unset($_SESSION['profil']);
                unset($_SESSION['tokenCSRF']);
                Toolbox::dataJson(false, "expired token");
                exit;
            }
        } else {
            Toolbox::dataJson(false, "no-connected");
            exit;
        }
    }
}
