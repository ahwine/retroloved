<?php
/**
 * Email Configuration - EXAMPLE FILE
 * Copy this file to email.php and update with your SMTP credentials
 */

// SMTP Configuration
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password');
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls');

// Email Settings
define('FROM_EMAIL', 'noreply@retroloved.com');
define('FROM_NAME', 'RetroLoved');
define('REPLY_TO_EMAIL', 'support@retroloved.com');

// Load PHPMailer
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Email Helper Class
 */
class EmailHelper {
    /**
     * Send email using PHPMailer
     */
    public static function send($to, $subject, $body, $altBody = '') {
        $mail = new PHPMailer(true);
        
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = SMTP_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = SMTP_USERNAME;
            $mail->Password = SMTP_PASSWORD;
            $mail->SMTPSecure = SMTP_SECURE;
            $mail->Port = SMTP_PORT;
            
            // Recipients
            $mail->setFrom(FROM_EMAIL, FROM_NAME);
            $mail->addAddress($to);
            $mail->addReplyTo(REPLY_TO_EMAIL, FROM_NAME);
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $body;
            $mail->AltBody = $altBody ?: strip_tags($body);
            
            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Email Error: {$mail->ErrorInfo}");
            return false;
        }
    }
    
    /**
     * Get email template
     */
    public static function getTemplate($title, $content) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>{$title}</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                <h2 style='color: #D97706;'>{$title}</h2>
                {$content}
                <hr style='margin: 30px 0; border: none; border-top: 1px solid #E5E7EB;'>
                <p style='color: #6B7280; font-size: 12px;'>
                    Email ini dikirim otomatis oleh sistem RetroLoved.<br>
                    Jangan balas email ini.
                </p>
            </div>
        </body>
        </html>
        ";
    }
}
?>
