<?php
/**
 * Script para generar reporte final de apartamentos del Bloque 27
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/config.php';

echo "=== REPORTE FINAL: Apartamentos del Bloque 27 ===\n\n";

try {
    // Apartamentos con formato nuevo
    echo "APARTAMENTOS CON FORMATO NUEVO (PB-A, 1A, etc.):\n";
    echo str_repeat("=", 80) . "\n";
    
    $sql = "SELECT escalera, COUNT(*) as total 
            FROM apartamentos 
            WHERE bloque = '27' 
            AND (numero_apartamento LIKE 'PB-%' OR numero_apartamento REGEXP '^[0-9][A-Z]$')
            GROUP BY escalera 
            ORDER BY escalera";
    
    $nuevos = Database::fetchAll($sql);
    $totalNuevos = 0;
    
    foreach ($nuevos as $row) {
        echo "Escalera {$row['escalera']}: {$row['total']} apartamentos\n";
        $totalNuevos += $row['total'];
    }
    echo "Total con formato nuevo: $totalNuevos\n\n";
    
    // Apartamentos con formato antiguo que tienen usuarios asignados
    echo "APARTAMENTOS CON FORMATO ANTIGUO (ASIGNADOS A USUARIOS):\n";
    echo str_repeat("=", 80) . "\n";
    
    $sql = "SELECT a.id, a.escalera, a.piso, a.numero_apartamento,
                   u.nombre_completo, u.email,
                   au.cantidad_controles
            FROM apartamentos a
            JOIN apartamento_usuario au ON au.apartamento_id = a.id AND au.activo = TRUE
            JOIN usuarios u ON u.id = au.usuario_id
            WHERE a.bloque = '27' 
            AND a.numero_apartamento REGEXP '^[0-9]+$'
            ORDER BY a.escalera, a.numero_apartamento";
    
    $antiguosAsignados = Database::fetchAll($sql);
    
    if (count($antiguosAsignados) > 0) {
        echo "⚠ Los siguientes apartamentos antiguos tienen usuarios asignados:\n\n";
        foreach ($antiguosAsignados as $apto) {
            echo "  - Escalera {$apto['escalera']}, Piso {$apto['piso']}, Apto {$apto['numero_apartamento']}\n";
            echo "    Usuario: {$apto['nombre_completo']} ({$apto['email']})\n";
            echo "    Controles: {$apto['cantidad_controles']}\n\n";
        }
        echo "ACCIÓN REQUERIDA: Reasignar estos usuarios a apartamentos con formato nuevo.\n";
    } else {
        echo "✓ No hay apartamentos antiguos con usuarios asignados.\n";
    }
    
    echo "\n" . str_repeat("=", 80) . "\n";
    echo "RESUMEN GENERAL:\n";
    echo "  - Apartamentos con formato nuevo: $totalNuevos\n";
    echo "  - Apartamentos antiguos asignados: " . count($antiguosAsignados) . "\n";
    echo "  - Total: " . ($totalNuevos + count($antiguosAsignados)) . "\n";
    
    echo "\n✓ Reporte generado exitosamente\n";
    
} catch (Exception $e) {
    echo "\n✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
