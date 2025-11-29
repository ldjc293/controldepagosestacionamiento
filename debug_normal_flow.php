<?php
/**
 * Script para depurar el flujo normal de login del operador
 */

echo "<h1>Debug: Flujo Normal de Login Operador</h1>";

// Limpiar sesiones anteriores
session_start();
session_destroy();
session_start();

echo "<h2>Paso 1: Verificando configuración básica</h2>";

// Verificar configuración
if (!defined('APP_URL')) {
    echo "<p style='color: red;'>❌ APP_URL no está definida</p>";
} else {
    echo "<p style='color: green;'>✅ APP_URL: " . APP_URL . "</p>";
}

// Verificar base de datos
try {
    $pdo = new PDO("mysql:host=localhost;dbname=estacionamiento_db", "root", "");
    echo "<p style='color: green;'>✅ Conexión a BD exitosa</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error de BD: " . $e->getMessage() . "</p>";
}

// Verificar usuario operador
try {
    require_once __DIR__ . '/config/database.php';
    $sql = "SELECT * FROM usuarios WHERE email = 'operador@estacionamiento.com' AND rol = 'operador'";
    $usuario = Database::fetchOne($sql);
    if ($usuario) {
        echo "<p style='color: green;'>✅ Usuario operador encontrado</p>";
        echo "<pre>ID: {$usuario['id']}, Nombre: {$usuario['nombre_completo']}, Activo: {$usuario['activo']}</pre>";
    } else {
        echo "<p style='color: red;'>❌ Usuario operador no encontrado</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error al buscar usuario: " . $e->getMessage() . "</p>";
}

echo "<h2>Paso 2: Proceso de Login Simulado</h2>";

// Simular envío de formulario POST
$_POST = [
    'email' => 'operador@estacionamiento.com',
    'password' => 'operador123',
    'csrf_token' => 'test-token'
];

// Verificar credenciales
require_once __DIR__ . '/app/models/Usuario.php';

echo "<h3>Verificando credenciales:</h3>";
$resultado = Usuario::verifyLogin($_POST['email'], $_POST['password']);

echo "<pre>";
print_r($resultado);
echo "</pre>";

if ($resultado['success']) {
    echo "<p style='color: green;'>✅ Credenciales verificadas</p>";

    // Establecer sesión
    $_SESSION['user_id'] = $resultado['user']->id;
    $_SESSION['user_rol'] = $resultado['user']->rol;
    $_SESSION['user_email'] = $resultado['user']->email;
    $_SESSION['user_nombre'] = $resultado['user']->nombre_completo;

    echo "<h3>Sesión establecida:</h3>";
    echo "<pre>";
    echo "user_id: " . $_SESSION['user_id'] . "\n";
    echo "user_rol: " . $_SESSION['user_rol'] . "\n";
    echo "user_email: " . $_SESSION['user_email'] . "\n";
    echo "</pre>";

    // Probar redirección
    echo "<h2>Paso 3: Probando redirección a dashboard</h2>";

    $dashboardUrl = url('operador/dashboard');
    echo "<p>URL de redirección: " . $dashboardUrl . "</p>";

    // Intentar redirección
    echo "<p>Intentando redirección...</p>";

    // En lugar de redirigir, mostramos lo que cargaría el dashboard
    require_once __DIR__ . '/app/controllers/OperadorController.php';

    echo "<h3>Cargando OperadorController:</h3>";
    $controller = new OperadorController();

    // Forzar los métodos a públicos temporalmente
    $reflection = new ReflectionClass($controller);
    $checkAuth = $reflection->getMethod('checkAuth');
    $checkAuth->setAccessible(true);

    $dashboard = $reflection->getMethod('dashboard');
    $dashboard->setAccessible(true);

    echo "<p>Métodos hechos accesibles</p>";

    // Probar autenticación
    $usuarioAutenticado = $checkAuth->invoke($controller);

    if ($usuarioAutenticado) {
        echo "<p style='color: green;'>✅ Autenticación del controlador exitosa</p>";
        echo "<p>Usuario autenticado: {$usuarioAutenticado->nombre_completo}</p>";

        // Probar dashboard
        echo "<h3>Cargando dashboard:</h3>";
        ob_start();
        $dashboard->invoke($controller);
        $output = ob_get_clean();

        if ($output) {
            echo "<p style='color: green;'>✅ Dashboard cargado con éxito</p>";
            echo "<h4>Salida del dashboard:</h4>";
            echo "<div style='border: 1px solid #ddd; padding: 10px; background: #f9f9f9; max-height: 400px; overflow-y: auto;'>";
            echo htmlspecialchars(substr($output, 0, 1000)) . (strlen($output) > 1000 ? '...' : '');
            echo "</div>";
        } else {
            echo "<p style='color: red;'>❌ El dashboard no produjo salida</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ Autenticación del controlador falló</p>";
    }

} else {
    echo "<p style='color: red;'>❌ Credenciales incorrectas</p>";
}

echo "<hr>";
echo "<h2>Enlaces de prueba:</h2>";
echo "<p><a href='auth/login'>Ir al login normal</a></p>";
echo "<p><a href='operador_direct_access.php'>Ir al acceso directo</a></p>";
echo "<p><a href='debug_operador.php'>Ir al debug anterior</a></p>";
?>