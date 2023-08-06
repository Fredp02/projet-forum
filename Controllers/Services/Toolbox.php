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
use Controllers\Services\HtmlTemplateMail\HtmlTemplateMail;

require '../vendor/autoload.php';
include "../Controllers/Services/stopWord.php";


class Toolbox
{
    public static function dataJson($bool, $message = null, $data = null): void
    {
        $Data = [
            'boolean' => $bool,
            'message' => $message,
            'data' => $data
        ];
        // header('Content-Type: application/json');
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

        //on peu imaginer beaucoup plus de chose à remplacer, dans ce cas ce code est une meilleurs solution : 
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

        $string = strtolower($string);
        $string = trim($string);

        $string = str_replace(' ', '_', $string);
        $string = preg_replace('/[^a-z0-9_]/', '', $string);
        $string = preg_replace('/_+/', '_', $string);
        $string = str_replace('_', ' ', $string);

        foreach (STOPWORD as $stopWord) {
            $string = str_replace($stopWord, ' ', $string);
        }
        return '%' . str_replace(' ', '%', $string) . '%';
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
