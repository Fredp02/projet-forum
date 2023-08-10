<?php

namespace Models;

use PDO;
use Exception;
use Core\DbConnect;


class SearchModel extends DbConnect
{
    public function __construct()
    {
    }

    //recherche basique
    public function defaultSearch($searchString)
    {

        $req = "SELECT topics.*, messages.*, COUNT(messages.messageID) AS totalMessages, users.userID, users.pseudo
        FROM topics
        JOIN messages ON messages.topicID = topics.topicID
        JOIN users ON messages.userID = users.userID
        WHERE topics.topicTitle LIKE :searchString OR messages.messageText LIKE :searchString
        GROUP BY topics.topicID, messages.messageID
        ORDER BY messages.messageDate DESC        
        ";


        $sql = $this->getBdd()->prepare($req);

        try {
            $sql->bindValue(":searchString", $searchString);
            $sql->execute();
            $resultat = $sql->fetchAll();
            $sql->closeCursor();
            return $resultat;
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }
}
