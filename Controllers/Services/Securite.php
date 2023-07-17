<?php


namespace Controllers\Services;

use Controllers\Services\JWTService\JWTService;


class Securite
{
    // public const COOKIE_NAME = "timers";

    // public static function secureHTML($chaine)
    // {
    //     return htmlentities($chaine);
    // }
    public static function isConnected()
    {
        // return (isset($_SESSION['profil']));
        return (isset($_SESSION['profil']));
    }

    public static function tokenCSRF()
    {
        /**
         * !La probabilité qu’un pirate informatique puisse prédire la valeur d’un token CSRF généré en utilisant bin2hex(random_bytes(32)) est extrêmement faible. Étant donné que random_bytes génère des nombres aléatoires cryptographiquement sécurisés et que 32 octets (256 bits) sont utilisés pour le token, l’espace des clés est de 2^256, soit environ 10^77. Cela signifie qu’un pirate informatique aurait une chance sur 10^77 de deviner correctement la valeur du token en une seule tentative.
         * 
         * !Pour mettre cela en perspective, supposons qu’un pirate informatique dispose d’un ordinateur capable de vérifier un milliard (10^9) de tokens par seconde. Même à cette vitesse, il faudrait au pirate informatique environ 10^68 secondes, soit environ 3 x 10^60 années, pour parcourir tout l’espace des clés et deviner la valeur du token avec certitude. Cela dépasse de loin l’âge estimé de l’univers, qui est d’environ 14 milliards d’années (1,4 x 10^10 années).

         *!En résumé, la probabilité qu’un pirate informatique puisse prédire la valeur d’un token CSRF généré en utilisant bin2hex(random_bytes(32)) est si faible qu’elle peut être considérée comme pratiquement nulle.
         */
        $_SESSION['tokenCSRF'] = bin2hex(random_bytes(32));
    }
    // public static function estUtilisateur()
    // {
    //     return ($_SESSION['profil']['role'] === "utilisateur");
    // }
    // public static function estAdministrateur()
    // {
    //     return ($_SESSION['profil']['role'] === "administrateur");
    // }
    // public static function genererCookieConnexion()
    // {
    //     $ticket = session_id() . microtime() . rand(0, 999999);
    //     $ticket = hash("sha512", $ticket);
    //     setcookie(self::COOKIE_NAME, $ticket, time() + (60 * 20));
    //     $_SESSION['profil'][self::COOKIE_NAME] = $ticket;
    // }
    // public static function checkCookieConnexion()
    // {
    //     return $_COOKIE[self::COOKIE_NAME] === $_SESSION['profil'][self::COOKIE_NAME];
    // }

    public static function verifCSRF()
    {
        return ($_SERVER['REQUEST_METHOD'] === 'POST'
            && !empty($_POST['tokenCSRF'])
            && hash_equals($_SESSION['tokenCSRF'], $_POST['tokenCSRF'])
        );
    }



    //! version d'origine
    // public static function createTokenJWT($userId, $pseudo = null, $email = null)
    // {
    //     $header = [
    //         'typ' => 'JWT',
    //         'alg' => 'HS256'
    //     ];
    //     $payload = [
    //         'userID' => $userId,
    //         'pseudo' => $pseudo,
    //         'email' => $email
    //     ];
    //     $jwt = new JWTService();

    //     //Si "email" n'est pas null, c'est donc un token destiné à l'envoi d'un mail. Dans ce cas précis le token aura une durée de validité de 3h. Si "email" reste null, ce sera un token de connexion d'une durée de validité de 86400s -> 24h.
    //     $validity = $email ? 10800 : 86400;
    //     return $jwt->generate($header, $payload, SECRET, $validity);
    // }
    public static function createTokenJWT($userId, $pseudo, $email)
    {
        $header = [
            'typ' => 'JWT',
            'alg' => 'HS256'
        ];
        $payload = [
            'userID' => $userId,
            'pseudo' => $pseudo,
            'email' => $email
        ];
        $jwt = new JWTService();

        //Si "email" n'est pas null, c'est donc un token destiné à l'envoi d'un mail. Dans ce cas précis le token aura une durée de validité de 3h. Si "email" reste null, ce sera un token de connexion d'une durée de validité de 86400s -> 24h.
        $validity = 10800;
        return $jwt->generate($header, $payload, SECRET, $validity);
    }
}
