<?php

namespace Controllers\Services;

// use DateTime;
use DateTime;
use DateTimeZone;
use DateTimeImmutable;
// use PHPMailer\PHPMailer\PHPMailer;
use IntlDateFormatter;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use Wamania\Snowball\StemmerManager;
use Controllers\Services\HtmlTemplateMail\HtmlTemplateMail;

require '../vendor/autoload.php';
include "../Controllers/Services/stopword.php";


class Toolbox
{
    public static function dataJson($bool, $message = null, $data = null): void
    {
        $Data = [
            'boolean' => $bool,
            'message' => $message,
            'data' => $data
        ];
        print_r(json_encode($Data));
    }


    public static function ajouterMessageAlerte($message, $couleur): void
    {
        $_SESSION['alert'] = [
            "message" => $message,
            "couleur" => $couleur
        ];
    }
    public static function convertDate($dateTime, $pattern = "EEEE dd MMMM yyyy à HH'h'mm"): string
    {
        //d MMMM Y
        $formatter = new IntlDateFormatter('fr_FR', IntlDateFormatter::SHORT, IntlDateFormatter::SHORT);
        $formatter->setPattern($pattern);
        $date = DateTime::createFromFormat('Y-m-d H:i:s', $dateTime);
        $formattedDate = $formatter->format($date);
        return ucfirst($formattedDate);
    }

    public static function creerDateActuelle(): string
    {
        // Obtient la date et l'heure actuelles
        $dateActuelle = new DateTime();

        // Crée un formateur de date avec la localisation en français
        $formateur = new IntlDateFormatter('fr_FR', IntlDateFormatter::FULL, IntlDateFormatter::FULL);
        $formateur->setPattern("EEEE dd MMMM yyyy HH'h'mm");

        // Formatte la date actuelle selon le modèle défini
        $dateFormatee = $formateur->format($dateActuelle);

        // Affiche la date formatée
        return ucfirst($dateFormatee);
    }

    public static function createEmailContent($template, $pseudo, $route): array|false|string
    {
        $contentMail = file_get_contents($template);
        $contentMail = str_replace('{name}', $pseudo, $contentMail);
        $contentMail = str_replace('{activation_link}', $route, $contentMail);
        return $contentMail;

        //on peut imaginer beaucoup plus de chose à remplacer, dans ce cas ce code est une meilleure solution :
        // $contentMail = file_get_contents($template);
        // $replacements = array('{name}' => $pseudo, '{activation_link}' => $route);
        // return str_replace(array_keys($replacements), array_values($replacements), $contentMail);
    }

    public static function sendmail($email, $sujet, $contentMail)
    {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'sandbox.smtp.mailtrap.io';
            $mail->SMTPAuth = true;
            $mail->Port = 2525;
            $mail->Username = '1d3f6c38385955';
            $mail->Password = '8d28441fd8e5fb';

            $mail->setFrom('contact@guitareforum.fr', 'Guitareforum.');
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';
            $mail->Subject = $sujet;
            $mail->Body = $contentMail;
            return $mail->send();
        } catch (Exception $e) {
            echo "Une erreur est survenue: {$mail->ErrorInfo}";
        }
    }

    public static function cleanSearch($string): string
    {
        //Le remplacement des espaces par des underscores, puis des underscores par des espaces, peut sembler inutile à première vue. Cependant, cette étape est importante pour la suppression des caractères non autorisés et pour la gestion des espaces consécutifs.

        //Après avoir remplacé les espaces par des underscores, la méthode utilise `preg_replace` pour supprimer tous les caractères qui ne sont pas des lettres minuscules, des chiffres ou des underscores. Si cette étape était effectuée directement sur la chaîne avec des espaces, les espaces seraient également supprimés. En remplaçant d'abord les espaces par des underscores, on s'assure que les espaces ne sont pas supprimés lors de cette étape.

        //Ensuite, la méthode utilise `preg_replace` pour remplacer les underscores consécutifs par un seul underscore. Si cette étape était effectuée directement sur la chaîne avec des espaces, les espaces consécutifs seraient également remplacés par un seul espace. En remplaçant d'abord les espaces par des underscores, on s'assure que les espaces consécutifs ne sont pas fusionnés lors de cette étape.

        //Enfin, les underscores sont remplacés par des espaces pour restaurer les espaces originaux. Cette séquence d'étapes permet de conserver les espaces tout en supprimant les caractères non autorisés et en gérant les espaces consécutifs.

        $string = strtolower($string);
        $string = trim($string);
        $string = str_replace(' ', '_', $string);
        $string = preg_replace('/[^a-z0-9_àáâäèéêëìíîïòóôöùúûü]/', '', $string);
        $string = preg_replace('/_+/', '_', $string);
        $string = str_replace('_', ' ', $string);

        foreach (STOPWORD as $stopWord) {
            $string = str_replace($stopWord, ' ', $string);
        }

        //! On utilise un stemmer :
        // Cela permet de réduire les mots à leur racine ou à leur forme de base en supprimant les suffixes et les préfixes. Le stemming est une technique couramment utilisée dans les moteurs de recherche pour normaliser les termes de recherche et améliorer la pertinence des résultats renvoyés.
        //*https://www.elastic.co/fr/blog/leviers-elasticsearch-pour-le-traitement-des-specificites-linguistiques
        $manager = new StemmerManager();
        $stemmer = $manager->stem($string, 'fr');

        return '%' . str_replace(' ', '%', $stemmer) . '%';
    }







    // //! méthode qui permet de classer les entrées de l'entitée categories dans un tableau de façon hierarchique en fonction de leur categorie parentes respectives.
    // public static function buildCategoryHierarchy($results, $parentId = null, $level = 1)
    // {
    //     $hierarchy = [];
    //     foreach ($results as $row) {
    //         if ($row->CategoryParentID == $parentId) {
    //             $category = [
    //                 'categoryName' => $row->categoryName,
    //                 'subcategories' => self::buildCategoryHierarchy($results, $row->categoryID, $level + 1)
    //             ];
    //             $hierarchy[] = $category;
    //         }
    //     }
    //     return $hierarchy;
    // }
}
