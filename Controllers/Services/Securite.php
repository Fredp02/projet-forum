<?php


namespace Controllers\Services;

use Controllers\Services\JWTService\JWTService;
use DOMDocument;


class Securite
{

    public static function isConnected()
    {
        return (isset($_SESSION['profil']));
    }

    public static function tokenCSRF()
    {
        $_SESSION['tokenCSRF'] = bin2hex(random_bytes(32));
    }


    public static function verifCSRF()
    {
        return ($_SERVER['REQUEST_METHOD'] === 'POST'
            && !empty($_POST['tokenCSRF'])
            && hash_equals($_SESSION['tokenCSRF'], $_POST['tokenCSRF'])
        );
    }


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

        $validity = 10800;
        return $jwt->generate($header, $payload, SECRET, $validity);
    }

    public static function verifPassword($password, $userPassBDD)
    {
        return password_verify($password, $userPassBDD);
    }

    public static function htmlPurifier($html)
    {
        //On souhaite purifier le message :
        //Les trois prochaines lignes de code utilisent la bibliothèque HTML Purifier pour nettoyer le contenu HTML.
        // HTML Purifier est une solution de filtrage HTML qui utilise une combinaison unique de listes blanches robustes et
        // d’analyse pour garantir que non seulement les attaques XSS sont contrecarrées,
        // mais que le HTML résultant est conforme aux normes.
        //Crée un objet de configuration par défaut pour HTML Purifier.
        $config = \HTMLPurifier_Config::createDefault();

        //crée une nouvelle instance de HTML Purifier en utilisant l’objet de configuration créé précédemment.
        $purifier = new \HTMLPurifier($config);

        //utilise la méthode purify() de l’instance de HTML Purifier pour nettoyer le contenu HTML contenu dans la variable $decodedResponse.
        return $purifier->purify($html);
    }

    public static function removeEmptyTags($html)
    {

        // Créez un nouveau document DOM
        $dom = new DOMDocument;

        // Ajoutez une balise <div> autour de votre HTML pour pouvoir le charger dans le DOM
        $dom->loadHTML('<div>' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        // Obtenez la première balise <div>
        $div = $dom->getElementsByTagName('div')->item(0);

        // Supprimez tous les espaces blancs et les nouvelles lignes entre les balises <p> et </p>
        foreach ($div->getElementsByTagName('p') as $p) {
            if ($p->firstChild && $p->firstChild->nodeType === XML_TEXT_NODE && trim($p->firstChild->textContent) === '') {
                $p->removeChild($p->firstChild);
            }
        }

        // Supprimez les balises <p><br></p> et <p></p> vides au début
        while ($div->firstChild && $div->firstChild->nodeName === 'p' && ($div->firstChild->childNodes->length === 0 || ($div->firstChild->childNodes->length === 1 && $div->firstChild->firstChild->nodeName === 'br'))) {
            $div->removeChild($div->firstChild);
        }

        // Supprimez les balises <p><br></p> et <p></p> vides à la fin
        while ($div->lastChild && $div->lastChild->nodeName === 'p' && ($div->lastChild->childNodes->length === 0 || ($div->lastChild->childNodes->length === 1 && $div->lastChild->firstChild->nodeName === 'br'))) {
            $div->removeChild($div->lastChild);
        }

        // Retournez le HTML modifié sans la balise <div> extérieure
        $html = str_replace(array('<div>', '</div>'), '', $dom->saveHTML());

        return trim($html);
    }

    public static function deleteQlCursorQuill($html)
    {
        return str_replace(array('&iuml;&raquo;&iquest;', '<span class="ql-cursor"></span>'), '', $html);

    }
}
