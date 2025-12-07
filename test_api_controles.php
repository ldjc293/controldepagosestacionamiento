<?php
/**
 * Test directo de la API de controles disponibles
 */

require_once 'config/database.php';
require_once 'config/config.php';
require_once 'app/controllers/ApiController.php';

echo "=== TEST API CONTROLES DISPONIBLES ===\n\n";

// Simular petición GET
$_GET['cantidad'] = '2';

// Crear instancia del controlador
$apiController = new ApiController();

// Llamar al método directamente
echo "Llamando a controlesDisponibles()...\n";
try {
    $apiController->controlesDisponibles();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n=== FIN TEST ===\n";
?>