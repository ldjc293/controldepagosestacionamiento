<?php
/**
 * Debug con sesión de cliente
 */

session_start();

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/app/models/Usuario.php';

// Buscar un cliente real y establecer sesión
$usuarioData = Database::fetchAll("SELECT id, nombre_completo, email, rol FROM usuarios WHERE rol = 'cliente' AND activo = 1 LIMIT 1");

if ($usuarioData && count($usuarioData) > 0) {
    $_SESSION['user_id'] = $usuarioData[0]['id'];
    $_SESSION['user_rol'] = $usuarioData[0]['rol'];
    $_SESSION['user_name'] = $usuarioData[0]['nombre_completo'];

    echo "<h2>Sesión establecida:</h2>";
    echo "<pre>";
    echo "user_id: " . $_SESSION['user_id'] . "\n";
    echo "user_rol: " . $_SESSION['user_rol'] . "\n";
    echo "user_name: " . $_SESSION['user_name'] . "\n";
    echo "</pre>";

    echo "<p><a href='cliente/estado-cuenta'>Ir a Estado de Cuenta</a></p>";
} else {
    echo "<p>No se encontraron clientes activos</p>";
}
?>