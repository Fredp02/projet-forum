<?php

namespace Models;

use Exception;
use Core\DbConnect;
use Entities\Messages;

class MessagesModel extends DbConnect
{
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
        // Je souhaite obtenir la liste de tout les messages en fonction de topicID, ainsi que les informations relative à l'utilisateur à l'origine de chaque message (id, pseudo, avatar, ville)

        $req = "SELECT messages.*, users.userID, users.pseudo, users.avatar, users.guitare, 
        (SELECT COUNT(*) FROM messages m2 WHERE m2.userID = users.userID) AS totalUserMessages
        FROM messages
        JOIN users ON messages.userID = users.userID
        WHERE messages.topicID = $topicID
        ORDER BY messages.messageDate ASC
        ";

        $sql = $this->getBdd()->prepare($req);
        try {
            $sql->execute();
            $resultat = $sql->fetchAll();
            $sql->closeCursor();
            return $resultat;
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }
}
