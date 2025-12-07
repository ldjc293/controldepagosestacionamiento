<?php
require_once __DIR__ . '/config/database.php';

echo "=== Verificación de Solicitudes Pendientes ===\n\n";

$sql = "SELECT id, tipo_solicitud, estado, fecha_solicitud, datos_nuevo_usuario FROM solicitudes_cambios WHERE estado = 'pendiente'";
$results = Database::fetchAll($sql);

echo "Total pendientes: " . count($results) . "\n\n";

foreach ($results as $row) {
    echo "ID: {$row['id']}\n";
    echo "Tipo: {$row['tipo_solicitud']}\n";
    echo "Estado: {$row['estado']}\n";
    echo "Fecha: {$row['fecha_solicitud']}\n";
    echo "Datos JSON: " . substr($row['datos_nuevo_usuario'] ?? 'NULL', 0, 100) . "...\n";
    echo "----------------------------------------\n";
}
