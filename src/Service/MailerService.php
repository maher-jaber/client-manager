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

    public function __construct(
        string $host,
        string $username,
        string $password,
        int $port,
        string $fromEmail,
        string $fromName
    ) {
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
        $this->port = $port;
        $this->fromEmail = $fromEmail;
        $this->fromName = $fromName;
    }

    public function sendMail(string $toEmail, string $toName, string $subject, string $body, string $entite = 'IDS'): bool
    {
        $sender = $this->getSenderConfig($entite);
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = $this->host;
            $mail->Port = $this->port;

            if (!empty($this->username) && !empty($this->password)) {
                $mail->SMTPAuth = true;
                $mail->Username = $this->username;
                $mail->Password = $this->password;
            } else {
                $mail->SMTPAuth = false;
            }

            // Chiffrement selon port
            if ($this->port == 465) {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // SSL
            } elseif ($this->port == 587) {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // TLS
            } else {
                $mail->SMTPSecure = false;
            }

            $mail->setFrom($sender['email'], $sender['name']);
            $mail->addAddress($toEmail, $toName);

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;

            $mail->send();

            return true;
        } catch (Exception $e) {
            error_log('Mailer Error: ' . $mail->ErrorInfo); // Pour debug
            return false;
        }
    }
    private function getSenderConfig(string $entite): array
    {
        return match (strtolower($entite)) {
            'altra' => [
                'email' => 'GroupeAltraSystems@altra-systems.com',
                'name' => 'Altra System',
            ],
            'ids' => [
                'email' => 'info@ids.tn',
                'name' => 'IDS Group',
            ],
            default => [
                'email' => 'default@example.com',
                'name' => 'Default Sender',
            ]
        };
    }
}
