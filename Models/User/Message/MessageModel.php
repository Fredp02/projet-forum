<?php

namespace Models\User\Message;

use Exception;
use Models\DbConnect;
use Models\User\Message\Message;

class MessageModel extends DbConnect
{
    public function createMessage(Message $message)
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
}
