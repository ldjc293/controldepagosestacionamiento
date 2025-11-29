<?php
/**
 * Script para agregar la columna 'cedula' a la tabla usuarios
 *
 * Ejecutar una sola vez para corregir el esquema de la base de datos
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

echo "Agregando columna 'cedula' a la tabla usuarios...\n";

try {
    $db = Database::getInstance();

    // Verificar si la columna ya existe
    $sqlCheck = "SHOW COLUMNS FROM usuarios LIKE 'cedula'";
    $result = Database::fetchOne($sqlCheck);

    if ($result) {
        echo "✓ La columna 'cedula' ya existe en la tabla usuarios\n";
    } else {
        // Agregar la columna cedula
        $sqlAlter = "ALTER TABLE usuarios ADD COLUMN cedula VARCHAR(20) NULL COMMENT 'Cédula de identidad' AFTER telefono";
        Database::execute($sqlAlter);

        echo "✓ Columna 'cedula' agregada exitosamente a la tabla usuarios\n";

        // Agregar índice para búsquedas por cédula
        $sqlIndex = "ALTER TABLE usuarios ADD INDEX idx_cedula (cedula)";
        Database::execute($sqlIndex);

        echo "✓ Índice idx_cedula creado\n";
    }

    echo "\n¡Operación completada! Ahora puedes ejecutar el script de importación.\n";

} catch (Exception $e) {
    echo "✗ Error al agregar la columna: " . $e->getMessage() . "\n";
    exit(1);
}