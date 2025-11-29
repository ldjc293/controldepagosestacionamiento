<?php
/**
 * Script to fix character encoding in the database
 * Converts incorrectly stored UTF-8 data (stored as Latin1) back to proper UTF-8
 */

require_once __DIR__ . '/config/database.php';

echo "=== Fixing Character Encoding ===\n\n";

try {
    $db = Database::getInstance();
    
    // Tables and columns to fix
    $tables = [
        'usuarios' => ['nombre_completo'],
        'apartamentos' => ['bloque'],
        'notificaciones' => ['titulo', 'mensaje'],
    ];
    
    $db->beginTransaction();
    
    foreach ($tables as $table => $columns) {
        echo "Processing table: $table\n";
        
        foreach ($columns as $column) {
            echo "  - Fixing column: $column\n";
            
            // Convert from latin1 to utf8mb4
            // This fixes data that was inserted as UTF-8 but stored as Latin1
            $sql = "UPDATE `$table` 
                    SET `$column` = CONVERT(CAST(CONVERT(`$column` USING latin1) AS BINARY) USING utf8mb4)
                    WHERE `$column` IS NOT NULL";
            
            $affected = $db->exec($sql);
            echo "    → $affected rows updated\n";
        }
    }
    
    $db->commit();
    echo "\n✓ Character encoding fixed successfully!\n";
    
} catch (Exception $e) {
    if (isset($db)) {
        $db->rollBack();
    }
    echo "\n✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
