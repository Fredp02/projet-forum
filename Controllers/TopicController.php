<?php

namespace Controllers;

use Controllers\MainController;
use Models\MessagesModel;
use Models\TopicsModel;
// use Models\CategorysModel;

class TopicController extends MainController
{

    // private $categorysModel;
    private $topicsModel;
    private $MessagesModel;
    // private $user;

    public function __construct()
    {
        // $this->categorysModel = new CategorysModel();
        $this->topicsModel = new TopicsModel();
        $this->MessagesModel = new MessagesModel();
    }

    public function topic($topicUrl)
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
                    "view" => "../Views/topics/viewTopic.php",
                    "css" => "/style/topicStyle.css",
                    //editor quill
                    "quillSnowCSS" => "//cdn.quilljs.com/1.3.6/quill.snow.css",
                    "quillEmojiCSS" => "public/quill/dist/quill-emoji.css",
                    "quillJS" => "//cdn.quilljs.com/1.3.6/quill.js",
                    "quillEmojiJS" => "/quill/dist/quill-emoji.js",
                    "quillImageJS" => "/quill/dist/quill.imageUploader.js",
                    "quillImageCSS" => "/quill/dist/quill.imageUploader.css",
                    //----------
                    "script" => "/js/responseTopic.js",
                    "template" => "../Views/common/template.php",
                    "tokenCSRF" => $_SESSION["tokenCSRF"],
                    // "categoryName" => $infosTopic->categoryName,
                    // "categorySlug" => $infosTopic->categorySlug,
                    // "categoryID" => $infosTopic->categoryID,
                    "infosTopic" => $infosTopic,
                    'messagesTopics' => $messagesTopics
                ];
                $this->render($data_page);
            } else {
                //si "id" inexistant, on redirige.
                header("Location: " . URL);
            }
        } else {
            header("Location: " . URL);
        }
    }
}
