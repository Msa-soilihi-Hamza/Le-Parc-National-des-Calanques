<?php

declare(strict_types=1);

namespace ParcCalanques\Shared\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
use ParcCalanques\Shared\Utils\EnvLoader;

class EmailService
{
    private PHPMailer $mailer;
    
    public function __construct()
    {
        // Variables d'environnement déjà chargées automatiquement

        $this->mailer = new PHPMailer(true);

        // Configuration SMTP depuis les variables d'environnement
        $this->mailer->isSMTP();
        $this->mailer->Host = EnvLoader::getRequired('SMTP_HOST');
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = EnvLoader::getRequired('SMTP_USERNAME');
        $this->mailer->Password = EnvLoader::getRequired('SMTP_PASSWORD');

        // Configuration de l'encryption
        $encryption = EnvLoader::get('SMTP_ENCRYPTION', 'tls');
        $this->mailer->SMTPSecure = $encryption === 'ssl' ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
        $this->mailer->Port = (int)EnvLoader::get('SMTP_PORT', '587');

        // Configuration de l'expéditeur
        $fromEmail = EnvLoader::getRequired('SMTP_FROM_EMAIL');
        $fromName = EnvLoader::get('SMTP_FROM_NAME', 'Parc National des Calanques');
        $this->mailer->setFrom($fromEmail, $fromName);

        // Configuration pour le débogage
        $debugLevel = (int)EnvLoader::get('SMTP_DEBUG', '0');
        $this->mailer->SMTPDebug = $debugLevel;
        $this->mailer->isHTML(true);
    }
    
    /**
     * Envoie un email de vérification
     */
    public function sendVerificationEmail(string $email, string $name, string $verificationToken): bool
    {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($email, $name);
            
            $this->mailer->Subject = 'Vérification de votre compte - Parc National des Calanques';
            
            // URL de vérification depuis les variables d'environnement
            $baseUrl = EnvLoader::get('APP_URL', 'http://localhost/Le-Parc-National-des-Calanques');
            $verificationUrl = $baseUrl . "/verify-email.php?token=" . urlencode($verificationToken);
            
            // Template HTML
            $htmlBody = $this->getVerificationEmailTemplate($name, $verificationUrl);
            $this->mailer->Body = $htmlBody;
            
            // Version texte
            $this->mailer->AltBody = "
Bonjour $name,

Merci de vous être inscrit(e) sur le site du Parc National des Calanques.

Pour activer votre compte, cliquez sur le lien suivant ou copiez-le dans votre navigateur :
$verificationUrl

Ce lien expire dans 24 heures.

Si vous n'avez pas créé de compte, ignorez cet email.

Cordialement,
L'équipe du Parc National des Calanques
            ";
            
            return $this->mailer->send();
            
        } catch (Exception $e) {
            error_log("Erreur envoi email : " . $this->mailer->ErrorInfo);
            return false;
        }
    }
    
    /**
     * Template HTML pour l'email de vérification
     */
    private function getVerificationEmailTemplate(string $name, string $verificationUrl): string
    {
        return "
        <!DOCTYPE html>
        <html lang='fr'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Vérification de votre compte</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #60a5fa, #3b82f6); color: white; text-align: center; padding: 30px; border-radius: 10px 10px 0 0; }
                .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px; }
                .btn { display: inline-block; background: #3b82f6; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; margin: 20px 0; }
                .btn:hover { background: #2563eb; }
                .footer { text-align: center; margin-top: 30px; color: #666; font-size: 14px; }
                .mountain { font-size: 24px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <div class='mountain'>🏔️</div>
                    <h1>Parc National des Calanques</h1>
                    <p>Vérification de votre compte</p>
                </div>
                
                <div class='content'>
                    <h2>Bonjour $name,</h2>
                    
                    <p>Merci de vous être inscrit(e) sur le site du Parc National des Calanques !</p>
                    
                    <p>Pour activer votre compte et commencer à explorer nos services, cliquez sur le bouton ci-dessous :</p>
                    
                    <center>
                        <a href='$verificationUrl' class='btn'>Activer mon compte</a>
                    </center>
                    
                    <p>Ou copiez ce lien dans votre navigateur :</p>
                    <p style='background: #e9ecef; padding: 10px; border-radius: 5px; word-break: break-all; font-family: monospace;'>$verificationUrl</p>
                    
                    <p><strong>Important :</strong> Ce lien expire dans 24 heures pour des raisons de sécurité.</p>
                    
                    <p>Si vous n'avez pas créé de compte, ignorez simplement cet email.</p>
                    
                    <div class='footer'>
                        <p>Cordialement,<br>
                        L'équipe du Parc National des Calanques</p>
                        
                        <p>🌊 Découvrez la beauté préservée de la Méditerranée 🌊</p>
                    </div>
                </div>
            </div>
        </body>
        </html>
        ";
    }
    
    /**
     * Envoie un email de bienvenue après vérification
     */
    public function sendWelcomeEmail(string $email, string $name): bool
    {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($email, $name);
            
            $this->mailer->Subject = 'Bienvenue au Parc National des Calanques !';
            
            $htmlBody = "
            <!DOCTYPE html>
            <html lang='fr'>
            <head>
                <meta charset='UTF-8'>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: linear-gradient(135deg, #10b981, #059669); color: white; text-align: center; padding: 30px; border-radius: 10px; }
                    .content { padding: 30px 0; }
                    .feature { background: #f0f9ff; padding: 15px; margin: 10px 0; border-radius: 8px; border-left: 4px solid #3b82f6; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>🏔️ Bienvenue $name !</h1>
                        <p>Votre compte est maintenant activé</p>
                    </div>
                    
                    <div class='content'>
                        <h2>Félicitations !</h2>
                        
                        <p>Votre compte a été vérifié avec succès. Vous pouvez maintenant profiter de tous nos services :</p>
                        
                        <div class='feature'>
                            <strong>🏕️ Réservations de camping</strong><br>
                            Réservez votre emplacement dans les plus beaux sites du parc
                        </div>
                        
                        <div class='feature'>
                            <strong>🥾 Sentiers et randonnées</strong><br>
                            Découvrez nos sentiers balisés et points d'intérêt
                        </div>
                        
                        <div class='feature'>
                            <strong>🌿 Biodiversité</strong><br>
                            Explorez la faune et flore exceptionnelles des Calanques
                        </div>
                        
                        <p>Nous vous souhaitons de merveilleuses découvertes !</p>
                        
                        <p>L'équipe du Parc National des Calanques</p>
                    </div>
                </div>
            </body>
            </html>
            ";
            
            $this->mailer->Body = $htmlBody;
            
            return $this->mailer->send();
            
        } catch (Exception $e) {
            error_log("Erreur envoi email de bienvenue : " . $this->mailer->ErrorInfo);
            return false;
        }
    }
}