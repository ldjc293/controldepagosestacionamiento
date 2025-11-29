<?php
/**
 * Script para verificar los apartamentos del Bloque 27
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/config.php';

echo "=== Verificación de Apartamentos del Bloque 27 ===\n\n";

try {
    // Contar apartamentos por escalera
    $sql = "SELECT escalera, COUNT(*) as total, 
                   GROUP_CONCAT(DISTINCT piso ORDER BY piso) as pisos
            FROM apartamentos 
            WHERE bloque = '27' 
            GROUP BY escalera 
            ORDER BY escalera";
    
    $resultados = Database::fetchAll($sql);
    
    echo "Resumen por escalera:\n";
    echo str_repeat("-", 60) . "\n";
    
    $totalGeneral = 0;
    foreach ($resultados as $row) {
        echo "Escalera {$row['escalera']}: {$row['total']} apartamentos\n";
        echo "  Pisos: {$row['pisos']}\n";
        $totalGeneral += $row['total'];
    }
    
    echo str_repeat("-", 60) . "\n";
    echo "Total de apartamentos en Bloque 27: $totalGeneral\n\n";
    
    // Mostrar algunos ejemplos de cada escalera
    echo "Ejemplos de apartamentos por escalera:\n";
    echo str_repeat("-", 60) . "\n";
    
    for ($escalera = 1; $escalera <= 4; $escalera++) {
        $sql = "SELECT bloque, escalera, piso, numero_apartamento 
                FROM apartamentos 
                WHERE bloque = '27' AND escalera = ? 
                ORDER BY piso, numero_apartamento 
                LIMIT 5";
        
        $ejemplos = Database::fetchAll($sql, [(string)$escalera]);
        
        echo "\nEscalera $escalera (primeros 5):\n";
        foreach ($ejemplos as $apto) {
            echo "  - Bloque {$apto['bloque']}, Escalera {$apto['escalera']}, Piso {$apto['piso']}, Apto {$apto['numero_apartamento']}\n";
        }
    }
    
    echo "\n✓ Verificación completada\n";
    
} catch (Exception $e) {
    echo "\n✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
