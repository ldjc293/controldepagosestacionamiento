<?php
require_once __DIR__ . '/config/database.php';

echo "=== Verificación de Apartamentos ===\n\n";

// 1. Verificar bloque 32, escalera 3, piso 0 (PB)
echo "1. Bloque 32, Escalera 3, Piso 0 (PB):\n";
$sql = "SELECT * FROM apartamentos WHERE bloque = '32' AND escalera = '3' AND piso = 0";
$result = Database::fetchAll($sql);
echo "Total apartamentos: " . count($result) . "\n";
foreach ($result as $apto) {
    echo "  - Apartamento: {$apto['numero_apartamento']}\n";
}
echo "\n";

// 2. Verificar bloque 28, contar apartamentos por piso
echo "2. Bloque 28 - Apartamentos por piso:\n";
$sql = "SELECT escalera, piso, COUNT(*) as total 
        FROM apartamentos 
        WHERE bloque = '28' 
        GROUP BY escalera, piso 
        ORDER BY escalera, piso";
$result = Database::fetchAll($sql);
foreach ($result as $row) {
    $pisoLabel = $row['piso'] == 0 ? 'PB' : $row['piso'];
    echo "  - Escalera {$row['escalera']}, Piso {$pisoLabel}: {$row['total']} apartamentos\n";
}
echo "\n";

// 3. Listar todos los apartamentos del bloque 28
echo "3. Todos los apartamentos del bloque 28:\n";
$sql = "SELECT * FROM apartamentos WHERE bloque = '28' ORDER BY escalera, piso, numero_apartamento";
$result = Database::fetchAll($sql);
foreach ($result as $apto) {
    $pisoLabel = $apto['piso'] == 0 ? 'PB' : $apto['piso'];
    echo "  - {$apto['bloque']}-{$apto['escalera']}-{$pisoLabel}-{$apto['numero_apartamento']}\n";
}
