<?php
/**
 * Script para limpiar apartamentos antiguos duplicados de la Escalera 1
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/config.php';

echo "=== Limpieza de Apartamentos Antiguos Duplicados ===\n\n";

try {
    // Identificar apartamentos antiguos (formato 101, 102, etc. que NO son 001, 002, etc.)
    $sql = "SELECT a.id, a.escalera, a.piso, a.numero_apartamento,
                   u.nombre_completo, u.email,
                   au.id as au_id, au.cantidad_controles
            FROM apartamentos a
            LEFT JOIN apartamento_usuario au ON au.apartamento_id = a.id AND au.activo = TRUE
            LEFT JOIN usuarios u ON u.id = au.usuario_id
            WHERE a.bloque = '27'
            AND a.numero_apartamento REGEXP '^[0-9]{3}$'
            AND (
                (a.piso = 0 AND CAST(a.numero_apartamento AS UNSIGNED) > 10) OR
                (a.piso > 0 AND CAST(a.numero_apartamento AS UNSIGNED) != (a.piso * 100 + 1) 
                             AND CAST(a.numero_apartamento AS UNSIGNED) != (a.piso * 100 + 2)
                             AND CAST(a.numero_apartamento AS UNSIGNED) != (a.piso * 100 + 3)
                             AND CAST(a.numero_apartamento AS UNSIGNED) != (a.piso * 100 + 4)
                             AND CAST(a.numero_apartamento AS UNSIGNED) != (a.piso * 100 + 5))
            )
            ORDER BY a.escalera, a.piso, a.numero_apartamento";
    
    $antiguos = Database::fetchAll($sql);
    
    if (count($antiguos) == 0) {
        echo "✓ No se encontraron apartamentos antiguos duplicados\n";
        exit(0);
    }
    
    echo "Apartamentos antiguos encontrados:\n";
    echo str_repeat("-", 80) . "\n";
    
    $conUsuarios = [];
    $sinUsuarios = [];
    
    foreach ($antiguos as $apto) {
        $info = "Escalera {$apto['escalera']}, Piso {$apto['piso']}, Apto {$apto['numero_apartamento']}";
        
        if ($apto['nombre_completo']) {
            echo "⚠ CON USUARIO: $info\n";
            echo "  Usuario: {$apto['nombre_completo']} ({$apto['email']})\n";
            echo "  Controles: {$apto['cantidad_controles']}\n\n";
            $conUsuarios[] = $apto;
        } else {
            echo "  SIN USUARIO: $info\n";
            $sinUsuarios[] = $apto;
        }
    }
    
    echo str_repeat("-", 80) . "\n";
    echo "Total con usuarios: " . count($conUsuarios) . "\n";
    echo "Total sin usuarios: " . count($sinUsuarios) . "\n\n";
    
    if (count($sinUsuarios) > 0) {
        Database::beginTransaction();
        
        echo "Eliminando apartamentos sin usuarios...\n";
        foreach ($sinUsuarios as $apto) {
            $sql = "DELETE FROM apartamentos WHERE id = ?";
            Database::execute($sql, [$apto['id']]);
            echo "  ✓ Eliminado: Escalera {$apto['escalera']}, Piso {$apto['piso']}, Apto {$apto['numero_apartamento']}\n";
        }
        
        Database::commit();
        echo "\n✓ Eliminados " . count($sinUsuarios) . " apartamentos antiguos\n";
    }
    
    if (count($conUsuarios) > 0) {
        echo "\n⚠ ACCIÓN REQUERIDA:\n";
        echo "Los siguientes apartamentos tienen usuarios asignados y deben reasignarse manualmente:\n\n";
        
        foreach ($conUsuarios as $apto) {
            echo "  - Escalera {$apto['escalera']}, Piso {$apto['piso']}, Apto {$apto['numero_apartamento']}\n";
            echo "    Usuario: {$apto['nombre_completo']} ({$apto['email']})\n";
            echo "    Controles: {$apto['cantidad_controles']}\n\n";
        }
    }
    
    // Verificar total final
    $sql = "SELECT COUNT(*) as total FROM apartamentos WHERE bloque = '27'";
    $total = Database::fetchOne($sql);
    echo "\nTotal de apartamentos en Bloque 27: {$total['total']}\n";
    
} catch (Exception $e) {
    if (Database::inTransaction()) {
        Database::rollback();
    }
    echo "\n✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
