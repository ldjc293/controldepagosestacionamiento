<?php
/**
 * Debug script para verificar actualización de sesión
 */

session_start();

echo "<h1>Debug - Estado de Sesión</h1>";
echo "<pre>";

echo "=== DATOS DE SESIÓN ===\n";
echo "user_id: " . ($_SESSION['user_id'] ?? 'NO SET') . "\n";
echo "user_nombre: " . ($_SESSION['user_nombre'] ?? 'NO SET') . "\n";
echo "user_email: " . ($_SESSION['user_email'] ?? 'NO SET') . "\n";
echo "user_rol: " . ($_SESSION['user_rol'] ?? 'NO SET') . "\n";

echo "\n=== TODA LA SESIÓN ===\n";
print_r($_SESSION);

echo "\n=== COOKIES ===\n";
print_r($_COOKIE);

echo "\n=== SERVER ===\n";
echo "REQUEST_METHOD: " . ($_SERVER['REQUEST_METHOD'] ?? 'NO SET') . "\n";
echo "HTTP_REFERER: " . ($_SERVER['HTTP_REFERER'] ?? 'NO SET') . "\n";
echo "REMOTE_ADDR: " . ($_SERVER['REMOTE_ADDR'] ?? 'NO SET') . "\n";

echo "</pre>";

// Si hay un usuario logueado, mostrar info de BD
if (isset($_SESSION['user_id'])) {
    require_once 'config/database.php';

    $sql = "SELECT id, nombre_completo, email FROM usuarios WHERE id = ?";
    $user = Database::fetchOne($sql, [$_SESSION['user_id']]);

    echo "<h2>Usuario en Base de Datos</h2>";
    echo "<pre>";
    print_r($user);
    echo "</pre>";

    echo "<h2>Comparación Sesión vs BD</h2>";
    echo "<p>Nombre en sesión: <strong>" . ($_SESSION['user_nombre'] ?? 'NO SET') . "</strong></p>";
    echo "<p>Nombre en BD: <strong>" . ($user['nombre_completo'] ?? 'NO SET') . "</strong></p>";

    if (($_SESSION['user_nombre'] ?? '') !== ($user['nombre_completo'] ?? '')) {
        echo "<p style='color: red;'><strong>❌ DESINCORNIZADO: El nombre en sesión no coincide con la BD</strong></p>";
    } else {
        echo "<p style='color: green;'><strong>✅ SINCRONIZADO: El nombre en sesión coincide con la BD</strong></p>";
    }
}

echo "<hr>";
echo "<a href='" . ($_SERVER['HTTP_REFERER'] ?? '#') . "'>← Volver</a>";
?>