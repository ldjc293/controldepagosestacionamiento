<?php
/**
 * Ver el código fuente generado de configuracion.php
 */

// Simular autenticación
session_start();
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
    $_SESSION['user_rol'] = 'administrador';
    $_SESSION['user_email'] = 'admin@test.com';
}

// Capturar salida
ob_start();

// Incluir el controller que carga la vista
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/app/controllers/AdminController.php';

$controller = new AdminController();
$controller->configuracion();

$output = ob_get_clean();

// Mostrar como texto plano para ver el código fuente
header('Content-Type: text/plain; charset=utf-8');
echo "=== CÓDIGO FUENTE GENERADO ===\n\n";
echo $output;
