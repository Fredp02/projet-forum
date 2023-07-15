<?php

namespace Models;

use Exception;
use Core\DbConnect;

class TopicsModel extends DbConnect
{
    public function getListTopicsByCat($categoryID)
    {

        /**
         * *je souhaite obtenir une liste de chaque topics avec
         * Toutes les colonnes du topics en fonction de "categoryID"
         * Je souhaite ajouter le "pseudo" du user associé à la création de ce topic, ainsi que la date du message le plus récent enregistré en base de donnée en fonction du topic, ainsi que le pseudo du user à l'origine de cette réponse la plus récente.
         */

        $req = "SELECT 
        t.*,
        u1.pseudo AS topicCreator,
        COUNT(m.messageID) AS totalMessages,
        MAX(m.messageDate) AS latestMessageDate,
        (SELECT u2.pseudo FROM messages m2 JOIN users u2 ON m2.userID = u2.userID WHERE m2.topicID = t.topicID ORDER BY m2.messageDate DESC LIMIT 1) AS latestMessageUser
        FROM topics t
        JOIN users u1 ON t.userID = u1.userID
        LEFT JOIN messages m ON t.topicID = m.topicID
        WHERE t.categoryID = $categoryID
        GROUP BY t.topicID;
                
        
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

    public function getTopicInfos($topicID)
    {
        // Je souhaite obtenir toutes les colonnes du topic, ainsi que les infos de la catégorie associée à ce topic
        $req = "SELECT topics.*,  categorys.categoryName, categorys.categoryID, categorys.categorySlug
        FROM topics
        JOIN categorys ON topics.categoryID = categorys.categoryID
        WHERE topics.topicID = $topicID
        ";

        $sql = $this->getBdd()->prepare($req);
        try {
            $sql->execute();
            $resultat = $sql->fetch();
            $sql->closeCursor();
            return $resultat;
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }
    // public function getMessagesByTopic($topicID)
    // {
    //     // Je souhaite obtenir la liste de tout les messages en fonction de topicID, ainsi que les informations relative à l'utilisateur à l'origine de chaque message (id, pseudo, avatar, ville)

    //     $req = "SELECT messages.*, users.userID, users.pseudo, users.avatar, users.guitare, 
    //     (SELECT COUNT(*) FROM messages m2 WHERE m2.userID = users.userID) AS totalUserMessages
    //     FROM messages
    //     JOIN users ON messages.userID = users.userID
    //     WHERE messages.topicID = $topicID
    //     ORDER BY messages.messageDate ASC
    //     ";

    //     $sql = $this->getBdd()->prepare($req);
    //     try {
    //         $sql->execute();
    //         $resultat = $sql->fetchAll();
    //         $sql->closeCursor();
    //         return $resultat;
    //     } catch (Exception $e) {
    //         die('Erreur : ' . $e->getMessage());
    //     }
    // }
}