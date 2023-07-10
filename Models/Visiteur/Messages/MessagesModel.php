<?php

namespace Models\Visiteur\Messages;

use Exception;
use Models\DbConnect;

class MessagesModel extends DbConnect
{
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
