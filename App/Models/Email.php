<?php

/**
 * @package     ZCode
 * @author      Miguel92
 * @copyright   2024 - 2026
 * @version     4.1.0
*/

declare(strict_types=1);

namespace App\Models;

use App\Services\LoggerService;
use App\Contexts\EmailContext;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!defined('ZCODE_ULTIMATE')) {
    exit('No se permite el acceso directo al script');
}

class Email
{
    private string $to = '';
    private string $subjectKey = 'notification';
    private string $body = '';
    private string $template = 'layout';
    private array $templateVars = [];

    private LoggerService $logger;
    protected EmailContext $EmailContext;
    private array $config;

    public function __construct(EmailContext $EmailContext, array $config = [])
    {
        $this->logger = new LoggerService();
        $this->logger->setLogFile('email.log');

        $this->EmailContext = $EmailContext;
        $this->config = $config;
    }

    // Setters fluidos --------------------------------------------------------

    public function to(string $email): self
    {
        $this->to = $email;
        return $this;
    }

    public function subject(string $key): self
    {
        $this->subjectKey = $key;
        return $this;
    }

    public function template(string $tpl): self
    {
        $this->template = $tpl;
        return $this;
    }

    public function body(string $html): self
    {
        $this->body = $html;
        return $this;
    }

    public function withVars(array $vars): self
    {
        $this->templateVars = $vars;
        return $this;
    }

    // ----------------------------------------------------------------------

    private function resolveSubject(): array
    {
        return match ($this->subjectKey) {
            'signup'            => ['type' => 'alert', 'text' => 'Por favor completa tu registro.'],
            'welcome'           => ['type' => 'success', 'text' => 'Bienvenido a nuestra comunidad.'],
            'password_recovery' => ['type' => 'notification', 'text' => 'Instrucciones para recuperar tu contraseña.'],
            'email_change'      => ['type' => 'notification', 'text' => 'Confirma el cambio de tu dirección de correo.'],
            'twofactor_setup'   => ['type' => 'notification', 'text' => 'Configura tu verificación en dos pasos.'],
            'security_alert'    => ['type' => 'alert', 'text' => 'Hemos detectado un intento de acceso a tu cuenta.'],
            'support_reply'     => ['type' => 'notification', 'text' => 'Tienes una respuesta de soporte.'],
            'payment_success'   => ['type' => 'success', 'text' => 'Tu pago se ha procesado correctamente.'],
            'payment_failed'    => ['type' => 'error', 'text' => 'Hubo un problema con tu pago.'],
            'newsletter'        => ['type' => 'notification', 'text' => 'Últimas novedades y actualizaciones.'],
            'admin_notice'      => ['type' => 'alert', 'text' => 'Tienes un aviso importante del administrador.'],
            'ban_notice'        => ['type' => 'alert', 'text' => 'Tu cuenta ha sido suspendida.'],
            'system_update'     => ['type' => 'notification', 'text' => 'Actualización importante del sistema.'],
            default             => ['type' => 'notification', 'text' => 'Notificación'],
        };
    }

    private function applyHeaderColor(string $html, string $type): string
    {
        $color = match ($type) {
            'alert'   => '#D99100',
            'error'   => '#C74343',
            'success' => '#2F9E45',
            default   => '#2588BC',
        };
        return str_replace('{color_header}', $color, $html);
    }

    private function buildBody(): string
    {
        $path = UTILITIES . "/emails/" . $this->template . ".php";

        if (!file_exists($path)) {
            $this->logger->error("Email template not found: $path");
            return '';
        }

        $template = require $path;
        if (!is_string($template)) {
            $this->logger->error("Email template must return string.");
            return '';
        }

        $subject = $this->resolveSubject();

        $replace = array_merge([
            '{url}'       => $this->EmailContext->getUrl(),
            '{titulo}'    => $this->EmailContext->getTitle(),
            '{slogan}'    => $this->EmailContext->getSlogan(),
            '{contenido}' => $this->body,
            '{asunto}'    => $subject['text'],
        ], $this->templateVars);

        $html = str_replace(array_keys($replace), array_values($replace), $template);

        return $this->applyHeaderColor($html, $subject['type']);
    }

    private function validateConfig(): bool
    {
        $required = ['smtp_host', 'smtp_user', 'smtp_pass', 'smtp_name', 'smtp_port', 'smtp_secure'];
        foreach ($required as $key) {
            if (empty($this->config[$key])) {
                $this->logger->warning("Missing SMTP configuration: $key");
                return false;
            }
        }

        return true;
    }

    // ----------------------------------------------------------------------

    public function send(): bool
    {
        if (!$this->validateConfig()) {
            return false;
        }

        if (!filter_var($this->to, FILTER_VALIDATE_EMAIL)) {
            $this->logger->warning("Invalid recipient email: {$this->to}");
            return false;
        }

        $subject = $this->resolveSubject();
        $mail = new PHPMailer(true);

        try {
            // SMTP
            $mail->isSMTP();
            $mail->Host = $this->config['smtp_host'];
            $mail->SMTPAuth = true;
            $mail->Username = $this->config['smtp_user'];
            $mail->Password = $this->config['smtp_pass'];
            $mail->SMTPSecure = $this->config['smtp_secure'] === 'ssl' ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = (int) $this->config['smtp_port'];
            $mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer'       => false,
                    'verify_peer_name'  => false,
                    'allow_self_signed' => true
                ]
            ];

            $mail->setFrom(
                $this->EmailContext->getEmail(),
                $this->EmailContext->getTitle()
            );

            $mail->addAddress($this->to);

            // Embeded Image
            $mail->addEmbeddedImage(
                TS_IMAGES . '/favicon/logo-128.webp',
                'logo_cid',
                'logo-128.webp'
            );

            // Content
            $mail->CharSet  = 'UTF-8';
            $mail->Encoding = 'base64';
            $mail->isHTML(true);

            $mail->Subject = $subject['text'];
            $mail->Body    = $this->buildBody();
            $this->logger->info("Mail Send");
            return $mail->send();
        } catch (Exception $e) {
            $this->logger->error("Mailer Error: {$mail->ErrorInfo}");
            return false;
        }
    }
}
