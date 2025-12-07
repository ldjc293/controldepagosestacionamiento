<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/app/models/Apartamento.php';

$bloque = '28';
$apartamentosEliminados = 0;

echo "=== Eliminación de Apartamentos del Bloque 28 ===\n\n";

try {
    Database::beginTransaction();

    // Get all apartments for block 28
    $apartamentos = Apartamento::getByBloque($bloque);

    foreach ($apartamentos as $apto) {
        // Delete the apartment
        $sql = "DELETE FROM apartamentos WHERE id = ?";
        $result = Database::execute($sql, [$apto->id]);

        if ($result) {
            echo "  - Eliminado: Bloque {$apto->bloque}, Escalera {$apto->escalera}, Piso {$apto->piso}, Apto {$apto->numero_apartamento} (ID: {$apto->id})\n";
            $apartamentosEliminados++;
        }
    }

    Database::commit();

    echo "=== Resumen ===\n";
    echo "Apartamentos eliminados: $apartamentosEliminados\n";
    echo "\n✓ Proceso completado exitosamente\n";

} catch (Exception $e) {
    Database::rollback();
    echo "\n✗ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}