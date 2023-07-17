<?php

namespace Controllers;

class LogoutController extends MainController
{
    public function logout()
    {
        unset($_SESSION['profil']);
        unset($_SESSION['tokenCSRF']);
        session_destroy();
        header("Location: " . URL . "home");
        exit;
    }
}
