<?php
echo "<h1>Verificación del Sistema</h1>";

echo "<h2>Módulos de Apache</h2>";
if (function_exists('apache_get_modules')) {
    $modules = apache_get_modules();
    echo "mod_rewrite: " . (in_array('mod_rewrite', $modules) ? '<span style="color:green">✓ Habilitado</span>' : '<span style="color:red">✗ Deshabilitado</span>') . "<br>";
    echo "<details><summary>Ver todos los módulos</summary><pre>";
    print_r($modules);
    echo "</pre></details>";
} else {
    echo "No se puede detectar (probablemente está habilitado)<br>";
}

echo "<h2>Sesión</h2>";
echo "Session ID: " . (session_id() ?: 'No iniciada') . "<br>";
echo "User ID: " . ($_SESSION['user_id'] ?? 'No autenticado') . "<br>";

echo "<h2>Rutas</h2>";
echo "REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'N/A') . "<br>";
echo "SCRIPT_NAME: " . ($_SERVER['SCRIPT_NAME'] ?? 'N/A') . "<br>";
echo "PHP_SELF: " . ($_SERVER['PHP_SELF'] ?? 'N/A') . "<br>";

echo "<h2>Archivos requeridos</h2>";
$files = [
    'config/config.php',
    'config/database.php',
    '.env',
    'app/controllers/AuthController.php'
];
foreach ($files as $file) {
    $path = __DIR__ . '/' . $file;
    echo "$file: " . (file_exists($path) ? '<span style="color:green">✓ Existe</span>' : '<span style="color:red">✗ No existe</span>') . "<br>";
}

echo "<h2>Prueba de base de datos</h2>";
try {
    require_once __DIR__ . '/config/config.php';
    require_once __DIR__ . '/config/database.php';
    $db = Database::getInstance();
    echo '<span style="color:green">✓ Conexión exitosa</span><br>';

    // Contar usuarios
    $result = $db->query("SELECT COUNT(*) as total FROM usuarios")->fetch();
    echo "Usuarios en DB: " . $result['total'] . "<br>";
} catch (Exception $e) {
    echo '<span style="color:red">✗ Error: ' . htmlspecialchars($e->getMessage()) . '</span><br>';
}

echo "<h2>Enlaces de prueba</h2>";
echo '<a href="/controldepagosestacionamiento/auth/login">Ir a Login</a><br>';
echo '<a href="/controldepagosestacionamiento/public/index.php?url=auth/login">Ir a Login (directo)</a><br>';
