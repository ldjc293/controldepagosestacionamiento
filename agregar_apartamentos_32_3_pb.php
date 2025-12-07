<?php
/**
 * Script para agregar apartamentos faltantes
 * Bloque 32, Escalera 3, Piso PB (0)
 */

require_once __DIR__ . '/config/database.php';

echo "=== Agregar Apartamentos - Bloque 32, Escalera 3, Piso PB ===\n\n";

// Verificar si ya existen
$sql = "SELECT COUNT(*) as total FROM apartamentos WHERE bloque = '32' AND escalera = '3' AND piso = 0";
$result = Database::fetchOne($sql);
$existentes = $result['total'];

echo "Apartamentos existentes en 32-3-PB: $existentes\n";

if ($existentes > 0) {
    echo "⚠️  Ya existen apartamentos en esta ubicación.\n";
    echo "¿Desea continuar de todas formas? (y/n): ";
    $handle = fopen("php://stdin", "r");
    $line = fgets($handle);
    if (trim($line) != 'y') {
        echo "Operación cancelada.\n";
        exit;
    }
    fclose($handle);
}

// Apartamentos a crear (siguiendo el patrón del bloque 28: 001-005)
$apartamentos = ['001', '002', '003', '004', '005'];

echo "\nCreando apartamentos:\n";

try {
    Database::beginTransaction();

    foreach ($apartamentos as $numero) {
        $sql = "INSERT INTO apartamentos (bloque, escalera, piso, numero_apartamento, activo) 
                VALUES ('32', '3', 0, ?, TRUE)";
        
        Database::execute($sql, [$numero]);
        echo "  ✓ Creado: 32-3-PB-$numero\n";
    }

    Database::commit();
    echo "\n✅ Todos los apartamentos fueron creados exitosamente!\n";

    // Verificar
    $sql = "SELECT * FROM apartamentos WHERE bloque = '32' AND escalera = '3' AND piso = 0 ORDER BY numero_apartamento";
    $result = Database::fetchAll($sql);
    
    echo "\nApartamentos en 32-3-PB:\n";
    foreach ($result as $apto) {
        echo "  - {$apto['bloque']}-{$apto['escalera']}-PB-{$apto['numero_apartamento']} (ID: {$apto['id']})\n";
    }

} catch (Exception $e) {
    Database::rollback();
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
