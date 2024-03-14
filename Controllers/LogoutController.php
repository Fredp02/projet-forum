<?php

namespace Controllers;

class LogoutController extends MainController
{
    public function index()
    {
        unset($_SESSION['profil']);
        unset($_SESSION['tokenCSRF']);
        session_destroy();
        header("Location:index.php");
        exit;
    }
}
