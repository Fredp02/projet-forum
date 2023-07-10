<?php

namespace Controllers\Services\HtmlTemplateMail;
// On inclut la classe PHPMailer
use PHPMailer\PHPMailer\PHPMailer;

// On crée une nouvelle classe qui étend PHPMailer
class HtmlTemplateMail extends PHPMailer
{

    // On déclare une propriété pour stocker le chemin du template HTML
    protected $template;

    // On déclare une propriété pour stocker les valeurs à remplacer dans le template
    protected $values = array();

    // On crée une méthode pour définir le template HTML à utiliser
    public function setTemplate($template)
    {
        $this->template = $template;
    }

    // On crée une méthode pour définir les valeurs à remplacer dans le template
    public function setValues($values)
    {
        $this->values = $values;
    }

    // On surcharge la méthode send pour utiliser le template HTML
    public function send()
    {
        // On vérifie si le template HTML est défini
        if (isset($this->template)) {
            // On lit le contenu du fichier HTML
            $body = file_get_contents($this->template);

            // On remplace les variables dans le contenu par les valeurs définies
            foreach ($this->values as $key => $value) {

                $body = str_replace("{" . $key . "}", $value, $body);
            }

            // On utilise la méthode MsgHTML pour définir le contenu du mail en HTML
            /**
    //          * *en utilisant $mail->msgHTML, on se passe des lignes $mail->Body, et $mail->AltBody.
    //          * !dans la pratique, il est recommandé d'utiliser systématiquement $mail->msgHTML, car il crée automatiquement deux versions : une en HTML, et une autre en texte brut, ainsi les personnes utilisant un client de messagerie qui n'accepte pas l'html, pourront tout de même lire le mail.
    //          */
            $this->MsgHTML($body);
        }
        // On appelle la méthode send de la classe parente
        // return parent::send();
        return parent::send();
    }
}
