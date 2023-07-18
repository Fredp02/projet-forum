<?php

namespace Controllers;

use Entities\Messages;
use Models\UsersModel;
use Models\MessagesModel;
use Controllers\Services\Toolbox;
use Controllers\Services\Securite;

class TopicReplyController extends MainController
{

    private $message; //getter-setter de l'entité messages
    private $messageModel;

    public function __construct()
    {

        $this->messageModel = new MessagesModel;
        $this->message = new Messages;
    }

    public function topicReply($action)
    {
        $this->$action();
    }

    private function uploadImage()
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

                $filePath = 'images/topics/' . $topicID;
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
                    $imageURL = 'public/' . $filePath . '/' . $imageRename;
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
                echo 'erreur';
                exit;
            }
        } else {
            header("Location: " . URL . "home");
            exit;
        }
    }
    private function validation()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!empty($_POST['tokenCSRF']) && hash_equals($_SESSION['tokenCSRF'], $_POST['tokenCSRF'])) {
                if (Securite::isConnected()) {
                    if (!empty($_POST['inputResponse']) && !empty($_POST['topicID'])) {

                        /**
                         * !on fait la même vérif ici que celle de JS pour les réponses vides :
                         * on retire toutes les balises vide par "". Si au final la chaine est completement vide alors c'est que la soumission ne contient rien.
                         */
                        $contenuDeVerification = preg_replace('/<[^>]*>/', '', $_POST['inputResponse']);
                        if ($contenuDeVerification) {
                            $escapedResponse = htmlspecialchars($_POST['inputResponse']);


                            $topicID = htmlspecialchars($_POST['topicID']);
                            $userID = $_SESSION['profil']['userID'];
                            if (is_numeric($topicID)) {
                                //j'initialise les setters :
                                $this->message->setMessageText($escapedResponse);
                                $this->message->setUserID($userID);
                                $this->message->setTopicID($topicID);

                                //je fais appel à mon messageModel pour enregistrer les données en lui injectant "$this->message" qui correspond à l'instance de "new Message()" :
                                if ($this->messageModel->createMessage($this->message)) {
                                    $decodedResponse = html_entity_decode($escapedResponse);

                                    /**
                                     * !Les trois prochaines lignes de code utilisent la bibliothèque HTML Purifier pour nettoyer le contenu HTML. HTML Purifier est une solution de filtrage HTML qui utilise une combinaison unique de listes blanches robustes et d’analyse pour garantir que non seulement les attaques XSS sont contrecarrées, mais que le HTML résultant est conforme aux normes.
                                     * 
                                     * $config = \HTMLPurifier_Config::createDefault(); crée un objet de configuration par défaut pour HTML Purifier.
                                     * 
                                     * $purifier = new \HTMLPurifier($config); crée une nouvelle instance de HTML Purifier en utilisant l’objet de configuration créé précédemment.
                                     * 
                                     * $clean_html = $purifier->purify($decodedResponse); utilise la méthode purify() de l’instance de HTML Purifier pour nettoyer le contenu HTML contenu dans la variable $decodedResponse. Le résultat est stocké dans la variable $clean_html.
                                     */
                                    $config = \HTMLPurifier_Config::createDefault();
                                    $purifier = new \HTMLPurifier($config);
                                    $clean_html = $purifier->purify($decodedResponse);

                                    $data = [
                                        'reponseTopic' => $clean_html,
                                        'topicID' => $topicID,
                                        'dataUser' => $_SESSION['profil']
                                    ];
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
                            Toolbox::dataJson(false, "Veuillez entrer du contenu avant de poster votre réponse");
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
            header("Location: " . URL . "home");
            exit;
        }
    }
}
