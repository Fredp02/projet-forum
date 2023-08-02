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

class MessageController extends MainController
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
     * Valide et enregistre le message
     *
     * @return void
     */
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
                            return str_contains($match[0], '<img src="data:image/')  ? $match[0] : '';
                        }, $_POST['inputResponse']);
                        if ($contenuDeVerification) {

                            // et on procède aux vérification du fichier : 
                            preg_match_all('/src="data:([^;]+);base64,([^"]+)"/', $_POST['topicID'], $matches);
                            $mimeTypes = $matches[1];
                            $TableauBase64 = $matches[2];

                            //tableau type mime autorisés
                            $TypeMimeAuthorized = [
                                "jpg" => "image/jpg",
                                "jpeg" => "image/jpeg",
                                "gif" => "image/gif",
                                "png" => "image/png"
                            ];
                            foreach ($TableauBase64 as $index => $base64Chaine) {
                                $mimeType = $mimeTypes[$index];
                                if (!in_array($mimeType, $TypeMimeAuthorized)) {
                                    Toolbox::dataJson(false, "Le fichier n'est pas une image valide. Extensions autorisées : png, gif ou jpeg(jpg)");
                                    exit;
                                }

                                $base64ChaineDecode = base64_decode($base64Chaine);
                                $tailleImage = strlen($base64ChaineDecode);
                                if ($tailleImage > 307200) {
                                    Toolbox::dataJson(false, "Le poids de l'image doit être inférieure à 300ko");
                                    exit;
                                }
                            }

                            //si type et poids ok, le code continu ...

                            $topicID = htmlspecialchars($_POST['topicID']);
                            $userID = $_SESSION['profil']['userID'];
                            if (is_numeric($topicID)) {
                                //On nettoie l'html
                                $clean_html = Securite::htmlPurifier($_POST['inputResponse']);

                                //j'initialise les setters :
                                $this->message->setMessageText($clean_html);
                                $this->message->setUserID($userID);
                                $this->message->setTopicID($topicID);

                                //je fais appel à mon messageModel pour enregistrer les données en lui injectant "$this->message" qui correspond à l'instance de "new Message()" :
                                if ($this->messagesModel->createMessage($this->message)) {

                                    $categoryID = $this->topicsModel->getTopicInfos($topicID)->categoryID;
                                    $data = [
                                        'reponseTopic' => $clean_html,
                                        'topicID' => $topicID,
                                        'categoryID' => $categoryID,
                                        'dataUser' => $_SESSION['profil'],
                                    ];
                                    $_SESSION['profil']['messagesCount']++;

                                    if (isset($_POST['titleTopic'])) {
                                        Toolbox::ajouterMessageAlerte('Topic créé avec succès', 'vert');
                                    }
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
                            Toolbox::dataJson(false, "PHP : Veuillez entrer un contenu valide avant de poster votre réponse", $_POST['inputResponse']);
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


    public function createEditMessageView($messageID)
    {
        /**
         * Quand je clique sur le bouton éditer, c'est un lien, ., Je suis redirifer vers une méthode avec en paramètre get un message ID
         * Je va
         */
        if (Securite::isConnected()) {
            $data_page = [
                "pageDescription" => "Page d'édition d'un message' sur Guitare-forum",
                "pageTitle" => "Modifier son message | Guitare-forum",
                "view" => "../Views/topics/viewCreateTopic_editMessage.php",
                "css" => "./style/createTopicStyle.css",
                "script" => "./js/createTopic.js",
                "template" => "../Views/common/template.php",
                'title_a' => "Modifier son message dans : ",
                'title_b' => "nom du topic",
                'action' => "createTopic",
                'textAction' => "Créer",
                //editor quill
                "quillSnowCSS" => "//cdn.quilljs.com/1.3.6/quill.snow.css",
                "quillEmojiCSS" => "./quill/dist/quill-emoji.css",
                "quillJS" => "//cdn.quilljs.com/1.3.6/quill.js",
                "quillEmojiJS" => "./quill/dist/quill-emoji.js",
                "quillImageJS" => "./quill/dist/quill.imageUploader.js",
                "quillImageCSS" => "./quill/dist/quill.imageUploader.css",
                //----------
                // "infoCategory" => $infoCategory

            ];
            $this->render($data_page);
        } else {
            header("Location:index.php?controller=login");
            exit;
        }
    }
}
