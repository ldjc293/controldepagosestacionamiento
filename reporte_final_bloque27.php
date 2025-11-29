<?php
/**
 * Reporte final detallado de apartamentos del Bloque 27
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/config.php';

echo "=== REPORTE FINAL: Apartamentos del Bloque 27 ===\n\n";

try {
    // Total general
    $sql = "SELECT COUNT(*) as total FROM apartamentos WHERE bloque = '27'";
    $total = Database::fetchOne($sql);
    echo "TOTAL DE APARTAMENTOS: {$total['total']}\n\n";
    
    // Por escalera con detalle de cada piso
    echo "DISTRIBUCIÓN POR ESCALERA Y PISO:\n";
    echo str_repeat("=", 80) . "\n\n";
    
    for ($escalera = 1; $escalera <= 4; $escalera++) {
        $sql = "SELECT COUNT(*) as total FROM apartamentos 
                WHERE bloque = '27' AND escalera = ?";
        $totalEscalera = Database::fetchOne($sql, [(string)$escalera]);
        
        echo "ESCALERA $escalera: {$totalEscalera['total']} apartamentos\n";
        
        for ($piso = 0; $piso <= 4; $piso++) {
            $sql = "SELECT numero_apartamento FROM apartamentos 
                    WHERE bloque = '27' AND escalera = ? AND piso = ?
                    ORDER BY numero_apartamento";
            $aptos = Database::fetchAll($sql, [(string)$escalera, $piso]);
            
            if (count($aptos) > 0) {
                $numeros = array_map(fn($a) => $a['numero_apartamento'], $aptos);
                $pisoNombre = $piso == 0 ? 'PB' : "Piso $piso";
                echo "  $pisoNombre: " . implode(', ', $numeros) . "\n";
            }
        }
        echo "\n";
    }
    
    // Verificar apartamentos con usuarios asignados
    echo str_repeat("=", 80) . "\n";
    echo "APARTAMENTOS CON USUARIOS ASIGNADOS:\n";
    echo str_repeat("=", 80) . "\n\n";
    
    $sql = "SELECT a.escalera, a.piso, a.numero_apartamento,
                   u.nombre_completo, u.email, au.cantidad_controles
            FROM apartamentos a
            JOIN apartamento_usuario au ON au.apartamento_id = a.id AND au.activo = TRUE
            JOIN usuarios u ON u.id = au.usuario_id
            WHERE a.bloque = '27'
            ORDER BY a.escalera, a.piso, a.numero_apartamento";
    
    $conUsuarios = Database::fetchAll($sql);
    
    if (count($conUsuarios) > 0) {
        foreach ($conUsuarios as $apto) {
            echo "Escalera {$apto['escalera']}, Piso {$apto['piso']}, Apto {$apto['numero_apartamento']}\n";
            echo "  Usuario: {$apto['nombre_completo']} ({$apto['email']})\n";
            echo "  Controles: {$apto['cantidad_controles']}\n\n";
        }
    } else {
        echo "No hay apartamentos con usuarios asignados.\n\n";
    }
    
    // Verificar formato de numeración
    echo str_repeat("=", 80) . "\n";
    echo "VERIFICACIÓN DE FORMATO:\n";
    echo str_repeat("=", 80) . "\n\n";
    
    $sql = "SELECT COUNT(*) as total FROM apartamentos 
            WHERE bloque = '27' AND numero_apartamento REGEXP '^[0-9]{3}$'";
    $formatoCorrecto = Database::fetchOne($sql);
    
    echo "✓ Apartamentos con formato numérico correcto (###): {$formatoCorrecto['total']}\n";
    
    $sql = "SELECT numero_apartamento FROM apartamentos 
            WHERE bloque = '27' AND numero_apartamento NOT REGEXP '^[0-9]{3}$'";
    $formatoIncorrecto = Database::fetchAll($sql);
    
    if (count($formatoIncorrecto) > 0) {
        echo "✗ Apartamentos con formato incorrecto:\n";
        foreach ($formatoIncorrecto as $apto) {
            echo "  - {$apto['numero_apartamento']}\n";
        }
    } else {
        echo "✓ Todos los apartamentos tienen formato numérico de 3 dígitos\n";
    }
    
    echo "\n" . str_repeat("=", 80) . "\n";
    echo "✓ Reporte completado exitosamente\n";
    
} catch (Exception $e) {
    echo "\n✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
