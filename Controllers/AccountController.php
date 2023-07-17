<?php

namespace Controllers;

class AccountController extends MainController
{
    // private $usersModel;

    public function __construct()
    {
        // $this->usersModel = new UsersModel();
    }

    public function account()
    {
        if (!empty($_GET['page'])) {
            $url = explode("/", filter_var($_GET['page'], FILTER_SANITIZE_URL));
        }
    }
}
