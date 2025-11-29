<?php
/**
 * Script para analizar en detalle los apartamentos del Bloque 27
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/config.php';

echo "=== Análisis Detallado de Apartamentos del Bloque 27 ===\n\n";

try {
    // Ver todos los apartamentos agrupados por escalera y piso
    $sql = "SELECT escalera, piso, COUNT(*) as cantidad,
                   GROUP_CONCAT(numero_apartamento ORDER BY numero_apartamento) as apartamentos
            FROM apartamentos 
            WHERE bloque = '27' 
            GROUP BY escalera, piso 
            ORDER BY escalera, piso";
    
    $resultados = Database::fetchAll($sql);
    
    echo "Distribución completa:\n";
    echo str_repeat("=", 80) . "\n";
    
    $totalPorEscalera = [];
    
    foreach ($resultados as $row) {
        $escalera = $row['escalera'];
        $piso = $row['piso'];
        $cantidad = $row['cantidad'];
        $apartamentos = $row['apartamentos'];
        
        if (!isset($totalPorEscalera[$escalera])) {
            $totalPorEscalera[$escalera] = 0;
        }
        $totalPorEscalera[$escalera] += $cantidad;
        
        echo "Escalera $escalera, Piso $piso: $cantidad apartamentos\n";
        echo "  Apartamentos: $apartamentos\n";
    }
    
    echo str_repeat("=", 80) . "\n";
    echo "\nResumen por escalera:\n";
    foreach ($totalPorEscalera as $escalera => $total) {
        echo "Escalera $escalera: $total apartamentos\n";
    }
    
    $totalGeneral = array_sum($totalPorEscalera);
    echo "\nTotal general: $totalGeneral apartamentos\n";
    
    // Verificar si hay apartamentos con formato antiguo (101, 102, etc.)
    echo "\n" . str_repeat("=", 80) . "\n";
    echo "Apartamentos con formato numérico (101, 102, etc.):\n";
    $sql = "SELECT COUNT(*) as total FROM apartamentos 
            WHERE bloque = '27' AND numero_apartamento REGEXP '^[0-9]+$'";
    $formatoNumerico = Database::fetchOne($sql);
    echo "Total con formato numérico: {$formatoNumerico['total']}\n";
    
    echo "\nApartamentos con formato nuevo (PB-A, 1A, etc.):\n";
    $sql = "SELECT COUNT(*) as total FROM apartamentos 
            WHERE bloque = '27' AND (numero_apartamento LIKE 'PB-%' OR numero_apartamento REGEXP '^[0-9][A-Z]$')";
    $formatoNuevo = Database::fetchOne($sql);
    echo "Total con formato nuevo: {$formatoNuevo['total']}\n";
    
} catch (Exception $e) {
    echo "\n✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
