<?php

namespace Controllers\Traits;

use Controllers\Services\Toolbox;
use Controllers\Services\Securite;

trait VerifPostTrait
{
    public function VerifPostTrait()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (Securite::verifCSRF()) {
                if (Securite::isConnected()) {
                    return true;
                } else {
                    Toolbox::dataJson(false, "noConnected");
                    exit;
                }
            } else {
                Toolbox::ajouterMessageAlerte("Session expirée, veuillez recommencer", 'rouge');

                //Les "unset" peuvent être utiles pour des raisons de sécurité, car cela empêche toute utilisation ultérieure de ces données de session potentiellement compromises. De plus, cela garantit que l’utilisateur doit se reconnecter et obtenir un nouveau jeton CSRF avant de poursuivre,
                unset($_SESSION['profil']);
                unset($_SESSION['tokenCSRF']);
                Toolbox::dataJson(false, "expired token");
                exit;
            }
        } else {
            header("Location:index.php");
            exit;
        }
        return false;
    }
}
