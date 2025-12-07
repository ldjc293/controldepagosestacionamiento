<?php
require_once __DIR__ . '/config/database.php';

echo "=== Todos los apartamentos del Bloque 32, Escalera 3 ===\n\n";

$sql = "SELECT * FROM apartamentos WHERE bloque = '32' AND escalera = '3' ORDER BY piso, numero_apartamento";
$result = Database::fetchAll($sql);

echo "Total: " . count($result) . " apartamentos\n\n";

foreach ($result as $apto) {
    $pisoLabel = $apto['piso'] == 0 ? 'PB' : $apto['piso'];
    echo "ID: {$apto['id']} | {$apto['bloque']}-{$apto['escalera']}-{$pisoLabel}-{$apto['numero_apartamento']}\n";
}
