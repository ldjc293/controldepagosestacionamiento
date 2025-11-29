<?php
/**
 * Script para verificar la numeración de apartamentos del Bloque 27
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/config.php';

echo "=== Verificación Final de Apartamentos del Bloque 27 ===\n\n";

try {
    // Mostrar todos los apartamentos por escalera y piso
    $sql = "SELECT escalera, piso, 
                   GROUP_CONCAT(numero_apartamento ORDER BY numero_apartamento) as apartamentos,
                   COUNT(*) as cantidad
            FROM apartamentos 
            WHERE bloque = '27' 
            GROUP BY escalera, piso 
            ORDER BY escalera, piso";
    
    $resultados = Database::fetchAll($sql);
    
    echo "Distribución completa por escalera y piso:\n";
    echo str_repeat("=", 80) . "\n\n";
    
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
        
        $pisoNombre = $piso == 0 ? 'PB' : "Piso $piso";
        echo "Escalera $escalera, $pisoNombre: $cantidad apartamentos\n";
        echo "  Números: $apartamentos\n\n";
    }
    
    echo str_repeat("=", 80) . "\n";
    echo "RESUMEN POR ESCALERA:\n";
    echo str_repeat("-", 80) . "\n";
    
    $totalGeneral = 0;
    foreach ($totalPorEscalera as $escalera => $total) {
        echo "Escalera $escalera: $total apartamentos\n";
        $totalGeneral += $total;
    }
    
    echo str_repeat("-", 80) . "\n";
    echo "TOTAL GENERAL: $totalGeneral apartamentos\n\n";
    
    // Verificar que todos tengan formato numérico correcto
    $sql = "SELECT COUNT(*) as total FROM apartamentos 
            WHERE bloque = '27' 
            AND numero_apartamento REGEXP '^[0-9]{3}$'";
    $formatoCorrecto = Database::fetchOne($sql);
    
    echo "Apartamentos con formato correcto (###): {$formatoCorrecto['total']}\n";
    
    // Verificar si hay apartamentos con formato incorrecto
    $sql = "SELECT numero_apartamento FROM apartamentos 
            WHERE bloque = '27' 
            AND numero_apartamento NOT REGEXP '^[0-9]{3}$'";
    $formatoIncorrecto = Database::fetchAll($sql);
    
    if (count($formatoIncorrecto) > 0) {
        echo "\n⚠ Apartamentos con formato incorrecto:\n";
        foreach ($formatoIncorrecto as $apto) {
            echo "  - {$apto['numero_apartamento']}\n";
        }
    } else {
        echo "✓ Todos los apartamentos tienen formato numérico correcto\n";
    }
    
    echo "\n✓ Verificación completada\n";
    
} catch (Exception $e) {
    echo "\n✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
