<?php
/**
 * Test para cargar el dashboard del operador con depuración de errores
 */

// Habilitar mostrar todos los errores
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

echo "<h1>Test: Carga del Dashboard del Operador</h1>";

// Establecer sesión
session_start();
$_SESSION['user_id'] = 2;
$_SESSION['user_rol'] = 'operador';
$_SESSION['user_email'] = 'operador@estacionamiento.com';

echo "<h2>Paso 1: Cargando dependencias</h2>";

try {
    require_once __DIR__ . '/config/config.php';
    echo "<p>✅ config.php cargado</p>";
} catch (Exception $e) {
    die("<p style='color: red;'>❌ Error crítico en config.php: " . $e->getMessage() . "</p>");
}

try {
    require_once __DIR__ . '/app/models/Usuario.php';
    echo "<p>✅ Usuario.php cargado</p>";
} catch (Exception $e) {
    die("<p style='color: red;'>❌ Error en Usuario.php: " . $e->getMessage() . "</p>");
}

try {
    require_once __DIR__ . '/app/models/Pago.php';
    echo "<p>✅ Pago.php cargado</p>";
} catch (Exception $e) {
    die("<p style='color: red;'>❌ Error en Pago.php: " . $e->getMessage() . "</p>");
}

try {
    require_once __DIR__ . '/app/models/Mensualidad.php';
    echo "<p>✅ Mensualidad.php cargado</p>";
} catch (Exception $e) {
    die("<p style='color: red;'>❌ Error en Mensualidad.php: " . $e->getMessage() . "</p>");
}

try {
    require_once __DIR__ . '/app/models/Control.php';
    echo "<p>✅ Control.php cargado</p>";
} catch (Exception $e) {
    die("<p style='color: red;'>❌ Error en Control.php: " . $e->getMessage() . "</p>");
}

try {
    require_once __DIR__ . '/app/helpers/ValidationHelper.php';
    echo "<p>✅ ValidationHelper.php cargado</p>";
} catch (Exception $e) {
    die("<p style='color: red;'>❌ Error en ValidationHelper.php: " . $e->getMessage() . "</p>");
}

try {
    require_once __DIR__ . '/app/controllers/OperadorController.php';
    echo "<p>✅ OperadorController.php cargado</p>";
} catch (Exception $e) {
    die("<p style='color: red;'>❌ Error en OperadorController.php: " . $e->getMessage() . "</p>");
}

echo "<h2>Paso 2: Probando el controlador</h2>";

$controller = new OperadorController();

echo "<h3>Probando método dashboard():</h3>";

// Hacer el método dashboard público temporalmente
$reflection = new ReflectionClass($controller);
$method = $reflection->getMethod('dashboard');
$method->setAccessible(true);

echo "<p>Método dashboard hecho accesible</p>";

try {
    echo "<p>Ejecutando dashboard()...</p>";
    ob_start();
    $method->invoke($controller);
    $output = ob_get_clean();

    if ($output) {
        echo "<p style='color: green;'>✅ Dashboard ejecutado con éxito</p>";
        echo "<h3>Salida generada:</h3>";
        echo "<div style='border: 2px solid #ccc; padding: 15px; background: #f0f0f0; max-height: 500px; overflow-y: auto;'>";
        echo "<pre style='white-space: pre-wrap; word-wrap: break-word;'>";
        echo htmlspecialchars($output);
        echo "</pre>";
        echo "</div>";

        // Verificar si hay errores PHP
        if (strpos($output, 'Fatal error') !== false || strpos($output, 'Uncaught') !== false) {
            echo "<p style='color: red;'>⚠️ Se detectaron errores PHP en la salida</p>";
        }
    } else {
        echo "<p style='color: orange;'>⚠️ El dashboard no produjo salida</p>";
    }

} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error fatal en dashboard():</p>";
    echo "<pre>" . $e->getMessage() . "\n" . $e->getTraceAsString() . "</pre>";
} catch (Error $e) {
    echo "<p style='color: red;'>❌ Error de PHP en dashboard():</p>";
    echo "<pre>" . $e->getMessage() . "\n" . $e->getTraceAsString() . "</pre>";
}

echo "<h2>Paso 3: Verificando vista del dashboard</h2>";

$vistaPath = __DIR__ . '/app/views/operador/dashboard.php';
if (file_exists($vistaPath)) {
    echo "<p style='color: green;'>✅ Vista dashboard.php existe</p>";

    // Verificar sintaxis de la vista
    $phpLint = shell_exec('php -l "' . $vistaPath . '" 2>&1');
    if (strpos($phpLint, 'No syntax errors') !== false) {
        echo "<p style='color: green;'>✅ Sintaxis PHP correcta</p>";
    } else {
        echo "<p style='color: red;'>❌ Errores de sintaxis PHP:</p>";
        echo "<pre>$phpLint</pre>";
    }

    // Buscar errores comunes en la vista
    $vistaContent = file_get_contents($vistaPath);
    $errores = [
        'count()' => 'count($variable ?? [])',
        'Undefined variable' => 'isset($variable) ? $variable : default',
        'Trying to access array offset' => 'isset($array[$key]) ? $array[$key] : default'
    ];

    foreach ($errores as $error => $solucion) {
        if (strpos($vistaContent, $error) !== false) {
            echo "<p style='color: orange;'>⚠️ Posible error: $error</p>";
            echo "<small>Sugerencia: Usar $solucion</small><br>";
        }
    }
} else {
    echo "<p style='color: red;'>❌ Vista dashboard.php no existe</p>";
}

echo "<hr>";
echo "<h2>Conclusiones:</h2>";
echo "<p>Si el dashboard del operador no funciona, el problema podría estar en:</p>";
echo "<ul>";
echo "<li>1. Variables no definidas en la vista (count, etc.)</li>";
echo "<li>2. Errores de sintaxis PHP</li>";
echo "<li>3. Dependencias faltantes</li>";
echo "<li>4. Problemas de sesión o autenticación</li>";
echo "</ul>";

echo "<p><strong>Accesos directos para probar:</strong></p>";
echo "<ul>";
echo "<li><a href='operador_direct_access.php'>Dashboard Operador (Acceso Directo)</a></li>";
echo "<li><a href='auth/login'>Login Normal</a></li>";
echo "</ul>";

// Limpiar archivo de prueba
echo "<script>";
echo "setTimeout(function() { window.location.href = 'auth/login'; }, 10000);";
echo "</script>";
?>