<?php
/**
 * Script para limpiar apartamentos con formato antiguo del Bloque 27
 * Elimina apartamentos con formato numérico (101, 102, etc.)
 * y deja solo los apartamentos con formato nuevo (PB-A, 1A, etc.)
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/config.php';

echo "=== Limpieza de Apartamentos del Bloque 27 ===\n\n";

try {
    Database::beginTransaction();
    
    // Primero, verificar si hay apartamentos antiguos asignados a usuarios
    $sql = "SELECT COUNT(*) as total 
            FROM apartamento_usuario au
            JOIN apartamentos a ON a.id = au.apartamento_id
            WHERE a.bloque = '27' 
            AND a.numero_apartamento REGEXP '^[0-9]+$'
            AND au.activo = TRUE";
    
    $asignados = Database::fetchOne($sql);
    
    if ($asignados['total'] > 0) {
        echo "⚠ ADVERTENCIA: Hay {$asignados['total']} apartamentos antiguos asignados a usuarios.\n";
        echo "No se eliminarán para evitar pérdida de datos.\n";
        echo "Por favor, reasigne manualmente estos usuarios a los nuevos apartamentos.\n\n";
    }
    
    // Contar apartamentos antiguos sin asignar
    $sql = "SELECT COUNT(*) as total 
            FROM apartamentos a
            LEFT JOIN apartamento_usuario au ON au.apartamento_id = a.id AND au.activo = TRUE
            WHERE a.bloque = '27' 
            AND a.numero_apartamento REGEXP '^[0-9]+$'
            AND au.id IS NULL";
    
    $sinAsignar = Database::fetchOne($sql);
    
    echo "Apartamentos con formato antiguo sin asignar: {$sinAsignar['total']}\n";
    
    if ($sinAsignar['total'] > 0) {
        // Eliminar apartamentos antiguos sin asignar
        $sql = "DELETE a FROM apartamentos a
                LEFT JOIN apartamento_usuario au ON au.apartamento_id = a.id AND au.activo = TRUE
                WHERE a.bloque = '27' 
                AND a.numero_apartamento REGEXP '^[0-9]+$'
                AND au.id IS NULL";
        
        $eliminados = Database::execute($sql);
        
        echo "✓ Eliminados: $eliminados apartamentos con formato antiguo\n";
    }
    
    Database::commit();
    
    // Verificar resultado final
    echo "\n=== Resultado Final ===\n";
    
    $sql = "SELECT COUNT(*) as total FROM apartamentos WHERE bloque = '27'";
    $total = Database::fetchOne($sql);
    echo "Total de apartamentos en Bloque 27: {$total['total']}\n";
    
    $sql = "SELECT escalera, COUNT(*) as total 
            FROM apartamentos 
            WHERE bloque = '27' 
            GROUP BY escalera 
            ORDER BY escalera";
    $porEscalera = Database::fetchAll($sql);
    
    echo "\nPor escalera:\n";
    foreach ($porEscalera as $row) {
        echo "  Escalera {$row['escalera']}: {$row['total']} apartamentos\n";
    }
    
    echo "\n✓ Limpieza completada exitosamente\n";
    
} catch (Exception $e) {
    Database::rollback();
    echo "\n✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
