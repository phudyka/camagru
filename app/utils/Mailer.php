<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require ROOT_PATH . '/vendor/autoload.php';

class Mailer {
    private static function getMailer() {
        $mail = new PHPMailer(true);
        
        // Configuration du serveur
        $mail->isSMTP();
        $mail->Host = getenv('MAIL_HOST') ?: 'mailhog'; // Utiliser MailHog pour le développement
        $mail->SMTPAuth = false;
        $mail->Port = getenv('MAIL_PORT') ?: 1025;
        
        // Expéditeur
        $mail->setFrom('noreply@camagru.com', 'Camagru');
        
        // Format
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        
        return $mail;
    }
    
    public static function sendVerificationEmail($to, $username, $token) {
        try {
            $mail = self::getMailer();
            
            // Destinataire
            $mail->addAddress($to);
            
            // Contenu
            $mail->Subject = 'Verify your Camagru account';
            $mail->Body = "
            <html>
            <head>
                <title>Verify your Camagru account</title>
            </head>
            <body>
                <h2>Hello {$username},</h2>
                <p>Thank you for registering on Camagru. Please click the link below to verify your account:</p>
                <p><a href='http://{$_SERVER['HTTP_HOST']}/verify?token={$token}'>Verify my account</a></p>
                <p>If you did not create this account, you can ignore this email.</p>
                <p>The Camagru Team</p>
            </body>
            </html>
            ";
            
            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Email could not be sent. Mailer Error: {$mail->ErrorInfo}");
            return false;
        }
    }
    
    public static function sendNotificationEmail($to, $username, $action, $commentText = '') {
        try {
            $mail = self::getMailer();
            
            // Destinataire
            $mail->addAddress($to);
            
            // Contenu
            if ($action === 'like') {
                $mail->Subject = 'Someone liked your photo on Camagru';
                $body = "
                <html>
                <head>
                    <title>New like on your photo</title>
                </head>
                <body>
                    <h2>Hello {$username},</h2>
                    <p>Someone liked your photo on Camagru.</p>
                    <p>Log in to see your gallery: <a href='http://{$_SERVER['HTTP_HOST']}/gallery'>View my gallery</a></p>
                    <p>The Camagru Team</p>
                </body>
                </html>
                ";
            } else if ($action === 'comment') {
                $mail->Subject = 'New comment on your photo on Camagru';
                $body = "
                <html>
                <head>
                    <title>New comment on your photo</title>
                </head>
                <body>
                    <h2>Hello {$username},</h2>
                    <p>Someone commented on your photo on Camagru:</p>
                    <p><em>\"" . htmlspecialchars($commentText) . "\"</em></p>
                    <p>Log in to see your gallery: <a href='http://{$_SERVER['HTTP_HOST']}/gallery'>View my gallery</a></p>
                    <p>The Camagru Team</p>
                </body>
                </html>
                ";
            }
            
            $mail->Body = $body;
            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Email could not be sent. Mailer Error: {$mail->ErrorInfo}");
            return false;
        }
    }
} 