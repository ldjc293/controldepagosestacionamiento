<?php
require_once __DIR__ . '/config/database.php';

echo "=== Verificación Bloque 32 ===\n\n";

// Verificar qué escaleras tiene el bloque 32
echo "Escaleras en bloque 32:\n";
$sql = "SELECT DISTINCT escalera FROM apartamentos WHERE bloque = '32' ORDER BY escalera";
$result = Database::fetchAll($sql);
foreach ($result as $row) {
    echo "  - Escalera: {$row['escalera']}\n";
}
echo "\n";

// Verificar pisos por escalera en bloque 32
echo "Pisos por escalera en bloque 32:\n";
$sql = "SELECT DISTINCT escalera, piso FROM apartamentos WHERE bloque = '32' ORDER BY escalera, piso";
$result = Database::fetchAll($sql);
foreach ($result as $row) {
    $pisoLabel = $row['piso'] == 0 ? 'PB' : $row['piso'];
    echo "  - Escalera {$row['escalera']}, Piso {$pisoLabel}\n";
}
echo "\n";

// Contar apartamentos por escalera y piso
echo "Total de apartamentos por escalera y piso en bloque 32:\n";
$sql = "SELECT escalera, piso, COUNT(*) as total 
        FROM apartamentos 
        WHERE bloque = '32' 
        GROUP BY escalera, piso 
        ORDER BY escalera, piso";
$result = Database::fetchAll($sql);
foreach ($result as $row) {
    $pisoLabel = $row['piso'] == 0 ? 'PB' : $row['piso'];
    echo "  - Escalera {$row['escalera']}, Piso {$pisoLabel}: {$row['total']} apartamentos\n";
}
