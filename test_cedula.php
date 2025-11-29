<?php
/**
 * Script de prueba para verificar que la columna cedula funciona
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/app/models/Usuario.php';

echo "Probando consultas con columna cedula...\n";

try {
    // Probar consulta simple
    $sql = "SELECT id, nombre_completo, email, cedula FROM usuarios WHERE rol = 'cliente' LIMIT 5";
    $result = Database::fetchAll($sql);

    echo "✓ Consulta básica exitosa. Encontrados " . count($result) . " clientes.\n";

    if (count($result) > 0) {
        echo "Primer cliente:\n";
        echo "- ID: " . $result[0]['id'] . "\n";
        echo "- Nombre: " . $result[0]['nombre_completo'] . "\n";
        echo "- Email: " . $result[0]['email'] . "\n";
        echo "- Cédula: " . ($result[0]['cedula'] ?? 'NULL') . "\n";
    }

    // Probar método del modelo
    $clientes = Usuario::getClientesConControles();
    echo "\n✓ Método getClientesConControles() funciona. " . count($clientes) . " clientes encontrados.\n";

    // Probar método de controles
    $controles = Control::getControlesConPropietarios();
    echo "✓ Método getControlesConPropietarios() funciona. " . count($controles) . " controles encontrados.\n";

    echo "\n🎉 Todas las pruebas pasaron exitosamente!\n";

} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}