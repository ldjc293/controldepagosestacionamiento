<?php
/**
 * Test completo del proceso de login
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Test de Login Completo</h1>";

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/app/models/Usuario.php';

echo "<h2>1. Verificar usuario en BD</h2>";

$email = 'admin@estacionamiento.com';
$password = 'Admin123!';

$admin = Database::fetchOne(
    "SELECT * FROM usuarios WHERE email = ?",
    [$email]
);

if ($admin) {
    echo "✓ Usuario encontrado<br>";
    echo "Email: {$admin['email']}<br>";
    echo "Rol: {$admin['rol']}<br>";
    echo "Activo: " . ($admin['activo'] ? 'Sí' : 'No') . "<br>";

    // Verificar password
    if (password_verify($password, $admin['password'])) {
        echo "<strong style='color:green'>✓ Password correcta</strong><br>";
    } else {
        echo "<strong style='color:red'>✗ Password incorrecta</strong><br>";
        die("Error: Password no coincide");
    }
} else {
    die("Error: Usuario no encontrado");
}

echo "<h2>2. Simular sesión</h2>";

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$_SESSION['user_id'] = $admin['id'];
$_SESSION['user_rol'] = $admin['rol'];
$_SESSION['user_nombre'] = $admin['nombre_completo'];

echo "✓ Sesión iniciada<br>";
echo "user_id: {$_SESSION['user_id']}<br>";
echo "user_rol: {$_SESSION['user_rol']}<br>";
echo "user_nombre: {$_SESSION['user_nombre']}<br>";

echo "<h2>3. Determinar redirección</h2>";

$rol = $_SESSION['user_rol'];
echo "Rol del usuario: $rol<br>";

// Convertir rol para redirección (igual que en AuthController)
$dashboardRol = $rol === 'administrador' ? 'admin' : $rol;
echo "Dashboard rol: $dashboardRol<br>";

$redirectUrl = "$dashboardRol/dashboard";
echo "URL de redirección: $redirectUrl<br>";

echo "<h2>4. Verificar controlador</h2>";

// Mapeo de controlador (igual que en index.php)
$controllerMap = [
    'administrador' => 'Admin',
    'admin' => 'Admin',
    'cliente' => 'Cliente',
    'operador' => 'Operador',
    'consultor' => 'Consultor',
    'auth' => 'Auth',
    'home' => 'Home'
];

$controllerBase = $dashboardRol;
$controllerName = isset($controllerMap[$controllerBase])
    ? $controllerMap[$controllerBase] . 'Controller'
    : ucfirst($controllerBase) . 'Controller';

echo "Controller base: $controllerBase<br>";
echo "Controller name: $controllerName<br>";

$controllerPath = __DIR__ . '/app/controllers/' . $controllerName . '.php';
echo "Controller path: $controllerPath<br>";

if (file_exists($controllerPath)) {
    echo "<strong style='color:green'>✓ Archivo del controlador existe</strong><br>";

    require_once $controllerPath;

    if (class_exists($controllerName)) {
        echo "<strong style='color:green'>✓ Clase $controllerName existe</strong><br>";

        $controller = new $controllerName();

        if (method_exists($controller, 'dashboard')) {
            echo "<strong style='color:green'>✓ Método dashboard() existe</strong><br>";
        } else {
            echo "<strong style='color:red'>✗ Método dashboard() NO existe</strong><br>";
        }
    } else {
        echo "<strong style='color:red'>✗ Clase $controllerName NO existe</strong><br>";
    }
} else {
    echo "<strong style='color:red'>✗ Archivo del controlador NO existe</strong><br>";
}

echo "<h2>5. URL completa</h2>";

$fullUrl = url($redirectUrl);
echo "URL completa: <a href='$fullUrl'>$fullUrl</a><br>";

echo "<h2>6. Probar acceso directo</h2>";
echo "<p><a href='" . url('admin/dashboard') . "' target='_blank'>Abrir admin/dashboard en nueva pestaña</a></p>";

echo "<h2>7. Estado de sesión actual</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<hr>";
echo "<p><strong>Todo parece correcto. Probando login real...</strong></p>";
echo "<p><a href='" . url('auth/login') . "'>Ir a página de login</a></p>";
