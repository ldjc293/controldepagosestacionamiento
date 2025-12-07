<?php
/**
 * Test API endpoints
 */

require_once __DIR__ . '/config/database.php';

echo "=== Test API Endpoints ===\n\n";

// Test 1: Get apartamentos
echo "1. GET apartamentos (bloque=32, escalera=3, piso=0):\n";
$bloque = '32';
$escalera = '3';
$piso = '0';

$sql = "SELECT numero_apartamento 
        FROM apartamentos 
        WHERE bloque = ? AND escalera = ? AND piso = ?
        ORDER BY numero_apartamento";

$results = Database::fetchAll($sql, [$bloque, $escalera, $piso]);
echo "Query: $sql\n";
echo "Params: bloque=$bloque, escalera=$escalera, piso=$piso\n";
echo "Results: " . count($results) . " apartamentos\n";
echo json_encode($results, JSON_PRETTY_PRINT) . "\n\n";

// Test 2: Try with integer piso
echo "2. GET apartamentos (bloque=32, escalera=3, piso=0 as integer):\n";
$piso = 0;
$results = Database::fetchAll($sql, [$bloque, $escalera, $piso]);
echo "Params: bloque=$bloque, escalera=$escalera, piso=$piso (integer)\n";
echo "Results: " . count($results) . " apartamentos\n";
echo json_encode($results, JSON_PRETTY_PRINT) . "\n\n";

// Test 3: Get pisos
echo "3. GET pisos (bloque=32, escalera=3):\n";
$sql = "SELECT DISTINCT piso FROM apartamentos WHERE bloque = ? AND escalera = ? ORDER BY piso";
$results = Database::fetchAll($sql, [$bloque, $escalera]);
echo "Results:\n";
echo json_encode($results, JSON_PRETTY_PRINT) . "\n";
