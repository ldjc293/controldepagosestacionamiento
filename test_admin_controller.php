<?php
/**
 * Test AdminSolicitudesController Logic
 */

// Simular sesión de administrador
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['user_rol'] = 'administrador';

// Mock redirect function
function redirect($url) {
    echo "Redirecting to: $url\n";
}

// Mock view requirement
function require_once_mock($file) {
    echo "Requiring view: $file\n";
    global $solicitudesPendientes;
    echo "Solicitudes pendientes en vista: " . count($solicitudesPendientes) . "\n";
    foreach ($solicitudesPendientes as $s) {
        echo " - ID: {$s->id}, Tipo: {$s->tipo_solicitud}\n";
    }
}

// Override require_once mechanism is hard in PHP without runkit or similar.
// Instead, we will include the controller and instantiate it, but we can't easily intercept the require_once inside it.
// So we will just use the model directly to see if it returns data in this context.

require_once __DIR__ . '/app/models/SolicitudCambio.php';

echo "=== Test SolicitudCambio::getSolicitudesRegistro ===\n";
$solicitudes = SolicitudCambio::getSolicitudesRegistro('pendiente');
echo "Encontradas: " . count($solicitudes) . "\n";
foreach ($solicitudes as $s) {
    echo " - ID: {$s->id}, Tipo: {$s->tipo_solicitud}\n";
}

echo "\n=== Test AdminSolicitudesController (Simulado) ===\n";
// We can't easily run the controller method because it requires the view file which expects variables.
// But we verified the model above.
