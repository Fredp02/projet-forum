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

class Toolbox
{
    public static function dataJson($bool, $message = null, $data = null)
    {
        $Data = [
            'boolean' => $bool,
            'message' => $message,
            'data' => $data
        ];
        // header('Content-Type: application/json');
        print_r(json_encode($Data));
    }


    public static function ajouterMessageAlerte($message, $couleur)
    {
        $_SESSION['alert'][] = [
            "message" => $message,
            "couleur" => $couleur
        ];
    }
    public static function convertDate($dateTime, $pattern = "EEEE dd MMMM yyyy à HH'h'mm")
    {
        //d MMMM Y
        $formatter = new IntlDateFormatter('fr_FR', IntlDateFormatter::SHORT, IntlDateFormatter::SHORT);
        $formatter->setPattern($pattern);
        $date = DateTime::createFromFormat('Y-m-d H:i:s', $dateTime);
        $formattedDate = $formatter->format($date);
        return ucfirst($formattedDate);
    }

    public static function creerDateActuelle()
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
    public static function sendmail2($pseudo, $userEmail, $route, $sujet, $cheminTemplate = null)
    {
        $mail = new PHPMailer(true);
        try {
            $mail->setFrom('f.poulain@gmx.com', 'Mon site');
            $mail->addAddress($userEmail);
            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';
            $mail->Subject = 'Nouveau message du site';
            $mail->Body    = "
            <html>
            <head>
              <meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
              <title>email</title>
            </head>
            <body>
              <div style='text-align: center;'>               
                  Nouveau Message !
                    " . $sujet . "
                    " . $route . "
                    " . $pseudo . "
                    
              </div>
            </body>
          </html>
            ";
            return $mail->send();
        } catch (Exception $e) {
            echo "Une erreur est survenue: {$mail->ErrorInfo}";
        }
    }
    public static function sendMail($pseudo, $userEmail, $route, $sujet, $cheminTemplate)
    {

        // On crée une instance de la classe HtmlTemplateMail
        $mailer = new HtmlTemplateMail();
        try {
            //pour afficher les infos
            // $mailer->SMTPDebug = 2;
            $mailer->isSMTP();
            // On configure les paramètres du serveur SMTP

            /**
             * ! parfois avec le port 465, ça fonctionne pas...!
             * !La différence entre les ports 465 et 587 est la façon dont le cryptage est mis en œuvre. Le port 465 utilise un cryptage SSL/TLS implicite, c’est-à-dire que la connexion est chiffrée dès le début. Le port 587 utilise un cryptage SSL/TLS explicite, c’est-à-dire que la connexion commence en clair et passe ensuite en mode chiffré après la commande STARTTLS.
             * 
             * ! port 465 VS 587 ! Le port 587 est plus recommandé car il est plus compatible avec les clients de messagerie modernes et plus conforme aux standards actuels
             */
            //$mailer->Host = 'smtp.hostinger.com';; // L'hôte du serveur SMTP
            // $mailer->Port = 587; // Le port du serveur SMTP
            // $mailer->SMTPAuth = true; // On active l'authentification SMTP
            // $mailer->Username = 'contact@fredericpoulain.fr'; // Le nom d'utilisateur du compte SMTP
            // $mailer->Password = '********'; // Le mot de passe du compte SMTP
            $mailer->Host = 'sandbox.smtp.mailtrap.io';; // L'hôte du serveur SMTP
            $mailer->Port = 2525; // Le port du serveur SMTP
            $mailer->SMTPAuth = true; // On active l'authentification SMTP
            $mailer->Username = '1d3f6c38385955'; // Le nom d'utilisateur du compte SMTP
            $mailer->Password = '8d28441fd8e5fb'; // Le mot de passe du compte SMTP

            // On définit l'expéditeur  du mail
            $mailer->setFrom('contact@guitareforum.fr', 'Guitare Forum');
            // et le destinataire :
            $mailer->addAddress($userEmail);
            $mailer->CharSet = 'UTF-8';
            // On définit le sujet du mail
            $mailer->Subject = $sujet;

            // On définit le template HTML à utiliser
            $mailer->setTemplate($cheminTemplate);

            //On définit les variables à remplacer dans le template
            $link = $route;
            // $link = "token";
            $mailer->setValues(array(
                'name' => $pseudo,
                'activation_link' => $link
            ));

            return $mailer->send();
        } catch (Exception $e) {
            // On affiche l'erreur si une exception est levée

            echo 'Le mail n\'a pas pu être envoyé.';
            echo 'Erreur : ' . $mailer->ErrorInfo;
        }
    }
    // public static function sendMail($userEmail)
    // {

    //     // On crée une nouvelle instance de PHPMailer
    //     $mail = new PHPMailer(true); // On active les exceptions

    //     try {
    //         // header('Content-Type: text/html; charset=utf-8');

    //         // On active le mode SMTP
    //         $mail->isSMTP();

    //         // On configure les paramètres du serveur SMTP
    //         $mail->Host = 'sandbox.smtp.mailtrap.io';; // L'hôte du serveur SMTP
    //         $mail->Port = 2525; // Le port du serveur SMTP
    //         $mail->SMTPAuth = true; // On active l'authentification SMTP
    //         $mail->Username = '1d3f6c38385955'; // Le nom d'utilisateur du compte SMTP
    //         $mail->Password = '8d28441fd8e5fb'; // Le mot de passe du compte SMTP

    //         // On définit l'expéditeur et le destinataire du mail
    //         $mail->setFrom('inscription@guitareforum.com', 'GuitareForum'); // L'adresse et le nom de l'expéditeur
    //         $mail->addAddress($userEmail); // L'adresse et le nom du destinataire

    //         $mail->CharSet = 'UTF-8';

    //         // On définit le sujet et le contenu du mail
    //         $mail->Subject = '$mail->Subject  : Test de PHPMailer avec try catch';
    //         // $mail->Body    = '$mail->Body  : <p>Ceci est un mail</p> envoyé avec PHPMailer et SMTP, en utilisant un bloc try catch pour gérer les erreurs.';
    //         // $mail->AltBody = '$mail->AltBody  : Ceci est un mail envoyé avec PHPMailer et SMTP, en utilisant un bloc try catch pour gérer les erreurs.';
    //         
    //         $mail->msgHTML("<p>Bonjour, ceci est un message HTML avec une image.</p><img src=\"image.png\">");
    //         // On envoie le mail
    //         $mail->send();
    //         echo 'Le mail a été envoyé.';
    //     } catch (Exception $e) {
    //         // On affiche l'erreur si une exception est levée
    //         echo 'Le mail n\'a pas pu être envoyé.';
    //         echo 'Erreur : ' . $mail->ErrorInfo;
    //     }
    // }
    // public static function sendMail($destinataire, $sujet, $message)
    // {
    //     $headers = "From: xxxxx@gmail.com";
    //     if (mail($destinataire, $sujet, $message, $headers)) {
    //         self::ajouterMessageAlerte("Mail envoyé", self::COULEUR_VERTE);
    //     } else {
    //         self::ajouterMessageAlerte("Mail non envoyé", self::COULEUR_ROUGE);
    //     }
    // }
    // public static function ajoutImage($file, $dir)
    // {
    //     if (!isset($file['name']) || empty($file['name']))
    //         throw new \Exception("Vous devez indiquer une image");

    //     if (!file_exists($dir)) mkdir($dir, 0777);

    //     $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    //     $random = rand(0, 99999);
    //     $target_file = $dir . $random . "_" . $file['name'];

    //     if (!getimagesize($file["tmp_name"]))
    //         throw new \Exception("Le fichier n'est pas une image");
    //     if ($extension !== "jpg" && $extension !== "jpeg" && $extension !== "png" && $extension !== "gif")
    //         throw new \Exception("L'extension du fichier n'est pas reconnu");
    //     if (file_exists($target_file))
    //         throw new \Exception("Le fichier existe déjà");
    //     if ($file['size'] > 500000)
    //         throw new \Exception("Le fichier est trop gros");
    //     if (!move_uploaded_file($file['tmp_name'], $target_file))
    //         throw new \Exception("l'ajout de l'image n'a pas fonctionné");
    //     else return ($random . "_" . $file['name']);
    // }






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
