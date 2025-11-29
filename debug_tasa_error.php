<?php
// Capturar todos los errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Función para manejar errores
set_error_handler(function($severity, $message, $file, $line) {
    echo "<div style='background: #ffebee; border: 1px solid #f44336; padding: 10px; margin: 10px 0;'>";
    echo "<h4 style='color: #d32f2f;'>PHP Error:</h4>";
    echo "<p><strong>Message:</strong> $message</p>";
    echo "<p><strong>File:</strong> $file:$line</p>";
    echo "<h5>Stack Trace:</h5>";
    echo "<pre>" . debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS) . "</pre>";
    echo "</div>";
});

// Función para manejar excepciones
set_exception_handler(function($exception) {
    echo "<div style='background: #ffebee; border: 1px solid #f44336; padding: 10px; margin: 10px 0;'>";
    echo "<h4 style='color: #d32f2f;'>Uncaught Exception:</h4>";
    echo "<p><strong>Message:</strong> " . $exception->getMessage() . "</p>";
    echo "<p><strong>File:</strong> " . $exception->getFile() . ":" . $exception->getLine() . "</p>";
    echo "<h5>Stack Trace:</h5>";
    echo "<pre>" . $exception->getTraceAsString() . "</pre>";
    echo "</div>";
});

echo "<h1>Debug - Captura de Error 'tasa'</h1>";

try {
    // Iniciar sesión
    session_start();

    // Cargar configuración
    require_once __DIR__ . '/config/config.php';
    require_once __DIR__ . '/config/database.php';

    echo "<p style='color: green;'>✓ Configuración cargada</p>";

    // Buscar cliente
    $usuarios = Database::fetchAll("SELECT id, nombre_completo FROM usuarios WHERE rol = 'cliente' AND activo = 1 LIMIT 1");

    if ($usuarios) {
        $usuarioId = $usuarios[0]['id'];
        $_SESSION['user_id'] = $usuarioId;
        $_SESSION['user_rol'] = 'cliente';

        echo "<p style='color: green;'>✓ Sesión establecida para usuario ID: $usuarioId</p>";

        // Cargar modelos
        require_once __DIR__ . '/app/models/Usuario.php';
        require_once __DIR__ . '/app/models/Mensualidad.php';
        require_once __DIR__ . '/app/models/Pago.php';

        echo "<p style='color: green;'>✓ Modelos cargados</p>";

        // Probar cada método paso a paso
        echo "<h3>Paso 1: Probar Usuario::findById</h3>";
        $usuario = Usuario::findById($usuarioId);
        echo "<p style='color: green;'>✓ Usuario::findById funcionó</p>";

        echo "<h3>Paso 2: Probar Mensualidad::getAllByUsuario</h3>";
        $mensualidades = Mensualidad::getAllByUsuario($usuarioId);
        echo "<p style='color: green;'>✓ Mensualidad::getAllByUsuario funcionó: " . count($mensualidades) . " resultados</p>";

        echo "<h3>Paso 3: Probar Pago::getByUsuario</h3>";
        $pagos = Pago::getByUsuario($usuarioId);
        echo "<p style='color: green;'>✓ Pago::getByUsuario funcionó: " . count($pagos) . " resultados</p>";

        echo "<h3>Paso 4: Probar Mensualidad::calcularDeudaTotal</h3>";
        $deudaInfo = Mensualidad::calcularDeudaTotal($usuarioId);
        echo "<p style='color: green;'>✓ Mensualidad::calcularDeudaTotal funcionó</p>";

        echo "<h3>✅ Todos los métodos funcionaron correctamente</h3>";
        echo "<p><a href='cliente/estado-cuenta' class='btn btn-primary'>Ir a Estado de Cuenta</a></p>";

    } else {
        echo "<p style='color: orange;'>⚠️ No hay clientes en la base de datos</p>";
    }

} catch (Exception $e) {
    echo "<div style='background: #ffebee; border: 1px solid #f44336; padding: 10px; margin: 10px 0;'>";
    echo "<h4 style='color: #d32f2f;'>Exception Capturada:</h4>";
    echo "<p><strong>Type:</strong> " . get_class($e) . "</p>";
    echo "<p><strong>Message:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>File:</strong> " . $e->getFile() . ":" . $e->getLine() . "</p>";
    echo "<h5>Stack Trace:</h5>";
    echo "<pre style='background: #f5f5f5; padding: 10px;'>" . $e->getTraceAsString() . "</pre>";
    echo "</div>";
}
?>