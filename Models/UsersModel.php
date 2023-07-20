<?php

namespace Models;

use Entities\Users;
use PDO;
use Exception;

use Core\DbConnect;


// require 'vendor/autoload.php';
// require_once 'Models/DbConnect.php';

class UsersModel extends DbConnect
{


    public function lastInsertId()
    {
        return $this->getBdd()->lastInsertId();
    }


    public function getUserById($userID)
    {
        $req = "SELECT * FROM users WHERE userID = :userID";
        $sql = $this->getBdd()->prepare($req);
        $sql->bindValue(":userID", $userID);
        try {
            $sql->execute();
            $resultat = $sql->fetch();
            $sql->closeCursor();
            return $resultat;
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }
    public function getUserByEmail($email)
    {
        $req = "SELECT * FROM users WHERE email = :email";
        $sql = $this->getBdd()->prepare($req);
        $sql->bindValue(":email", $email);
        try {
            $sql->execute();
            $resultat = $sql->fetch();
            $sql->closeCursor();
            return $resultat;
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }
    public function getUserByPseudo($pseudo)
    {
        $req = "SELECT * FROM users WHERE pseudo = :pseudo";
        $sql = $this->getBdd()->prepare($req);
        $sql->bindValue(":pseudo", $pseudo);
        try {
            $sql->execute();
            $resultat = $sql->fetch();
            $sql->closeCursor();
            return $resultat;
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }
    public function getUserinfo($pseudo)
    {
        //On selectionne toutes les colonnes de la table user, la colonne "roleName" de la table roles, le nombre de lignes dans "messages" nommé comme "messageCount"
        // une jointure standard avec "role" : on souhaite une correspondance du roleID de la table "roles" avec le roleID de la table "Users"
        // et un jointure externe avec message : on souhaite une correspondance du userID de la table "messages" avec "userID de la table "users", même si il ne trouve aucune correspondances dans le cas ou un userID n'est pas présent dans la table "messages". Dans ce cas, la colonne "messageCount" sera donc de "0". 
        $req = "SELECT users.*, roles.roleName, COUNT(messages.messageID) AS messagesCount
        FROM users 
        JOIN roles ON users.roleID = roles.roleID 
        LEFT JOIN messages ON users.userID = messages.userID
        WHERE users.pseudo = :pseudo
        GROUP BY users.userID
        ";
        // ajout de la clause "group by" car le mode "only_full_group_by" est configurer sur le serveur de Laragon.
        $sql = $this->getBdd()->prepare($req);
        $sql->bindValue(":pseudo", $pseudo);
        try {
            $sql->execute();
            $resultat = $sql->fetch();
            $sql->closeCursor();
            return $resultat;
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }
    public function inscription(Users $user)
    {
        $pseudo = $user->getPseudo();
        $email = $user->getEmail();
        $passwordhash = $user->getPassword();
        $guitare = $user->getGuitare();
        $ville = $user->getville();
        $emploi = $user->getEmploi();
        $avatar = $user->getAvatar();
        $date = $user->getUserDate();
        $req = "INSERT INTO users (pseudo, email, userDate, password, guitare, ville, emploi, avatar) VALUES (:pseudo, :email, :userDate, :password, :guitare, :ville, :emploi, :avatar)";
        $sql = $this->getBdd()->prepare($req);
        $sql->bindValue(":pseudo", $pseudo);
        $sql->bindValue(":email", $email);
        $sql->bindValue(":userDate", $date);
        $sql->bindValue(":password", $passwordhash);
        $sql->bindValue(":guitare", $guitare);
        $sql->bindValue(":ville", $ville);
        $sql->bindValue(":emploi", $emploi);
        $sql->bindValue(":avatar", $avatar);
        try {
            $sql->execute();
            $resultat = ($sql->rowCount() > 0);
            $sql->closeCursor();
            return $resultat;
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }
    public function accountActivation(Users $user)
    {
        $userId = $user->getUserId();
        $req = "UPDATE users SET isValid = 1 WHERE userID = :userId";
        $sql = $this->getBdd()->prepare($req);
        $sql->bindValue(":userId", $userId);
        try {
            $sql->execute();
            $resultat = ($sql->rowCount() > 0);
            $sql->closeCursor();
            return $resultat;
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }

    public function modifAvatarProfil(Users $user)
    {
        $userId = $user->getUserId();
        $avatar = $user->getAvatar();
        $req = "UPDATE users set avatar = :avatar WHERE userID = :userId";
        $sql = $this->getBdd()->prepare($req);
        $sql->bindValue(":userId", $userId, PDO::PARAM_INT);
        $sql->bindValue(":avatar", $avatar);
        try {
            $sql->execute();
            $resultat = ($sql->rowCount() > 0);
            $sql->closeCursor();
            return $resultat;
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }
    public function UpdateAboutUser(Users $user)
    {
        $userId = $user->getUserId();
        $userGuitare = $user->getGuitare();
        $userEmploi = $user->getEmploi();
        $userVille = $user->getVille();
        $req = "UPDATE users set guitare = :guitare, emploi = :emploi, ville = :ville WHERE userID = :userId";
        $sql = $this->getBdd()->prepare($req);
        $sql->bindValue(":userId", $userId, PDO::PARAM_INT);
        $sql->bindValue(":guitare", $userGuitare);
        $sql->bindValue(":emploi", $userEmploi);
        $sql->bindValue(":ville", $userVille);
        try {
            $sql->execute();
            if ($sql->rowCount() > 0) {
                //! Des lignes ont été affectées, donc les données ont été mises à jour
                $resultat = 2; // Modification effectuée
            } else {
                //! Aucune ligne n'a été affectée, donc les données n'ont pas changées mais aucunes erreur n'a été detectées par le "catch"
                $resultat = 1; // Valeurs inchangées
            }
            $sql->closeCursor();
            return $resultat;
        } catch (Exception $e) {
            //! Erreur detectées
            return 0; // Problème lors de la mise à jour des informations
        }
    }

    public function updatePassword(Users $user)
    {
        $userId = $user->getUserId();
        $userPassword = $user->getPassword();
        $req = "UPDATE users set password = :password WHERE userID = :userId";
        $sql = $this->getBdd()->prepare($req);
        $sql->bindValue(":userId", $userId, PDO::PARAM_INT);
        $sql->bindValue(":password", $userPassword);
        try {
            $sql->execute();
            $resultat = ($sql->rowCount() > 0);
            $sql->closeCursor();
            return $resultat;
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }
    public function editEmailUser(Users $user)
    {
        $userId = $user->getUserId();
        $nouveauEmail = $user->getEmail();
        $req = "UPDATE users set email = :email WHERE userID = :userId";
        $sql = $this->getBdd()->prepare($req);
        $sql->bindValue(":userId", $userId, PDO::PARAM_INT);
        $sql->bindValue(":email", $nouveauEmail);
        try {
            $sql->execute();
            $resultat = ($sql->rowCount() > 0);
            $sql->closeCursor();
            return $resultat;
        } catch (Exception $e) {
            die('Erreur : ' . $e->getMessage());
        }
    }
    public function deleteAccount($userId)
    {

        $req = "DELETE FROM users WHERE userID = :userId";
        $sql = $this->getBdd()->prepare($req);
        $sql->bindValue(":userId", $userId, PDO::PARAM_INT);
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
