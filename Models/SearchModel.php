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


    public function findByTitle($searchString)
    {
        $req = "SELECT topics.*, messages.* FROM topics
        JOIN messages ON messages.topicID = topics.topicID
        WHERE topics.topicTitle LIKE :searchString OR messages.messageText LIKE :searchString";

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
