<?php
/**
 * IMPLEMENTACIÓN: Registro de Usuarios con Google OAuth
 *
 * Opción A: Registro híbrido (Google OAuth + Formulario tradicional)
 *
 * Esta implementación permite:
 * - Login con Google OAuth
 * - Registro simplificado con formulario
 * - Sistema de solicitudes para aprobación
 * - Listas desplegables para datos de apartamento
 *
 * PASOS DE IMPLEMENTACIÓN:
 * 1. Instalar dependencias
 * 2. Configurar Google OAuth
 * 3. Actualizar base de datos
 * 4. Modificar AuthController
 * 5. Crear vistas
 * 6. Actualizar sistema de solicitudes
 */

echo "=== IMPLEMENTACIÓN REGISTRO GOOGLE OAUTH ===\n\n";

// ============================================================================
// PASO 1: INSTALAR DEPENDENCIAS
// ============================================================================

echo "PASO 1: Instalando dependencias...\n";
echo "Ejecutar: composer require google/apiclient\n\n";

// ============================================================================
// PASO 2: CONFIGURACIÓN GOOGLE OAUTH
// ============================================================================

echo "PASO 2: Configuración Google OAuth\n";
echo "Agregar a config/config.php:\n\n";

$config_oauth = '
<?php
// ... existing config ...

// Google OAuth Configuration
define(\'GOOGLE_CLIENT_ID\', \'TU_GOOGLE_CLIENT_ID_AQUI\');
define(\'GOOGLE_CLIENT_SECRET\', \'TU_GOOGLE_CLIENT_SECRET_AQUI\');
define(\'GOOGLE_REDIRECT_URI\', url(\'auth/google-callback\'));

// OAuth Scopes
define(\'GOOGLE_SCOPES\', [
    \'https://www.googleapis.com/auth/userinfo.email\',
    \'https://www.googleapis.com/auth/userinfo.profile\'
]);
';

echo $config_oauth . "\n\n";

// ============================================================================
// PASO 3: ACTUALIZAR BASE DE DATOS
// ============================================================================

echo "PASO 3: Actualizar base de datos\n";
echo "Ejecutar las siguientes consultas SQL:\n\n";

$sql_updates = '
// 1. Agregar tipo de solicitud para registro de nuevos usuarios
ALTER TABLE solicitudes_cambios
ADD COLUMN IF NOT EXISTS tipo_solicitud
ENUM(\'registro_nuevo_usuario\', \'cambio_cantidad_controles\', \'bloqueo_control\', \'transferencia_control\', \'otro\')
DEFAULT \'cambio_cantidad_controles\';

// 2. Agregar campos para datos del nuevo usuario en solicitudes
ALTER TABLE solicitudes_cambios
ADD COLUMN IF NOT EXISTS datos_nuevo_usuario JSON NULL;

// 3. Crear tabla para almacenar tokens OAuth temporales
CREATE TABLE IF NOT EXISTS oauth_temp_tokens (
    id INT PRIMARY KEY AUTO_INCREMENT,
    google_id VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    nombre_completo VARCHAR(255) NOT NULL,
    google_token TEXT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    usado BOOLEAN DEFAULT FALSE,
    INDEX idx_google_id (google_id),
    INDEX idx_email (email)
);

// 4. Agregar campos OAuth al usuario
ALTER TABLE usuarios
ADD COLUMN IF NOT EXISTS google_id VARCHAR(255) NULL UNIQUE,
ADD COLUMN IF NOT EXISTS google_token TEXT NULL,
ADD COLUMN IF NOT EXISTS fecha_vinculacion_google TIMESTAMP NULL;
';

echo $sql_updates . "\n\n";

// ============================================================================
// PASO 4: MODIFICAR AUTHCONTROLLER
// ============================================================================

echo "PASO 4: Modificar AuthController\n";
echo "Agregar estos métodos a app/controllers/AuthController.php:\n\n";

$auth_methods = '
<?php
// ... existing AuthController code ...

class AuthController
{
    // ... existing methods ...

    /**
     * Iniciar login con Google OAuth
     */
    public function googleLogin(): void
    {
        $client = new Google_Client();
        $client->setClientId(GOOGLE_CLIENT_ID);
        $client->setClientSecret(GOOGLE_CLIENT_SECRET);
        $client->setRedirectUri(GOOGLE_REDIRECT_URI);
        $client->setScopes(GOOGLE_SCOPES);
        $client->setAccessType(\'offline\');
        $client->setPrompt(\'consent\');

        $authUrl = $client->createAuthUrl();

        header(\'Location: \' . $authUrl);
        exit;
    }

    /**
     * Callback de Google OAuth
     */
    public function googleCallback(): void
    {
        try {
            $client = new Google_Client();
            $client->setClientId(GOOGLE_CLIENT_ID);
            $client->setClientSecret(GOOGLE_CLIENT_SECRET);
            $client->setRedirectUri(GOOGLE_REDIRECT_URI);

            if (isset($_GET[\'code\'])) {
                $token = $client->fetchAccessTokenWithAuthCode($_GET[\'code\']);
                $client->setAccessToken($token);

                $oauth = new Google_Service_Oauth2($client);
                $googleUser = $oauth->userinfo->get();

                // Verificar si el usuario ya existe
                $usuario = Usuario::findByEmail($googleUser->email);

                if ($usuario) {
                    // Usuario existe - verificar si ya está vinculado con Google
                    if ($usuario->google_id) {
                        // Login normal
                        $this->doLogin($usuario);
                    } else {
                        // Vincular cuenta existente con Google
                        $this->vincularCuentaGoogle($usuario, $googleUser, $token);
                    }
                } else {
                    // Usuario nuevo - mostrar formulario de registro
                    $this->mostrarFormularioRegistro($googleUser, $token);
                }
            } else {
                throw new Exception(\'Código de autorización no recibido\');
            }

        } catch (Exception $e) {
            writeLog("Error Google OAuth: " . $e->getMessage(), \'error\');
            $_SESSION[\'error\'] = \'Error al procesar login con Google. Intente nuevamente.\';
            redirect(\'auth/login\');
        }
    }

    /**
     * Mostrar formulario de registro para nuevos usuarios
     */
    private function mostrarFormularioRegistro($googleUser, $token): void
    {
        // Guardar datos temporales
        $tempTokenId = $this->guardarTokenTemporal($googleUser, $token);

        // Obtener datos para los dropdowns
        $bloques = $this->getBloquesDisponibles();
        $escaleras = $this->getEscalerasDisponibles();
        $pisos = $this->getPisosDisponibles();

        require_once __DIR__ . \'/../views/auth/registro_google.php\';
    }

    /**
     * Procesar registro de nuevo usuario
     */
    public function processRegistroGoogle(): void
    {
        if ($_SERVER[\'REQUEST_METHOD\'] !== \'POST\') {
            redirect(\'auth/login\');
        }

        // Validar CSRF
        if (!ValidationHelper::validateCSRFToken($_POST[\'csrf_token\'] ?? \'\')) {
            $_SESSION[\'error\'] = \'Token de seguridad inválido\';
            redirect(\'auth/login\');
        }

        // Validar token temporal
        $tempTokenId = $_POST[\'temp_token_id\'] ?? \'\';
        $tempData = $this->getTokenTemporal($tempTokenId);

        if (!$tempData) {
            $_SESSION[\'error\'] = \'Sesión expirada. Intente nuevamente.\';
            redirect(\'auth/login\');
        }

        // Validar datos del formulario
        $errores = $this->validarDatosRegistro($_POST);

        if (!empty($errores)) {
            $_SESSION[\'error\'] = implode(\'<br>\', $errores);
            $_SESSION[\'form_data\'] = $_POST;
            redirect(\'auth/registro-google?token=\' . $tempTokenId);
        }

        // Crear solicitud de registro
        $this->crearSolicitudRegistro($tempData, $_POST);

        // Marcar token como usado
        $this->marcarTokenUsado($tempTokenId);

        $_SESSION[\'success\'] = \'¡Solicitud de registro enviada exitosamente! Será revisada por un administrador en las próximas 24-48 horas.\';
        redirect(\'auth/login\');
    }

    /**
     * Vincular cuenta existente con Google
     */
    private function vincularCuentaGoogle($usuario, $googleUser, $token): void
    {
        try {
            $usuario->update([
                \'google_id\' => $googleUser->id,
                \'google_token\' => json_encode($token),
                \'fecha_vinculacion_google\' => date(\'Y-m-d H:i:s\')
            ]);

            writeLog("Cuenta vinculada con Google: {$usuario->email}", \'info\');
            $this->doLogin($usuario);

        } catch (Exception $e) {
            writeLog("Error vinculando cuenta Google: " . $e->getMessage(), \'error\');
            $_SESSION[\'error\'] = \'Error al vincular cuenta con Google.\';
            redirect(\'auth/login\');
        }
    }

    /**
     * Realizar login del usuario
     */
    private function doLogin($usuario): void
    {
        $_SESSION[\'user_id\'] = $usuario->id;
        $_SESSION[\'user_nombre\'] = $usuario->nombre_completo;
        $_SESSION[\'user_email\'] = $usuario->email;
        $_SESSION[\'user_rol\'] = $usuario->rol;
        $_SESSION[\'user_primer_acceso\'] = $usuario->primer_acceso;
        $_SESSION[\'user_password_temporal\'] = $usuario->password_temporal;

        session_regenerate_id(true);

        writeLog("Login exitoso con Google: {$usuario->email}", \'info\');

        $dashboardRol = $usuario->rol === \'administrador\' ? \'admin\' : $usuario->rol;
        redirect("{$dashboardRol}/dashboard");
    }

    /**
     * Guardar token temporal para el proceso de registro
     */
    private function guardarTokenTemporal($googleUser, $token): string
    {
        $sql = "INSERT INTO oauth_temp_tokens
                (google_id, email, nombre_completo, google_token)
                VALUES (?, ?, ?, ?)";

        $id = Database::insert($sql, [
            $googleUser->id,
            $googleUser->email,
            $googleUser->name,
            json_encode($token)
        ]);

        return $id;
    }

    /**
     * Obtener datos del token temporal
     */
    private function getTokenTemporal(string $tokenId): ?array
    {
        $sql = "SELECT * FROM oauth_temp_tokens
                WHERE id = ? AND usado = FALSE
                AND fecha_creacion > DATE_SUB(NOW(), INTERVAL 30 MINUTE)";

        return Database::fetchOne($sql, [$tokenId]);
    }

    /**
     * Marcar token temporal como usado
     */
    private function marcarTokenUsado(string $tokenId): void
    {
        $sql = "UPDATE oauth_temp_tokens SET usado = TRUE WHERE id = ?";
        Database::execute($sql, [$tokenId]);
    }

    /**
     * Validar datos del formulario de registro
     */
    private function validarDatosRegistro(array $data): array
    {
        $errores = [];

        // Nombre y apellido
        if (empty(trim($data[\'nombre\'] ?? \'\'))) {
            $errores[] = \'El nombre es obligatorio\';
        }

        if (empty(trim($data[\'apellido\'] ?? \'\'))) {
            $errores[] = \'El apellido es obligatorio\';
        }

        // Teléfono
        if (empty(trim($data[\'telefono\'] ?? \'\'))) {
            $errores[] = \'El teléfono es obligatorio\';
        } elseif (!ValidationHelper::validatePhone($data[\'telefono\'])) {
            $errores[] = \'Formato de teléfono inválido\';
        }

        // Apartamento
        if (empty($data[\'bloque\'] ?? \'\')) {
            $errores[] = \'Debe seleccionar un bloque\';
        }

        if (empty($data[\'escalera\'] ?? \'\')) {
            $errores[] = \'Debe seleccionar una escalera\';
        }

        if (empty($data[\'piso\'] ?? \'\')) {
            $errores[] = \'Debe seleccionar un piso\';
        }

        if (empty($data[\'apartamento\'] ?? \'\')) {
            $errores[] = \'Debe seleccionar un apartamento\';
        }

        // Verificar que la combinación bloque-escalera-piso-apartamento existe
        if (!empty($data[\'bloque\']) && !empty($data[\'escalera\']) && !empty($data[\'piso\']) && !empty($data[\'apartamento\'])) {
            if (!$this->verificarApartamentoExiste($data[\'bloque\'], $data[\'escalera\'], $data[\'piso\'], $data[\'apartamento\'])) {
                $errores[] = \'La combinación de apartamento seleccionada no existe\';
            }
        }

        // Cantidad de controles
        $controles = (int)($data[\'cantidad_controles\'] ?? 0);
        if ($controles < 1 || $controles > 5) {
            $errores[] = \'La cantidad de controles debe estar entre 1 y 5\';
        }

        return $errores;
    }

    /**
     * Crear solicitud de registro
     */
    private function crearSolicitudRegistro(array $tempData, array $formData): void
    {
        $datosUsuario = [
            \'google_id\' => $tempData[\'google_id\'],
            \'email\' => $tempData[\'email\'],
            \'nombre_completo\' => trim($formData[\'nombre\'] . \' \' . $formData[\'apellido\']),
            \'telefono\' => trim($formData[\'telefono\']),
            \'bloque\' => $formData[\'bloque\'],
            \'escalera\' => $formData[\'escalera\'],
            \'piso\' => $formData[\'piso\'],
            \'apartamento\' => $formData[\'apartamento\'],
            \'cantidad_controles\' => (int)$formData[\'cantidad_controles\'],
            \'comentarios\' => trim($formData[\'comentarios\'] ?? \'\'),
            \'google_token\' => $tempData[\'google_token\']
        ];

        $sql = "INSERT INTO solicitudes_cambios
                (tipo_solicitud, datos_nuevo_usuario, estado, fecha_creacion)
                VALUES (\'registro_nuevo_usuario\', ?, \'pendiente\', NOW())";

        Database::execute($sql, [json_encode($datosUsuario)]);

        writeLog("Nueva solicitud de registro creada: {$tempData[\'email\']}", \'info\');
    }

    /**
     * Verificar que un apartamento existe
     */
    private function verificarApartamentoExiste(string $bloque, string $escalera, string $piso, string $apartamento): bool
    {
        $sql = "SELECT COUNT(*) as total FROM apartamentos
                WHERE bloque = ? AND escalera = ? AND numero_piso = ? AND numero_apartamento = ?";

        $result = Database::fetchOne($sql, [$bloque, $escalera, $piso, $apartamento]);
        return ($result[\'total\'] ?? 0) > 0;
    }

    /**
     * Obtener bloques disponibles
     */
    private function getBloquesDisponibles(): array
    {
        $sql = "SELECT DISTINCT bloque FROM apartamentos ORDER BY bloque";
        $results = Database::fetchAll($sql);
        return array_column($results, \'bloque\');
    }

    /**
     * Obtener escaleras disponibles
     */
    private function getEscalerasDisponibles(): array
    {
        $sql = "SELECT DISTINCT escalera FROM apartamentos ORDER BY escalera";
        $results = Database::fetchAll($sql);
        return array_column($results, \'escalera\');
    }

    /**
     * Obtener pisos disponibles
     */
    private function getPisosDisponibles(): array
    {
        $sql = "SELECT DISTINCT numero_piso FROM apartamentos ORDER BY numero_piso";
        $results = Database::fetchAll($sql);
        return array_column($results, \'numero_piso\');
    }
}
';

echo $auth_methods . "\n\n";

// ============================================================================
// PASO 5: CREAR VISTA DE REGISTRO
// ============================================================================

echo "PASO 5: Crear vista de registro\n";
echo "Crear archivo: app/views/auth/registro_google.php\n\n";

$registro_view = '
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Completar Registro - <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: #2563eb;
            --primary-dark: #1e40af;
            --secondary-color: #64748b;
            --success-color: #10b981;
            --danger-color: #ef4444;
            --bg-light: #f8fafc;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, sans-serif;
        }

        .registro-container {
            width: 100%;
            max-width: 600px;
            padding: 20px;
        }

        .registro-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 40px;
            animation: slideUp 0.5s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .registro-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .registro-header .logo {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            box-shadow: 0 10px 30px rgba(37, 99, 235, 0.3);
        }

        .registro-header .logo i {
            font-size: 28px;
            color: white;
        }

        .registro-header h1 {
            font-size: 24px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 8px;
        }

        .registro-header p {
            color: var(--secondary-color);
            font-size: 14px;
            margin: 0;
        }

        .form-floating {
            margin-bottom: 20px;
        }

        .form-control, .form-select {
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px 16px;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            border: none;
            border-radius: 8px;
            padding: 14px;
            font-weight: 600;
            font-size: 16px;
            width: 100%;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(37, 99, 235, 0.4);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .alert {
            border-radius: 8px;
            border: none;
            padding: 12px 16px;
            margin-bottom: 20px;
            animation: slideDown 0.3s ease-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .row {
            margin-bottom: 15px;
        }

        .form-text {
            font-size: 12px;
            color: var(--secondary-color);
        }

        .progress-container {
            margin-bottom: 20px;
        }

        .progress {
            height: 6px;
            border-radius: 3px;
        }
    </style>
</head>
<body>
    <div class="registro-container">
        <div class="registro-card">
            <div class="registro-header">
                <div class="logo">
                    <i class="bi bi-person-plus-fill"></i>
                </div>
                <h1>Completar Registro</h1>
                <p>Complete sus datos para finalizar el registro</p>
            </div>

            <div class="progress-container">
                <div class="progress">
                    <div class="progress-bar bg-primary" role="progressbar" style="width: 100%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <small class="text-muted mt-1 d-block">Paso final: Información personal</small>
            </div>

            <?php if (isset($_SESSION[\'error\'])): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-circle"></i>
                    <?= $_SESSION[\'error\'] ?>
                </div>
                <?php unset($_SESSION[\'error\']); ?>
            <?php endif; ?>

            <form action="<?= url(\'auth/process-registro-google\') ?>" method="POST" id="registroForm">
                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                <input type="hidden" name="temp_token_id" value="<?= $tempData[\'id\'] ?? \'\' ?>">

                <!-- Información Personal -->
                <h5 class="mb-3"><i class="bi bi-person"></i> Información Personal</h5>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="text"
                                   class="form-control"
                                   id="nombre"
                                   name="nombre"
                                   placeholder="Nombre"
                                   required
                                   value="<?= $_SESSION[\'form_data\'][\'nombre\'] ?? \'\' ?>">
                            <label for="nombre">
                                <i class="bi bi-person"></i> Nombre *
                            </label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="text"
                                   class="form-control"
                                   id="apellido"
                                   name="apellido"
                                   placeholder="Apellido"
                                   required
                                   value="<?= $_SESSION[\'form_data\'][\'apellido\'] ?? \'\' ?>">
                            <label for="apellido">
                                <i class="bi bi-person"></i> Apellido *
                            </label>
                        </div>
                    </div>
                </div>

                <div class="form-floating">
                    <input type="tel"
                           class="form-control"
                           id="telefono"
                           name="telefono"
                           placeholder="Teléfono"
                           required
                           value="<?= $_SESSION[\'form_data\'][\'telefono\'] ?? \'\' ?>">
                    <label for="telefono">
                        <i class="bi bi-telephone"></i> Teléfono *
                    </label>
                    <div class="form-text">Ejemplo: 04141234567</div>
                </div>

                <!-- Información del Apartamento -->
                <h5 class="mb-3 mt-4"><i class="bi bi-house"></i> Información del Apartamento</h5>

                <div class="row">
                    <div class="col-md-3">
                        <div class="form-floating">
                            <select class="form-select" id="bloque" name="bloque" required>
                                <option value="">Seleccionar...</option>
                                <?php foreach ($bloques as $bloque): ?>
                                    <option value="<?= $bloque ?>" <?= ($_SESSION[\'form_data\'][\'bloque\'] ?? \'\') === $bloque ? \'selected\' : \'\' ?>>
                                        <?= $bloque ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <label for="bloque">
                                <i class="bi bi-building"></i> Bloque *
                            </label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-floating">
                            <select class="form-select" id="escalera" name="escalera" required>
                                <option value="">Seleccionar...</option>
                                <?php foreach ($escaleras as $escalera): ?>
                                    <option value="<?= $escalera ?>" <?= ($_SESSION[\'form_data\'][\'escalera\'] ?? \'\') === $escalera ? \'selected\' : \'\' ?>>
                                        <?= $escalera ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <label for="escalera">
                                <i class="bi bi-signpost"></i> Escalera *
                            </label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-floating">
                            <select class="form-select" id="piso" name="piso" required>
                                <option value="">Seleccionar...</option>
                                <?php foreach ($pisos as $piso): ?>
                                    <option value="<?= $piso ?>" <?= ($_SESSION[\'form_data\'][\'piso\'] ?? \'\') === $piso ? \'selected\' : \'\' ?>>
                                        <?= $piso ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <label for="piso">
                                <i class="bi bi-chevron-up"></i> Piso *
                            </label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-floating">
                            <select class="form-select" id="apartamento" name="apartamento" required>
                                <option value="">Seleccionar...</option>
                                <!-- Se cargará dinámicamente con JavaScript -->
                            </select>
                            <label for="apartamento">
                                <i class="bi bi-house-door"></i> Apto *
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Controles de Estacionamiento -->
                <h5 class="mb-3 mt-4"><i class="bi bi-car-front"></i> Controles de Estacionamiento</h5>

                <div class="form-floating">
                    <select class="form-select" id="cantidad_controles" name="cantidad_controles" required>
                        <option value="">Seleccionar...</option>
                        <option value="1" <?= ($_SESSION[\'form_data\'][\'cantidad_controles\'] ?? \'\') === \'1\' ? \'selected\' : \'\' ?>>1 Control</option>
                        <option value="2" <?= ($_SESSION[\'form_data\'][\'cantidad_controles\'] ?? \'\') === \'2\' ? \'selected\' : \'\' ?>>2 Controles</option>
                        <option value="3" <?= ($_SESSION[\'form_data\'][\'cantidad_controles\'] ?? \'\') === \'3\' ? \'selected\' : \'\' ?>>3 Controles</option>
                        <option value="4" <?= ($_SESSION[\'form_data\'][\'cantidad_controles\'] ?? \'\') === \'4\' ? \'selected\' : \'\' ?>>4 Controles</option>
                        <option value="5" <?= ($_SESSION[\'form_data\'][\'cantidad_controles\'] ?? \'\') === \'5\' ? \'selected\' : \'\' ?>>5 Controles</option>
                    </select>
                    <label for="cantidad_controles">
                        <i class="bi bi-hash"></i> Cantidad de Controles Activos *
                    </label>
                    <div class="form-text">Número de controles de estacionamiento que necesita</div>
                </div>

                <!-- Comentarios Adicionales -->
                <div class="form-floating">
                    <textarea class="form-control"
                              id="comentarios"
                              name="comentarios"
                              placeholder="Comentarios adicionales (opcional)"
                              style="height: 80px; resize: vertical;"><?= $_SESSION[\'form_data\'][\'comentarios\'] ?? \'\' ?></textarea>
                    <label for="comentarios">
                        <i class="bi bi-chat-dots"></i> Comentarios Adicionales
                    </label>
                    <div class="form-text">Información adicional que considere importante (opcional)</div>
                </div>

                <button type="submit" class="btn btn-primary mt-4" id="submitBtn">
                    <span id="btnText">
                        <i class="bi bi-send"></i> Enviar Solicitud de Registro
                    </span>
                    <span id="btnSpinner" style="display: none;">
                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        Enviando...
                    </span>
                </button>
            </form>

            <div class="text-center mt-3">
                <small class="text-muted">
                    <i class="bi bi-info-circle"></i>
                    Su solicitud será revisada por un administrador en 24-48 horas
                </small>
            </div>
        </div>

        <div class="text-center mt-4">
            <p style="color: white; font-size: 14px;">
                ¿Ya tiene cuenta? <a href="<?= url(\'auth/login\') ?>" style="color: white; text-decoration: underline;">Iniciar Sesión</a>
            </p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Cargar apartamentos dinámicamente
        function cargarApartamentos() {
            const bloque = document.getElementById(\'bloque\').value;
            const escalera = document.getElementById(\'escalera\').value;
            const piso = document.getElementById(\'piso\').value;
            const apartamentoSelect = document.getElementById(\'apartamento\');

            if (bloque && escalera && piso) {
                fetch(`<?= url(\'api/apartamentos\') ?>?bloque=${bloque}&escalera=${escalera}&piso=${piso}`)
                    .then(response => response.json())
                    .then(data => {
                        apartamentoSelect.innerHTML = \'<option value="">Seleccionar...</option>\';
                        data.forEach(apto => {
                            const option = document.createElement(\'option\');
                            option.value = apto.numero_apartamento;
                            option.textContent = apto.numero_apartamento;
                            apartamentoSelect.appendChild(option);
                        });
                    })
                    .catch(error => {
                        console.error(\'Error cargando apartamentos:\', error);
                    });
            } else {
                apartamentoSelect.innerHTML = \'<option value="">Seleccionar...</option>\';
            }
        }

        // Event listeners para cargar apartamentos
        document.getElementById(\'bloque\').addEventListener(\'change\', cargarApartamentos);
        document.getElementById(\'escalera\').addEventListener(\'change\', cargarApartamentos);
        document.getElementById(\'piso\').addEventListener(\'change\', cargarApartamentos);

        // Loading state on submit
        document.getElementById(\'registroForm\').addEventListener(\'submit\', function() {
            const btn = document.getElementById(\'submitBtn\');
            const btnText = document.getElementById(\'btnText\');
            const btnSpinner = document.getElementById(\'btnSpinner\');

            btn.disabled = true;
            btnText.style.display = \'none\';
            btnSpinner.style.display = \'inline-block\';
        });

        // Limpiar datos de formulario de la sesión
        <?php unset($_SESSION[\'form_data\']); ?>
    </script>
</body>
</html>
';

echo $registro_view . "\n\n";

// ============================================================================
// PASO 6: ACTUALIZAR VISTA DE LOGIN
// ============================================================================

echo "PASO 6: Actualizar vista de login\n";
echo "Modificar app/views/auth/login.php - Agregar botón de Google:\n\n";

$login_update = '
// Agregar después del botón "Iniciar Sesión":

<div class="text-center mt-3">
    <div class="divider">
        <span class="divider-text">o</span>
    </div>
</div>

<a href="<?= url(\'auth/google-login\') ?>" class="btn btn-outline-light mt-3 w-100">
    <i class="bi bi-google"></i> Continuar con Google
</a>

<div class="text-center mt-3">
    <small class="text-muted">
        Al registrarte, aceptas nuestros términos y condiciones
    </small>
</div>

// Agregar estilos CSS adicionales:
.divider {
    position: relative;
    margin: 20px 0;
}

.divider::before {
    content: \'\';
    position: absolute;
    top: 50%;
    left: 0;
    right: 0;
    height: 1px;
    background: rgba(255, 255, 255, 0.3);
}

.divider-text {
    background: rgba(255, 255, 255, 0.1);
    padding: 0 15px;
    color: rgba(255, 255, 255, 0.8);
    font-size: 14px;
    backdrop-filter: blur(10px);
    border-radius: 20px;
}
';

echo $login_update . "\n\n";

// ============================================================================
// PASO 7: ACTUALIZAR SISTEMA DE SOLICITUDES
// ============================================================================

echo "PASO 7: Actualizar sistema de solicitudes\n";
echo "Modificar AdminController y OperadorController para manejar solicitudes de registro:\n\n";

$solicitudes_update = '
<?php
// En AdminController.php y OperadorController.php

/**
 * Procesar solicitud de registro de nuevo usuario
 */
public function processSolicitudRegistro(): void
{
    if ($_SERVER[\'REQUEST_METHOD\'] !== \'POST\') {
        redirect(\'admin/solicitudes\');
        return;
    }

    // Validar permisos
    if (!$this->hasPermission(\'aprobar_solicitudes\')) {
        $_SESSION[\'error\'] = \'No tiene permisos para procesar solicitudes\';
        redirect(\'admin/solicitudes\');
        return;
    }

    // Validar CSRF
    if (!ValidationHelper::validateCSRFToken($_POST[\'csrf_token\'] ?? \'\')) {
        $_SESSION[\'error\'] = \'Token de seguridad inválido\';
        redirect(\'admin/solicitudes\');
        return;
    }

    $solicitudId = (int)($_POST[\'solicitud_id\'] ?? 0);
    $accion = $_POST[\'accion\'] ?? \'\';
    $respuesta = trim($_POST[\'respuesta\'] ?? \'\');

    if (!$solicitudId || !in_array($accion, [\'aprobar\', \'rechazar\'])) {
        $_SESSION[\'error\'] = \'Datos inválidos\';
        redirect(\'admin/solicitudes\');
        return;
    }

    try {
        // Obtener solicitud
        $sql = "SELECT * FROM solicitudes_cambios WHERE id = ? AND tipo_solicitud = \'registro_nuevo_usuario\'";
        $solicitud = Database::fetchOne($sql, [$solicitudId]);

        if (!$solicitud) {
            $_SESSION[\'error\'] = \'Solicitud no encontrada\';
            redirect(\'admin/solicitudes\');
            return;
        }

        if ($solicitud[\'estado\'] !== \'pendiente\') {
            $_SESSION[\'error\'] = \'Esta solicitud ya fue procesada\';
            redirect(\'admin/solicitudes\');
            return;
        }

        $datosUsuario = json_decode($solicitud[\'datos_nuevo_usuario\'], true);

        if ($accion === \'aprobar\') {
            // Crear el usuario
            $this->aprobarSolicitudRegistro($solicitudId, $datosUsuario, $respuesta);
            $_SESSION[\'success\'] = \'Usuario registrado exitosamente. Se ha enviado un email de confirmación.\';
        } else {
            // Rechazar solicitud
            $this->rechazarSolicitudRegistro($solicitudId, $respuesta);
            $_SESSION[\'success\'] = \'Solicitud rechazada correctamente.\';
        }

    } catch (Exception $e) {
        writeLog("Error procesando solicitud de registro: " . $e->getMessage(), \'error\');
        $_SESSION[\'error\'] = \'Error al procesar la solicitud. Intente nuevamente.\';
    }

    redirect(\'admin/solicitudes\');
}

/**
 * Aprobar solicitud de registro
 */
private function aprobarSolicitudRegistro(int $solicitudId, array $datosUsuario, string $respuesta = \'\'): void
{
    // Generar contraseña temporal
    $passwordTemporal = $this->generarPasswordTemporal();

    // Crear usuario
    $usuarioId = Usuario::create([
        \'nombre_completo\' => $datosUsuario[\'nombre_completo\'],
        \'email\' => $datosUsuario[\'email\'],
        \'password\' => $passwordTemporal,
        \'telefono\' => $datosUsuario[\'telefono\'],
        \'rol\' => \'cliente\',
        \'google_id\' => $datosUsuario[\'google_id\'],
        \'google_token\' => $datosUsuario[\'google_token\'],
        \'fecha_vinculacion_google\' => date(\'Y-m-d H:i:s\'),
        \'primer_acceso\' => true,
        \'password_temporal\' => true,
        \'perfil_completo\' => false
    ]);

    // Asignar apartamento
    $this->asignarApartamentoUsuario($usuarioId, $datosUsuario);

    // Asignar controles de estacionamiento
    $this->asignarControlesEstacionamiento($usuarioId, $datosUsuario[\'cantidad_controles\']);

    // Actualizar solicitud
    $sql = "UPDATE solicitudes_cambios
            SET estado = \'aprobada\', respuesta_admin = ?, fecha_procesamiento = NOW()
            WHERE id = ?";
    Database::execute($sql, [$respuesta, $solicitudId]);

    // Enviar email de bienvenida
    $this->enviarEmailBienvenida($datosUsuario[\'email\'], $datosUsuario[\'nombre_completo\'], $passwordTemporal);

    writeLog("Usuario registrado desde solicitud #{$solicitudId}: {$datosUsuario[\'email\']}", \'info\');
}

/**
 * Rechazar solicitud de registro
 */
private function rechazarSolicitudRegistro(int $solicitudId, string $respuesta): void
{
    $sql = "UPDATE solicitudes_cambios
            SET estado = \'rechazada\', respuesta_admin = ?, fecha_procesamiento = NOW()
            WHERE id = ?";
    Database::execute($sql, [$respuesta, $solicitudId]);

    writeLog("Solicitud de registro rechazada #{$solicitudId}", \'info\');
}

/**
 * Generar contraseña temporal
 */
private function generarPasswordTemporal(): string
{
    return bin2hex(random_bytes(8)); // 16 caracteres hexadecimales
}

/**
 * Asignar apartamento al usuario
 */
private function asignarApartamentoUsuario(int $usuarioId, array $datosUsuario): void
{
    // Buscar apartamento
    $sql = "SELECT id FROM apartamentos
            WHERE bloque = ? AND escalera = ? AND numero_piso = ? AND numero_apartamento = ?";
    $apartamento = Database::fetchOne($sql, [
        $datosUsuario[\'bloque\'],
        $datosUsuario[\'escalera\'],
        $datosUsuario[\'piso\'],
        $datosUsuario[\'apartamento\']
    ]);

    if ($apartamento) {
        $sql = "INSERT INTO apartamento_usuario (usuario_id, apartamento_id, activo, fecha_asignacion)
                VALUES (?, ?, TRUE, NOW())";
        Database::execute($sql, [$usuarioId, $apartamento[\'id\']]);
    }
}

/**
 * Asignar controles de estacionamiento
 */
private function asignarControlesEstacionamiento(int $usuarioId, int $cantidad): void
{
    // Esta lógica dependerá de cómo manejes la asignación de controles
    // Por ahora, solo registramos que necesita X controles
    writeLog("Usuario {$usuarioId} requiere {$cantidad} controles de estacionamiento", \'info\');
}

/**
 * Enviar email de bienvenida
 */
private function enviarEmailBienvenida(string $email, string $nombre, string $passwordTemporal): void
{
    $asunto = "Bienvenido a " . ESTACIONAMIENTO_NOMBRE;
    $mensaje = "
    <h2>¡Bienvenido, {$nombre}!</h2>
    <p>Su cuenta ha sido creada exitosamente.</p>
    <p><strong>Sus credenciales de acceso:</strong></p>
    <ul>
        <li><strong>Email:</strong> {$email}</li>
        <li><strong>Contraseña temporal:</strong> {$passwordTemporal}</li>
    </ul>
    <p>Por favor, inicie sesión y cambie su contraseña inmediatamente.</p>
    <p>Atentamente,<br>Equipo de " . ESTACIONAMIENTO_NOMBRE . "</p>
    ";

    MailHelper::sendHtml($email, $asunto, $mensaje);
}
';

echo $solicitudes_update . "\n\n";

// ============================================================================
// PASO 8: ACTUALIZAR VISTA DE SOLICITUDES
// ============================================================================

echo "PASO 8: Actualizar vista de solicitudes\n";
echo "Modificar app/views/operador/solicitudes.php para mostrar solicitudes de registro:\n\n";

$solicitudes_view_update = '
// Agregar en la sección de detalles de solicitud:

<?php if ($solicitud[\'tipo_solicitud\'] === \'registro_nuevo_usuario\'): ?>
    <div class="mb-3">
        <h6><i class="bi bi-person-plus"></i> Datos del Nuevo Usuario</h6>
        <?php $datosUsuario = json_decode($solicitud[\'datos_nuevo_usuario\'], true); ?>
        <div class="row">
            <div class="col-md-6">
                <p><strong>Nombre:</strong> <?= htmlspecialchars($datosUsuario[\'nombre_completo\']) ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($datosUsuario[\'email\']) ?></p>
                <p><strong>Teléfono:</strong> <?= htmlspecialchars($datosUsuario[\'telefono\']) ?></p>
            </div>
            <div class="col-md-6">
                <p><strong>Apartamento:</strong> <?= htmlspecialchars($datosUsuario[\'bloque\'] . \'-\' . $datosUsuario[\'escalera\'] . \'-\' . $datosUsuario[\'piso\'] . \'-\' . $datosUsuario[\'apartamento\']) ?></p>
                <p><strong>Controles:</strong> <?= (int)$datosUsuario[\'cantidad_controles\'] ?> control(es)</p>
                <?php if (!empty($datosUsuario[\'comentarios\'])): ?>
                    <p><strong>Comentarios:</strong> <?= htmlspecialchars($datosUsuario[\'comentarios\']) ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endif; ?>

// Agregar en el formulario de procesamiento:

<?php if ($solicitud[\'tipo_solicitud\'] === \'registro_nuevo_usuario\'): ?>
    <input type="hidden" name="tipo_solicitud" value="registro_nuevo_usuario">
<?php endif; ?>
';

echo $solicitudes_view_update . "\n\n";

// ============================================================================
// PASO 9: CREAR API PARA APARTAMENTOS
// ============================================================================

echo "PASO 9: Crear API para apartamentos\n";
echo "Crear archivo: app/controllers/ApiController.php\n\n";

$api_controller = '
<?php
/**
 * ApiController - Endpoints para AJAX
 */

class ApiController
{
    /**
     * Obtener apartamentos por bloque, escalera y piso
     */
    public function apartamentos(): void
    {
        header(\'Content-Type: application/json\');

        $bloque = $_GET[\'bloque\'] ?? \'\';
        $escalera = $_GET[\'escalera\'] ?? \'\';
        $piso = $_GET[\'piso\'] ?? \'\';

        if (empty($bloque) || empty($escalera) || empty($piso)) {
