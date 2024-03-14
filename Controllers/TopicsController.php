<?php

namespace Controllers;

use Controllers\Interfaces\TopicsControllerInterface;
use Entities\Topics;
use Entities\Messages;
use Models\TopicsModel;
use Models\MessagesModel;
use Models\CategorysModel;
use Controllers\MainController;
use Controllers\Services\Toolbox;
use Controllers\Services\Securite;
use Controllers\Traits\VerifPostTrait;

class TopicsController extends MainController implements TopicsControllerInterface
{

    private CategorysModel $categorysModel;
    private TopicsModel $topicsModel;
    private MessagesModel $messagesModel;
    private Messages $message;
    private Topics $topic;
    use VerifPostTrait;

    public function __construct()
    {
        $this->categorysModel = new CategorysModel();
        $this->topicsModel = new TopicsModel();
        $this->messagesModel = new MessagesModel();
        $this->message = new Messages;
        $this->topic = new Topics;;
    }



    /**
     * @param  mixed $catID
     * @return void
     */
    public function list($catID)    {

        //en cliquant sur le nom de la sous catégorie depuis la page d'acceuil, il faudra afficher la liste des topics en fonction de l'ID de la sous catégorie      



        $listTopics = $this->topicsModel->getListTopicsByCat($catID);
//        dd($listTopics);
        //si $souscategoryID est un id existant et que la requête à renvoyer un résultat
//        if ($listTopics) {
            $infosCategory = $this->categorysModel->getInfoCategory($catID);

            $data_page = [
                "pageDescription" => "Catégorie " . $infosCategory->categoryName . " du site Guitare-forum",
                "pageTitle" => $infosCategory->categoryName . " | Guitare-forum",
                "view" => "../Views/topics/viewTopicsList.php",
                "css" => "./style/topicsByCat.css",
                "template" => "../Views/common/template.php",
                "categoryName" => $infosCategory->categoryName,
//                "categorySlug" => $infosCategory->categorySlug,
                "categoryID" => $infosCategory->categoryID,
                "listTopics" => $listTopics,
                'categoryParentName' => $infosCategory->categoryParentName,
                'categoryParentID' => $infosCategory->categoryParentID,
                "tokenCSRF" => $_SESSION['tokenCSRF']
            ];

            $this->render($data_page);
//        } else {
//            header("Location:index.php");
//        }
    }

    /**
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
     *
     * @return void
     */
    public function createTitleTopic()
    {

        if ($this->VerifPostTrait()) {
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
                Toolbox::dataJson(false, "Erreur : données manquantes");
                exit;
            }
        }
    }
}
