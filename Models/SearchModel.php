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
        $req = "SELECT topics.* FROM topics
        WHERE topicTitle LIKE :searchString
        GROUP BY topics.topicID";
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
