<?php

namespace Controllers;

use Entities\Messages;
use Models\TopicsModel;
use Models\MessagesModel;
use Models\CategorysModel;
use Controllers\MainController;
use Controllers\Services\Toolbox;
use Controllers\Services\Securite;

class TopicsController extends MainController
{

    private $categorysModel;
    private $topicsModel;
    private $messagesModel;
    private $message;

    public function __construct()
    {
        $this->categorysModel = new CategorysModel();
        $this->topicsModel = new TopicsModel();
        $this->messagesModel = new MessagesModel();
        $this->message = new Messages;
    }

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
                'categoryParentID' => $infosCategory->CategoryParentID

            ];


            $this->render($data_page);
        } else {
            header("Location:index.php");
        }
    }

    public function thread($threadID)
    {
        $infosTopic = $this->topicsModel->getTopicInfos($threadID);
        //si $topicID est un id existant et que la requête à renvoyer un résultat
        if ($infosTopic) {
            $messagesTopics = $this->messagesModel->getMessagesByTopic($threadID);

            // dd($infosTopic);
            $data_page = [
                "pageDescription" => "Sujet : " . $infosTopic->topicTitle . " du site Guitare-forum",
                "pageTitle" => $infosTopic->topicTitle . " | Guitare-forum",
                "view" => "../Views/topics/viewThread.php",
                "css" => "./style/topicStyle.css",
                "script" => "./js/responseTopic.js",
                "template" => "../Views/common/template.php",
                //editor quill
                "quillSnowCSS" => "//cdn.quilljs.com/1.3.6/quill.snow.css",
                "quillEmojiCSS" => "./quill/dist/quill-emoji.css",
                "quillJS" => "//cdn.quilljs.com/1.3.6/quill.js",
                "quillEmojiJS" => "./quill/dist/quill-emoji.js",
                "quillImageJS" => "./quill/dist/quill.imageUploader.js",
                "quillImageCSS" => "./quill/dist/quill.imageUploader.css",
                //----------
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

    public function uploadImage()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0 && !empty($_POST['topicID'])) {
                $datasImage = $_FILES['image'];
                $topicID = htmlspecialchars($_POST['topicID']);
                //je crée un tableau avec les types mime autorisés
                $typeMime = [
                    "jpg" => "image/jpg",
                    "jpeg" => "image/jpeg",
                    "gif" => "image/gif",
                    "png" => "image/png"
                ];


                //on verifie le type mime du fichier
                if (!in_array($datasImage['type'], $typeMime)) {
                    Toolbox::dataJson(false, "Le fichier n'est pas une image valide. Extensions autorisées : png, gif ou jpeg(jpg)", $topicID);
                    exit;
                }

                //on vérifie sa taille
                if ($datasImage['size'] > 307200) {
                    Toolbox::dataJson(false, "Le poids de l'image doit être inférieure à 300ko");
                    exit;
                }


                $filePath = './images/topics/' . $topicID;
                // $filePath = 'images/topics/' . $topicID;
                //Si ce dossier n'existe pas, il faut le créer
                if (!file_exists($filePath)) {
                    mkdir($filePath, 0777, true);
                }

                // $datasImage['type'] = image/xxx
                // $array = explode("/", $datasImage['type'])[1];
                $extension = explode("/", $datasImage['type'])[1];
                $imageRename = uniqid($topicID, true) . '.' . $extension;

                //on déplace l'image des "temporaire" dans le dossier du user
                $moveImage = move_uploaded_file($datasImage['tmp_name'], $filePath . '/' . $imageRename);
                if ($moveImage) {

                    $imageURL = $filePath . '/' . $imageRename;
                    // $imageURL = './' . $filePath . '/' . $imageRename;
                    $dataImage = [
                        'url' => $imageURL
                    ];
                    Toolbox::dataJson(true, "Image enregistrée avec succès", $dataImage);
                    exit;
                } else {
                    Toolbox::dataJson(false, "Problème rencontré lors de l'enregistrement");
                    exit;
                }
            } else {
                Toolbox::dataJson(false, "Erreur d'upload d'image");
                exit;
            }
        } else {
            header("Location:index.php");
            exit;
        }
    }

    public function validation()
    {

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!empty($_POST['tokenCSRF']) && hash_equals($_SESSION['tokenCSRF'], $_POST['tokenCSRF'])) {
                if (Securite::isConnected()) {
                    if (!empty($_POST['inputResponse']) && !empty($_POST['topicID'])) {

                        /**
                         * !on fait la même vérif ici que celle de JS pour les réponses vides :
                         * Voir explication détaillée dans le cas particulier du fichier responseTopics
                         */

                        $contenuDeVerification = preg_replace_callback('/<[^>]*>/', function ($match) {
                            return str_contains($match[0], 'img')  ? $match[0] : '';
                        }, $_POST['topicID']);
                        if ($contenuDeVerification) {
                            // $escapedResponse = htmlspecialchars($_POST['inputResponse']);


                            $topicID = htmlspecialchars($_POST['topicID']);
                            $userID = $_SESSION['profil']['userID'];
                            if (is_numeric($topicID)) {
                                //On nettoie l'html
                                $clean_html = Securite::htmlPurifier($_POST['inputResponse']);

                                //j'initialise les setters :
                                // $this->message->setMessageText($escapedResponse);
                                $this->message->setMessageText($clean_html);
                                $this->message->setUserID($userID);
                                $this->message->setTopicID($topicID);

                                //je fais appel à mon messageModel pour enregistrer les données en lui injectant "$this->message" qui correspond à l'instance de "new Message()" :
                                if ($this->messagesModel->createMessage($this->message)) {


                                    $data = [
                                        'reponseTopic' => $clean_html,
                                        'topicID' => $topicID,
                                        'dataUser' => $_SESSION['profil'],
                                    ];
                                    $_SESSION['profil']['messagesCount']++;
                                    //et on envoie la réponse en json
                                    Toolbox::dataJson(true, "données reçues, ok !", $data);
                                    exit;
                                } else {
                                    Toolbox::dataJson(false, "Une erreur s'est produite");
                                    exit;
                                }
                            } else {
                                Toolbox::dataJson(false, "Une erreur s'est produite");
                                exit;
                            }
                        } else {
                            Toolbox::dataJson(false, "PHP : Veuillez entrer du contenu avant de poster votre réponse", $_POST['inputResponse']);
                            exit;
                        }
                    } else {
                        Toolbox::dataJson(false, "Erreur transmission POST inputResponse");
                        exit;
                    }
                } else {
                    Toolbox::dataJson(false, "noConnected");
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
            header("Location:index.php");
            exit;
        }
    }
}
