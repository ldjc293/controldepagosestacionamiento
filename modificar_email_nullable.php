<?php
/**
 * Script para modificar la columna 'email' en la tabla usuarios para permitir NULL
 *
 * Ejecutar una sola vez para permitir emails en blanco
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

echo "Modificando columna 'email' para permitir NULL...\n";

try {
    $db = Database::getInstance();

    // Verificar el estado actual de la columna
    $sqlCheck = "SHOW COLUMNS FROM usuarios LIKE 'email'";
    $result = Database::fetchOne($sqlCheck);

    if ($result && strpos($result['Null'], 'YES') !== false) {
        echo "✓ La columna 'email' ya permite NULL\n";
    } else {
        // Modificar la columna para permitir NULL
        $sqlAlter = "ALTER TABLE usuarios MODIFY COLUMN email VARCHAR(255) NULL COMMENT 'Email del usuario (puede estar vacío)'";
        Database::execute($sqlAlter);

        echo "✓ Columna 'email' modificada para permitir NULL\n";

        // Remover la restricción UNIQUE si existe
        $sqlDropUnique = "ALTER TABLE usuarios DROP INDEX email";
        Database::execute($sqlDropUnique);

        echo "✓ Restricción UNIQUE removida del email\n";

        // Verificar si el índice normal existe
        $sqlCheckIndex = "SHOW INDEX FROM usuarios WHERE Key_name = 'idx_email'";
        $indexExists = Database::fetchOne($sqlCheckIndex);

        if (!$indexExists) {
            // Agregar índice normal (no único)
            $sqlAddIndex = "ALTER TABLE usuarios ADD INDEX idx_email (email)";
            Database::execute($sqlAddIndex);
            echo "✓ Índice normal agregado al email\n";
        } else {
            echo "✓ Índice idx_email ya existe\n";
        }
    }

    echo "\n¡Operación completada! Ahora el email puede estar vacío.\n";

} catch (Exception $e) {
    echo "✗ Error al modificar la columna: " . $e->getMessage() . "\n";
    exit(1);
}