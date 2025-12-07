<?php
require_once __DIR__ . '/config/database.php';

echo "=== Verificación de Solicitudes Pendientes (Detallado) ===\n\n";

$sql = "SELECT id, tipo_solicitud, estado FROM solicitudes_cambios WHERE estado = 'pendiente'";
$results = Database::fetchAll($sql);

echo "Total pendientes: " . count($results) . "\n";

foreach ($results as $row) {
    echo "ID: " . $row['id'] . " | Tipo: '" . $row['tipo_solicitud'] . "' | Estado: " . $row['estado'] . "\n";
}
