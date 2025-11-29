<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Debug del Sistema</h1>";

// Cargar config
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

echo "<h2>1. Constantes definidas</h2>";
echo "APP_URL: " . (defined('APP_URL') ? APP_URL : 'NO DEFINIDA') . "<br>";
echo "BASE_URL: " . (defined('BASE_URL') ? BASE_URL : 'NO DEFINIDA') . "<br>";
echo "DB_NAME: " . (defined('DB_NAME') ? DB_NAME : 'NO DEFINIDA') . "<br>";

echo "<h2>2. Función url() test</h2>";
if (function_exists('url')) {
    echo "url('auth/login') = " . url('auth/login') . "<br>";
    echo "url('admin/dashboard') = " . url('admin/dashboard') . "<br>";
} else {
    echo "Función url() NO EXISTE<br>";
}

echo "<h2>3. Test de base de datos</h2>";
try {
    $db = Database::getInstance();
    echo "✓ Conexión exitosa<br>";

    // Verificar admin
    $admin = Database::fetchOne(
        "SELECT * FROM usuarios WHERE email = ?",
        ['admin@estacionamiento.com']
    );

    if ($admin) {
        echo "✓ Usuario admin existe<br>";
        echo "ID: " . $admin['id'] . "<br>";
        echo "Email: " . $admin['email'] . "<br>";
        echo "Rol: " . $admin['rol'] . "<br>";
        echo "Activo: " . ($admin['activo'] ? 'Sí' : 'No') . "<br>";

        // Verificar password
        echo "<h3>Test de password</h3>";
        $testPassword = 'Admin123!';
        echo "Password de prueba: $testPassword<br>";
        echo "Hash en DB: " . substr($admin['password'], 0, 20) . "...<br>";

        if (password_verify($testPassword, $admin['password'])) {
            echo "<strong style='color:green'>✓ Password correcta</strong><br>";
        } else {
            echo "<strong style='color:red'>✗ Password incorrecta</strong><br>";
        }
    } else {
        echo "✗ Usuario admin NO existe<br>";
    }

} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "<br>";
}

echo "<h2>4. Test de AuthController</h2>";
$authFile = __DIR__ . '/app/controllers/AuthController.php';
if (file_exists($authFile)) {
    echo "✓ AuthController.php existe<br>";
    require_once $authFile;

    if (class_exists('AuthController')) {
        echo "✓ Clase AuthController cargada<br>";
        $auth = new AuthController();
        echo "✓ AuthController instanciado<br>";

        $methods = get_class_methods($auth);
        echo "Métodos disponibles: " . implode(', ', $methods) . "<br>";
    } else {
        echo "✗ Clase AuthController NO encontrada<br>";
    }
} else {
    echo "✗ AuthController.php NO existe<br>";
}

echo "<h2>5. Simulación de login</h2>";
echo "<form method='POST' action='debug-login.php'>";
echo "Email: <input type='email' name='email' value='admin@estacionamiento.com'><br>";
echo "Password: <input type='password' name='password' value='Admin123!'><br>";
echo "<button type='submit'>Test Login</button>";
echo "</form>";
