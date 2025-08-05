<?php

namespace App\Service;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailerService
{
    private string $host;
    private string $username;
    private string $password;
    private int $port;
    private string $fromEmail;
    private string $fromName;

    public function __construct(string $host, string $username, string $password, int $port, string $fromEmail, string $fromName)
    {
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
        $this->port = $port;
        $this->fromEmail = $fromEmail;
        $this->fromName = $fromName;
    }

    public function sendMail(string $toEmail, string $toName, string $subject, string $body): bool
    {
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = $this->host;
            $mail->SMTPAuth   = true;
            $mail->Username   = $this->username;
            $mail->Password   = $this->password;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = $this->port;

            $mail->setFrom($this->fromEmail, $this->fromName);
            $mail->addAddress($toEmail, $toName);

            $mail->isHTML(false);
            $mail->Subject = $subject;
            $mail->Body    = $body;

            $mail->send();

            return true;
        } catch (Exception $e) {
            // Tu peux logger l'erreur ici si besoin
            return false;
        }
    }
}
