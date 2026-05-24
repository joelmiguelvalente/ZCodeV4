<?php

/**
 * @package     ZCode
 * @author      Miguel92
 * @copyright   2024 - 2026
 * @version     4.1.0
*/

declare(strict_types=1);

namespace App\Models;

if (!defined('ZCODE_ULTIMATE')) {
    exit('No se permite el acceso directo al script');
}

use App\Repository\AutenticarRepository;
use App\Models\Session;
use App\Services\TotpService;
use App\Utils\PasswordHandler;

class Autenticar
{
    protected ?Session $Session = null;
    protected PasswordHandler $PasswordHandler;
    private AutenticarRepository $AutenticarRepository;

    public function __construct(Session $Session, PasswordHandler $PasswordHandler)
    {
        if (!$Session) {
            throw new \Exception('Session NO está siendo inyectada');
        }
        $this->Session = $Session;
        $this->PasswordHandler = $PasswordHandler;
        $this->AutenticarRepository = new AutenticarRepository();
    }

    /*
        CARGA LA SESSION
        setSession()
    */
    public function setSession(): bool
    {
        if (!$this->Session->read()) {
            $this->Session->create();
            $this->Session->read();
        }
        return true;
    }


    private function redirectWithParams(): ?string
    {
        if (empty($_SERVER['HTTP_REFERER'])) {
            return null;
        }
        $ref = parse_url($_SERVER['HTTP_REFERER']);
        if (empty($ref['query'])) {
            return null;
        }
        parse_str($ref['query'], $e);
        if (!isset($e["redirectTo"])) {
            return null;
        }
        return "5: " . urldecode(base64_decode($e["redirectTo"]));
    }

    private function createSession(int $user_id = 0, bool $remember = false)
    {
        // Si no hay sesión, la creo
        $this->setSession();
        // Si la verificación es exitosa, actualizar la sesión
        $this->Session->update((int)$user_id, $remember, true);
    }

    /**
     * Función para validar el código de autentificación
    */
    public function validateTwoFactor(string $username = '', bool $remember = false)
    {
        // Obtener el secret y el código de recuperación del usuario desde la base de datos
        $data = $this->AutenticarRepository->getUserConfig($username);
        // Recuperar los códigos de recuperación (en caso de que el código 2FA no sea válido)
        $recovery = json_decode(base64_decode($data["user_recovery"]), true);
        $totp = new TotpService();
        # var_dump($totp->verifyCodeSecret($data["user_secret_2fa"], $_POST['code']));
        // Verificar si el código ingresado por el usuario es válido
        if ($totp->verifyCodeSecret($data["user_secret_2fa"], $_POST['code']) || in_array($_POST['code'], $recovery)) {
            $this->createSession((int)$data['user_id'], $remember);
            return '1: Código 2FA correcto.';
        }
        // Si la verificación falla
        return '0: No se pudo comprobar la doble autentificación.';
    }

    /**
     * Inicia sesión del usuario
     *
     * @param string $usuario Nombre o correo del usuario
     * @param string $password Contraseña
     * @param bool $recordar Recordar sesión (autologin)
     * @return string
     */
    public function login(string $usuario = '', string $password = '', bool $recordar = false, bool $redirectTo = false): ?string
    {
        $this->setSession();
        $user = $this->AutenticarRepository->getUserConfig($usuario);

        if (!isset($user['user_id'])) {
            return '0: Usuario no encontrado';
        }

        // Validar contraseña
        if (!$this->PasswordHandler->verify($password, $user['user_password'])) {
            return '2: Tu contrase&ntilde;a es incorrecta.';
        }

        // El usuario esta activo
        if (!(int)$user['user_activo']) {
            return '3: Debes activar tu cuenta';
        }

        // Comprobando 2FA
        if (empty($user['user_secret_2fa'])) {
            if ($this->Session->update((int)$user['user_id'], $recordar, true)) {
                $redireccionar = $this->redirectWithParams();
                if ($redirectTo || $redireccionar) {
                    $tsCore->redirectTo($redireccionar ?? true);
                } else {
                    return "1: Accediendo...";
                }
            }
            return '0: Error al inicia sesión';
        } else {
            return '4: Ingrese el código de autentificación.';
        }
    }

    /**
     * Cierra la sesión actual
     */
    public function logout()
    {
        if (!$this->Session instanceof Session) {
            throw new \Exception('La sesión no está disponible en logout()');
        }

        if ($this->Session->read()) {
            $this->Session->destroy();
        }
    }

    public function getSessionID()
    {
        return $this->Session->ID ?? null;
    }

    public function getTimeNow()
    {
        return $this->Session->time_now;
    }

    public function getIpAddress()
    {
        return $this->Session->ip_address;
    }

    public function clearSession()
    {
        $this->Session = null;
    }
}
