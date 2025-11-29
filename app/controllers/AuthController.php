<?php
/**
 * AuthController - Manejo de autenticación
 *
 * Login, Logout, Recuperación de contraseña (User Story #6)
 */

require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../helpers/ValidationHelper.php';
require_once __DIR__ . '/../helpers/MailHelper.php';

class AuthController
{
    /**
     * Mostrar formulario de login
     */
    public function login(): void
    {
        // Si ya está autenticado, redirigir al dashboard correspondiente
        if (isset($_SESSION['user_id'])) {
            $rol = $_SESSION['user_rol'] ?? 'cliente';
            $dashboardRol = $rol === 'administrador' ? 'admin' : $rol;
            redirect("$dashboardRol/dashboard");
        }

        // Renderizar vista de login
        require_once __DIR__ . '/../views/auth/login.php';
    }

    /**
     * Procesar login
     */
    public function processLogin(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('auth/login');
        }

        // Validar CSRF token
        if (!ValidationHelper::validateCSRFToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'Token de seguridad inválido';
            redirect('auth/login');
        }

        $email = sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        // Validar campos requeridos
        if (empty($email) || empty($password)) {
            $_SESSION['error'] = 'Todos los campos son obligatorios';
            redirect('auth/login');
        }

        // Validar formato de email
        if (!ValidationHelper::validateEmail($email)) {
            $_SESSION['error'] = 'Formato de email inválido';
            redirect('auth/login');
        }

        // Verificar credenciales
        $resultado = Usuario::verifyLogin($email, $password);

        if (!$resultado['success']) {
            $_SESSION['error'] = $resultado['message'];
            redirect('auth/login');
        }

        $usuario = $resultado['user'];

        // Crear sesión
        $_SESSION['user_id'] = $usuario->id;
        $_SESSION['user_nombre'] = $usuario->nombre_completo;
        $_SESSION['user_email'] = $usuario->email;
        $_SESSION['user_rol'] = $usuario->rol;
        $_SESSION['user_primer_acceso'] = $usuario->primer_acceso;
        $_SESSION['user_password_temporal'] = $usuario->password_temporal;

        // Regenerar ID de sesión por seguridad
        session_regenerate_id(true);

        writeLog("Login exitoso: {$usuario->email} (ID: {$usuario->id})", 'info');

        // Verificar si es primer acceso (User Story #2)
        if ($usuario->primer_acceso || $usuario->password_temporal) {
            redirect('auth/cambiar-password-obligatorio');
        }

        // Redirigir al dashboard según rol (admin en lugar de administrador)
        $dashboardRol = $usuario->rol === 'administrador' ? 'admin' : $usuario->rol;
        redirect("{$dashboardRol}/dashboard");
    }

    /**
     * Logout
     */
    public function logout(): void
    {
        $userId = $_SESSION['user_id'] ?? null;
        $userEmail = $_SESSION['user_email'] ?? 'Desconocido';

        // 1. Guardar el mensaje de éxito antes de destruir la sesión
        $successMessage = 'Sesión cerrada correctamente';

        // Destruir sesión
        session_unset();
        session_destroy();

        writeLog("Logout: $userEmail (ID: $userId)", 'info');

        // 2. Iniciar una nueva sesión limpia para el mensaje flash
        session_start();
        $_SESSION['success'] = $successMessage;

        redirect('auth/login');
    }

    /**
     * Mostrar formulario de recuperación de contraseña (User Story #6)
     */
    public function forgotPassword(): void
    {
        require_once __DIR__ . '/../views/auth/forgot_password.php';
    }

    /**
     * Procesar solicitud de recuperación de contraseña
     */
    public function processForgotPassword(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('auth/forgot-password');
        }

        // Validar CSRF
        if (!ValidationHelper::validateCSRFToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'Token de seguridad inválido';
            redirect('auth/forgot-password');
        }

        $email = sanitize($_POST['email'] ?? '');

        // Validar email
        if (!ValidationHelper::validateEmail($email)) {
            $_SESSION['error'] = 'Formato de email inválido';
            redirect('auth/forgot-password');
        }

        // Verificar rate limiting
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        if (!$this->checkRateLimiting($ip)) {
            $_SESSION['error'] = 'Por favor, espere ' . PASSWORD_RESET_RATE_LIMIT . ' segundos antes de solicitar otro código';
            redirect('auth/forgot-password');
        }

        $usuario = Usuario::findByEmail($email);

        // No revelar si el email existe (anti-enumeración)
        $_SESSION['success'] = 'Si el email existe en nuestro sistema, recibirás un código de verificación';

        // Si el usuario existe y está activo, enviar código
        if ($usuario && $usuario->activo) {
            // Generar código de 6 dígitos
            $codigo = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $fechaExpiracion = date('Y-m-d H:i:s', strtotime('+' . PASSWORD_RESET_CODE_EXPIRATION . ' minutes'));

            // Guardar en BD
            $sql = "INSERT INTO password_reset_tokens
                    (usuario_id, email, codigo, fecha_expiracion, ip_address, user_agent)
                    VALUES (?, ?, ?, ?, ?, ?)";

            Database::execute($sql, [
                $usuario->id,
                $email,
                $codigo,
                $fechaExpiracion,
                $ip,
                $_SERVER['HTTP_USER_AGENT'] ?? null
            ]);

            // Enviar email con código
            MailHelper::sendPasswordResetCode($email, $usuario->nombre_completo, $codigo);

            writeLog("Código de recuperación enviado a: $email", 'info');

            // Guardar email en sesión para el siguiente paso
            $_SESSION['reset_email'] = $email;
        } else {
            // Log de intento con email no registrado
            writeLog("Intento de recuperación con email no registrado: $email", 'warning');
        }

        redirect('auth/verificar-codigo');
    }

    /**
     * Mostrar formulario de verificación de código
     */
    public function verificarCodigo(): void
    {
        if (!isset($_SESSION['reset_email'])) {
            redirect('auth/forgot-password');
        }

        require_once __DIR__ . '/../views/auth/verify_code.php';
    }

    /**
     * Procesar verificación de código
     */
    public function processVerificarCodigo(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('auth/verificar-codigo');
        }

        if (!isset($_SESSION['reset_email'])) {
            redirect('auth/forgot-password');
        }

        // Validar CSRF
        if (!ValidationHelper::validateCSRFToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'Token de seguridad inválido';
            redirect('auth/verificar-codigo');
        }

        $codigo = sanitize($_POST['codigo'] ?? '');
        $email = $_SESSION['reset_email'];

        // Validar formato de código
        if (!ValidationHelper::validateVerificationCode($codigo)) {
            $_SESSION['error'] = 'Código inválido. Debe ser de 6 dígitos';
            redirect('auth/verificar-codigo');
        }

        // Buscar código en BD
        $sql = "SELECT * FROM password_reset_tokens
                WHERE email = ?
                  AND codigo = ?
                  AND usado = FALSE
                  AND fecha_expiracion > NOW()
                ORDER BY fecha_creacion DESC
                LIMIT 1";

        $token = Database::fetchOne($sql, [$email, $codigo]);

        if (!$token) {
            // Incrementar intentos fallidos
            $this->incrementarIntentosValidacion($email, $codigo);

            $_SESSION['error'] = 'Código incorrecto o expirado';
            redirect('auth/verificar-codigo');
        }

        // Código válido - marcar como verificado en sesión
        $_SESSION['reset_token_id'] = $token['id'];
        $_SESSION['reset_usuario_id'] = $token['usuario_id'];

        writeLog("Código verificado correctamente para: $email", 'info');

        redirect('auth/nueva-password');
    }

    /**
     * Mostrar formulario de nueva contraseña
     */
    public function nuevaPassword(): void
    {
        if (!isset($_SESSION['reset_token_id'])) {
            redirect('auth/forgot-password');
        }

        require_once __DIR__ . '/../views/auth/new_password.php';
    }

    /**
     * Procesar nueva contraseña
     */
    public function processNuevaPassword(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('auth/nueva-password');
        }

        if (!isset($_SESSION['reset_token_id']) || !isset($_SESSION['reset_usuario_id'])) {
            redirect('auth/forgot-password');
        }

        // Validar CSRF
        if (!ValidationHelper::validateCSRFToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'Token de seguridad inválido';
            redirect('auth/nueva-password');
        }

        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';

        // Validar que coincidan
        if ($password !== $passwordConfirm) {
            $_SESSION['error'] = 'Las contraseñas no coinciden';
            redirect('auth/nueva-password');
        }

        // Validar requisitos de contraseña
        $validacion = ValidationHelper::validatePassword($password);
        if (!$validacion['valid']) {
            $_SESSION['error'] = implode('<br>', $validacion['errors']);
            redirect('auth/nueva-password');
        }

        // Cargar usuario
        $usuario = Usuario::findById($_SESSION['reset_usuario_id']);

        if (!$usuario) {
            $_SESSION['error'] = 'Usuario no encontrado';
            redirect('auth/forgot-password');
        }

        // Verificar que no sea la misma contraseña anterior
        $sql = "SELECT password FROM usuarios WHERE id = ?";
        $result = Database::fetchOne($sql, [$usuario->id]);

        if (password_verify($password, $result['password'])) {
            $_SESSION['error'] = 'No puedes usar la misma contraseña anterior';
            redirect('auth/nueva-password');
        }

        // Cambiar contraseña
        if (!$usuario->cambiarPassword($password)) {
            $_SESSION['error'] = 'Error al cambiar la contraseña';
            redirect('auth/nueva-password');
        }

        // Marcar token como usado
        $sql = "UPDATE password_reset_tokens SET usado = TRUE WHERE id = ?";
        Database::execute($sql, [$_SESSION['reset_token_id']]);

        // Enviar email de confirmación
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        MailHelper::sendPasswordChanged($usuario->email, $usuario->nombre_completo, $ip);

        writeLog("Contraseña cambiada exitosamente para: {$usuario->email}", 'info');

        // Limpiar sesión
        unset($_SESSION['reset_email']);
        unset($_SESSION['reset_token_id']);
        unset($_SESSION['reset_usuario_id']);

        $_SESSION['success'] = 'Contraseña actualizada correctamente. Puedes iniciar sesión';
        redirect('auth/login');
    }

    /**
     * Cambio de contraseña obligatorio (User Story #2)
     */
    public function cambiarPasswordObligatorio(): void
    {
        if (!isset($_SESSION['user_id'])) {
            redirect('auth/login');
        }

        // Si no es primer acceso, redirigir
        if (!$_SESSION['user_primer_acceso'] && !$_SESSION['user_password_temporal']) {
            $rol = $_SESSION['user_rol'] ?? 'cliente';
            redirect("$rol/dashboard");
        }

        require_once __DIR__ . '/../views/auth/cambiar_password_obligatorio.php';
    }

    /**
     * Procesar cambio de contraseña obligatorio
     */
    public function processCambiarPasswordObligatorio(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('auth/cambiar-password-obligatorio');
        }

        if (!isset($_SESSION['user_id'])) {
            redirect('auth/login');
        }

        // Validar CSRF
        if (!ValidationHelper::validateCSRFToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'Token de seguridad inválido';
            redirect('auth/cambiar-password-obligatorio');
        }

        $passwordActual = $_POST['password_actual'] ?? '';
        $passwordNueva = $_POST['password_nueva'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';

        $usuario = Usuario::findById($_SESSION['user_id']);

        if (!$usuario) {
            $this->logout();
            return;
        }

        // Validar contraseña actual
        $sql = "SELECT password FROM usuarios WHERE id = ?";
        $result = Database::fetchOne($sql, [$usuario->id]);

        if (!password_verify($passwordActual, $result['password'])) {
            $_SESSION['error'] = 'Contraseña actual incorrecta';
            redirect('auth/cambiar-password-obligatorio');
        }

        // Validar que coincidan
        if ($passwordNueva !== $passwordConfirm) {
            $_SESSION['error'] = 'Las contraseñas no coinciden';
            redirect('auth/cambiar-password-obligatorio');
        }

        // Validar requisitos
        $validacion = ValidationHelper::validatePassword($passwordNueva);
        if (!$validacion['valid']) {
            $_SESSION['error'] = implode('<br>', $validacion['errors']);
            redirect('auth/cambiar-password-obligatorio');
        }

        // Cambiar contraseña
        if (!$usuario->cambiarPassword($passwordNueva)) {
            $_SESSION['error'] = 'Error al cambiar la contraseña';
            redirect('auth/cambiar-password-obligatorio');
        }

        // Marcar primer acceso como completado
        $usuario->marcarPrimerAccesoCompletado();

        // Actualizar sesión
        $_SESSION['user_primer_acceso'] = false;
        $_SESSION['user_password_temporal'] = false;

        writeLog("Primer acceso completado y contraseña cambiada: {$usuario->email}", 'info');

        $_SESSION['success'] = 'Contraseña actualizada correctamente';

        // Redirigir al dashboard
        redirect("{$usuario->rol}/dashboard");
    }

    /**
     * Verificar rate limiting para recuperación de contraseña
     *
     * @param string $ip Dirección IP
     * @return bool True si puede continuar, false si debe esperar
     */
    private function checkRateLimiting(string $ip): bool
    {
        $sql = "SELECT MAX(fecha_creacion) as ultima_solicitud
                FROM password_reset_tokens
                WHERE ip_address = ?";

        $result = Database::fetchOne($sql, [$ip]);

        if ($result && $result['ultima_solicitud']) {
            $tiempoTranscurrido = time() - strtotime($result['ultima_solicitud']);

            if ($tiempoTranscurrido < PASSWORD_RESET_RATE_LIMIT) {
                return false;
            }
        }

        return true;
    }

    /**
     * Incrementar intentos fallidos de validación de código
     *
     * @param string $email Email
     * @param string $codigo Código intentado
     */
    private function incrementarIntentosValidacion(string $email, string $codigo): void
    {
        $sql = "UPDATE password_reset_tokens
                SET intentos_validacion = intentos_validacion + 1
                WHERE email = ? AND codigo = ?";

        Database::execute($sql, [$email, $codigo]);

        // Si alcanza el máximo, invalidar token
        $sql = "UPDATE password_reset_tokens
                SET usado = TRUE
                WHERE email = ?
                  AND intentos_validacion >= ?";

        Database::execute($sql, [$email, PASSWORD_RESET_MAX_ATTEMPTS]);
    }
}
