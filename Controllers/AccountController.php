<?php

namespace Controllers;

use Models\UsersModel;

class AccountController extends MainController
{
    private $usersModel;

    public function __construct()
    {
        $this->usersModel = new UsersModel();
    }

    /**
     * !action = editAvatar OU editEmail  ...
     * account/profil -> affiche le profil
     * account/datasFormProfil (données envoyées à la page profil)
     * account/editAvatar
     * account/editEmail
     * account/editPassword
     * account/editAbout
     * account/viewDelete
     * account/validationDelete
     */
    public function account($action)
    {
        $this->$action();
    }

    private function profil()
    {
        $tokenCSRF = $_SESSION["tokenCSRF"];
        $pseudo = $_SESSION['profil']['pseudo'];
        $user = $this->usersModel->getUserinfo($pseudo);

        $userDatas = [
            'userID' => $user->userID,
            'pseudo' => $user->pseudo,
            'userDate' => $user->userDate,
            'role' => $user->roleName,
            'email' => $user->email,
            'guitare' => $user->guitare,
            'ville' => $user->ville,
            'emploi' => $user->emploi,
            'avatar' => $user->avatar
        ];

        $data_page = [
            "pageDescription" => "Page du profil",
            "pageTitle" => "Profil",
            "view" => "../Views/Utilisateur/viewProfil.php",
            "template" => "../views/common/template.php",
            "css" => "public/style/profilStyle.css",
            "script" => "public/js/profil.js",
            'tokenCSRF' => $tokenCSRF,
            "userDatas" => $userDatas
        ];
        $this->render($data_page);
    }
    private function datasFormProfil()
    {
        //code...
        dd('je rentre dans la méthode datasFormProfil()');
    }
    private function editAvatar()
    {
        //code...
        dd('je rentre dans la méthode editAvatar()');
    }
    private function editEmail()
    {
        //code...
        dd('je rentre dans la méthode editEmail()');
    }
}
