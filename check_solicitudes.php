<?php
require_once __DIR__ . '/config/database.php';

echo "=== Últimas 3 Solicitudes ===\n\n";

$sql = "SELECT id, tipo_solicitud, apartamento_usuario_id, cantidad_controles_nueva, control_id, motivo, estado, fecha_solicitud 
        FROM solicitudes_cambios 
        ORDER BY id DESC 
        LIMIT 3";

$results = Database::fetchAll($sql);

foreach ($results as $r) {
    echo "ID: " . $r['id'] . "\n";
    echo "Tipo: " . ($r['tipo_solicitud'] ?? 'NULL') . "\n";
    echo "Apartamento_Usuario_ID: " . ($r['apartamento_usuario_id'] ?? 'NULL') . "\n";
    echo "Cantidad Controles Nueva: " . ($r['cantidad_controles_nueva'] ?? 'NULL') . "\n";
    echo "Control ID: " . ($r['control_id'] ?? 'NULL') . "\n";
    echo "Motivo: " . substr($r['motivo'] ?? 'NULL', 0, 50) . "\n";
    echo "Estado: " . $r['estado'] . "\n";
    echo "Fecha: " . $r['fecha_solicitud'] . "\n";
    echo "----------------------------------------\n";
}
