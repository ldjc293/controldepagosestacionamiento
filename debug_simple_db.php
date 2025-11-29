<?php
/**
 * Debug simple de conexión sin dependencias externas
 */

echo "<h1>Debug Simple - Sin Composer</h1>";

// Definir constantes manualmente si no están definidas
if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
if (!defined('DB_NAME')) define('DB_NAME', 'estacionamiento_db');
if (!defined('DB_USER')) define('DB_USER', 'root');
if (!defined('DB_PASS')) define('DB_PASS', '');

echo "<h2>Paso 1: Definir constantes</h2>";
echo "<p>DB_HOST: " . DB_HOST . "</p>";
echo "<p>DB_NAME: " . DB_NAME . "</p>";
echo "<p>DB_USER: " . DB_USER . "</p>";

echo "<h2>Paso 2: Probar conexión PDO directa</h2>";
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p style='color: green;'>✅ Conexión PDO exitosa</p>";

    echo "<h2>Paso 3: Probar consulta</h2>";
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM usuarios WHERE rol = 'cliente' AND activo = 1");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p style='color: green;'>✅ Consulta exitosa: " . $result['total'] . " clientes</p>";

    echo "<h2>Paso 4: Obtener datos de cliente</h2>";
    $stmt = $pdo->query("SELECT id, nombre_completo, email FROM usuarios WHERE rol = 'cliente' AND activo = 1 LIMIT 1");
    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($cliente) {
        echo "<p style='color: green;'>✅ Cliente encontrado: " . htmlspecialchars($cliente['nombre_completo']) . "</p>";

        echo "<h2>Paso 5: Iniciar sesión y probar página</h2>";
        session_start();
        $_SESSION['user_id'] = $cliente['id'];
        $_SESSION['user_rol'] = 'cliente';
        $_SESSION['user_name'] = $cliente['nombre_completo'];

        echo "<p style='color: green;'>✅ Sesión iniciada</p>";
        echo "<p><a href='cliente/estado-cuenta' class='btn btn-primary'>Ir a Estado de Cuenta</a></p>";
        echo "<p><a href='debug_directo.php' class='btn btn-secondary'>Ir a Debug Directo</a></p>";

    } else {
        echo "<p style='color: orange;'>⚠️ No hay clientes en la base de datos</p>";
    }

} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Error de PDO: " . $e->getMessage() . "</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error general: " . $e->getMessage() . "</p>";
}
?>