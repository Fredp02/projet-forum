<?php

namespace Models\User;

class User
{
    private $userId;
    private $pseudo;
    private $email;
    private $userDate;
    private $password;
    private $ville;
    private $emploi;
    private $guitare;
    private $avatar;
    private $isValid;
    private $idRole;

    /**
     * Constructeur de la classe User
     * @param int $user_id L'identifiant de l'utilisateur
     * @param string $pseudo Le pseudo de l'utilisateur
     * @param string $created_at La date de création du compte de l'utilisateur
     */
    public function __construct($pseudo, $userDate, $userId = null)
    { // Constructeur avec trois paramètres

        $this->pseudo = $pseudo; // Initialisation de la propriété pseudo
        $this->userDate = $userDate; // Initialisation de la propriété created_at
        $this->userId = $userId; // Initialisation de la propriété user_id
    }

    /**
     * Get the value of userId
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set the value of userId
     *
     * @return  self
     */
    // public function setUserId($userId)
    // {
    //     $this->userId = $userId;

    //     return $this;
    // }

    /**
     * Get the value of pseudo
     */
    public function getPseudo()
    {
        return $this->pseudo;
    }

    /**
     * Set the value of pseudo
     *
     * @return  self
     */
    // public function setPseudo($pseudo)
    // {
    //     $this->pseudo = $pseudo;

    //     return $this;
    // }

    /**
     * Get the value of email
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set the value of email
     *
     * @return  self
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get the value of userDate
     */
    public function getUserDate()
    {
        return $this->userDate;
    }

    /**
     * Set the value of userDate
     *
     * @return  self
     */
    // public function setuserDate($userDate)
    // {
    //     $this->userDate = $userDate;

    //     return $this;
    // }

    /**
     * Get the value of password
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set the value of password
     *
     * @return  self
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }
    /**
     * Get the value of image
     */
    public function getAvatar()
    {
        return $this->avatar;
    }

    /**
     * Set the value of image
     *
     * @return  self
     */
    public function setAvatar($avatar)
    {
        $this->avatar = $avatar;

        return $this;
    }
    /**
     * Get the value of isValid
     */
    public function getisValid()
    {
        return $this->isValid;
    }

    /**
     * Set the value of isValid
     *
     * @return  self
     */
    public function setisValid($isValid)
    {
        $this->isValid = $isValid;

        return $this;
    }

    /**
     * Get the value of idRole
     */
    public function getIdRole()
    {
        return $this->idRole;
    }

    /**
     * Set the value of idRole
     *
     * @return  self
     */
    public function setIdRole($idRole)
    {
        $this->idRole = $idRole;

        return $this;
    }

    /**
     * Get the value of ville
     */
    public function getVille()
    {
        return $this->ville;
    }

    /**
     * Set the value of ville
     *
     * @return  self
     */
    public function setVille($ville)
    {
        $this->ville = $ville;

        return $this;
    }

    /**
     * Get the value of emploi
     */
    public function getEmploi()
    {
        return $this->emploi;
    }

    /**
     * Set the value of emploi
     *
     * @return  self
     */
    public function setEmploi($emploi)
    {
        $this->emploi = $emploi;

        return $this;
    }



    /**
     * Get the value of guitare
     */
    public function getGuitare()
    {
        return $this->guitare;
    }

    /**
     * Set the value of guitare
     *
     * @return  self
     */
    public function setGuitare($guitare)
    {
        $this->guitare = $guitare;

        return $this;
    }
}