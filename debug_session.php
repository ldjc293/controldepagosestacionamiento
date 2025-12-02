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
        echo "<p><strong>SOLUCIÓN:</strong> La sesión está desincronizada. Necesitas actualizar la sesión.</p>";
    } else {
        echo "<p style='color: green;'><strong>✅ SINCRONIZADO: El nombre en sesión coincide con la BD</strong></p>";
    }

    // Mostrar información adicional para admin
    if (($_SESSION['user_rol'] ?? '') === 'administrador') {
        echo "<h3>Información específica para Administrador</h3>";
        echo "<p><strong>¿Estás viendo esto desde la vista de admin?</strong> Si es así, el problema podría ser:</p>";
        echo "<ul>";
        echo "<li>1. Cache del navegador - Intenta Ctrl+F5 para recargar</li>";
        echo "<li>2. Sesión antigua - Cierra sesión y vuelve a entrar</li>";
        echo "<li>3. Problema de redireccionamiento - Verifica que el updatePerfil esté funcionando</li>";
        echo "</ul>";

        echo "<p><strong>Para probar:</strong></p>";
        echo "<ol>";
        echo "<li>Ve a tu perfil de admin</li>";
        echo "<li>Cambia tu nombre</li>";
        echo "<li>Guarda los cambios</li>";
        echo "<li>Vuelve aquí y recarga la página</li>";
        echo "</ol>";
    }
}

echo "<hr>";
echo "<a href='" . ($_SERVER['HTTP_REFERER'] ?? '#') . "'>← Volver</a>";
echo "<br><br>";
echo "<a href='?force_refresh=1' style='background: #007bff; color: white; padding: 10px; text-decoration: none; border-radius: 5px;'>🔄 Forzar Refresh de Sesión</a>";

// Si se solicita refresh forzado
if (isset($_GET['force_refresh']) && isset($_SESSION['user_id'])) {
    require_once 'config/database.php';
    $sql = "SELECT nombre_completo, email FROM usuarios WHERE id = ?";
    $user = Database::fetchOne($sql, [$_SESSION['user_id']]);

    if ($user) {
        $_SESSION['user_nombre'] = $user['nombre_completo'];
        $_SESSION['user_email'] = $user['email'];
        echo "<script>alert('Sesión actualizada forzosamente. Recarga la página principal.');</script>";
    }
}
?>