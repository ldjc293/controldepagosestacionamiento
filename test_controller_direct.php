<?php
/**
 * Test API Controller Logic Directly
 */

// Simular entorno
$_GET['bloque'] = '32';
$_GET['escalera'] = '3';
$_GET['piso'] = '0';

require_once __DIR__ . '/app/controllers/ApiController.php';

echo "=== Test Directo ApiController ===\n";
echo "Simulando GET: bloque=32, escalera=3, piso=0\n\n";

$controller = new ApiController();
$controller->getApartamentos();

echo "\n\n";

// Test con piso string "0"
$_GET['piso'] = "0";
echo "Simulando GET: piso=\"0\" (string)\n";
$controller->getApartamentos();
