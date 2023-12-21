<?php

namespace Models;

use Exception;
use Core\DbConnect;
use Entities\Messages;

class MessagesModel extends DbConnect
{
    public function lastInsertId()
    {
        return $this->getBdd()->lastInsertId();
    }
    public function createMessage(Messages $message)
    {
        $messageText = $message->getMessageText();
        $userID = $message->getUserID();
        $topicID = $message->getTopicID();

        $req = "INSERT INTO messages (messageText, userID, topicID) VALUES (:messageText, :userID, :topicID)";
        $sql = $this->getBdd()->prepare($req);
        $sql->bindValue(":messageText", $messageText);
        $sql->bindValue(":userID", $userID, \PDO::PARAM_INT);
        $sql->bindValue(":topicID", $topicID, \PDO::PARAM_INT);
        try {
            $sql->execute();
            $resultat = ($sql->rowCount() > 0);
            $sql->closeCursor();
            return $resultat;
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }

    public function getMessagesByTopic($topicID)
    {
        // req1 : Je souhaite obtenir la liste de tout les messages en fonction de topicID, ainsi que les informations relative à l'utilisateur à l'origine de chaque message (id, pseudo, avatar, ville)
        //on fait une sous requete qui compte le nombre de ligne de la table message (renommée en "m2") lorsque qu'il y correspondance entre userID de la table user et le userID de la table m2 (messages) ). on renomme le compte en "totalUserMessages"
        //on joint le tout avec la table user lorsque qu'il y correspondance entre userID de messages et userID de "users".
        //le tout lorsque le topicID de message est égale au $topicID en paramètre
        //et on réorgannise le tout par date du message

        // $req1 = "SELECT messages.*, users.userID, users.pseudo, users.avatar, users.guitare, 
        // (SELECT COUNT(*) FROM messages m2 WHERE m2.userID = users.userID) AS totalUserMessages
        // FROM messages
        // JOIN users ON messages.userID = users.userID
        // WHERE messages.topicID = :topicID
        // ORDER BY messages.messageDate ASC
        // ";
        $req2 = "SELECT messages.*, users.userID, users.pseudo, users.avatar, users.guitare, COUNT(m2.messageID) AS totalUserMessages
        FROM messages
        JOIN users ON messages.userID = users.userID
        JOIN messages m2 ON m2.userID = users.userID
        WHERE messages.topicID = :topicID
        GROUP BY messages.messageID
        ORDER BY messages.messageDate ASC

        ";


        $sql = $this->getBdd()->prepare($req2);
        $sql->bindValue(":topicID", $topicID);
        try {
            $sql->execute();
            $resultat = $sql->fetchAll();
            $sql->closeCursor();
            return $resultat;
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }


    /**
     * Toutes les colonnes du "message", ainsi que celles de son topic, de sa catégorie et de la catégorie parente
     *
     * @param  mixed $messageID
     * @return object
     */
    public function getInfoMessage($messageID)
    {

        $req = "SELECT messages.messageID as messageID, messages.messageText AS messageText, messages.userID AS messageUserID, messages.topicID AS messageTopicID, topics.topicTitle, topics.topicID, categorys.*, parent.categoryID AS parentID, parent.categoryName AS parentName 
        FROM messages 
        JOIN topics ON messages.topicID = topics.topicID 
        JOIN categorys ON topics.categoryID = categorys.categoryID 
        JOIN categorys parent ON categorys.categoryParentID = parent.categoryID 
        WHERE messages.messageID = :messageID;
        ";


        $sql = $this->getBdd()->prepare($req);
        $sql->bindValue(":messageID", $messageID);
        try {
            $sql->execute();
            $resultat = $sql->fetch();
            $sql->closeCursor();
            return $resultat;
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }

    public function editMessage(Messages $message)
    {
//                    var_dump($message);
//                    exit;
        $messageId = $message->getMessageID();
        $messageText = $message->getMessageText();
        $req = "UPDATE messages set messageText = :messageText WHERE messageId = :messageId";
        $sql = $this->getBdd()->prepare($req);
        $sql->bindValue(":messageId", $messageId, \PDO::PARAM_INT);
        $sql->bindValue(":messageText", $messageText);
        try {
            $sql->execute();
            $resultat = ($sql->rowCount() > 0);
            $sql->closeCursor();
            return $resultat;
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }
}
