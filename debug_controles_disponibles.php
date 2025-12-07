<?php
/**
 * Diagnóstico: Verificar controles disponibles
 */

require_once 'config/database.php';
require_once 'config/config.php';
require_once 'app/models/Control.php';

echo "=== DIAGNÓSTICO: CONTROLES DISPONIBLES ===\n\n";

// Verificar total de controles
$sql = "SELECT COUNT(*) as total FROM controles_estacionamiento";
$result = Database::fetchOne($sql);
echo "Total de controles en BD: " . $result['total'] . "\n\n";

// Verificar distribución por estado
$sql = "SELECT estado, COUNT(*) as cantidad FROM controles_estacionamiento GROUP BY estado ORDER BY cantidad DESC";
$estados = Database::fetchAll($sql);

echo "Distribución por estado:\n";
foreach ($estados as $estado) {
    echo "  {$estado['estado']}: {$estado['cantidad']}\n";
}
echo "\n";

// Verificar controles vacíos
$controlesVacios = Control::getVacios();
echo "Controles vacíos obtenidos por Control::getVacios(): " . count($controlesVacios) . "\n";

if (count($controlesVacios) > 0) {
    echo "Primeros 5 controles vacíos:\n";
    for ($i = 0; $i < min(5, count($controlesVacios)); $i++) {
        $control = $controlesVacios[$i];
        echo "  ID: {$control['id']}, Número: {$control['numero_control_completo']}, Estado: {$control['estado']}\n";
    }
} else {
    echo "❌ No hay controles vacíos disponibles\n";

    // Verificar si hay controles con apartamento_usuario_id NULL pero estado diferente
    $sql = "SELECT COUNT(*) as total FROM controles_estacionamiento WHERE apartamento_usuario_id IS NULL";
    $result = Database::fetchOne($sql);
    echo "Controles con apartamento_usuario_id NULL: {$result['total']}\n";

    // Verificar si hay controles con estado 'vacio' pero apartamento_usuario_id asignado
    $sql = "SELECT COUNT(*) as total FROM controles_estacionamiento WHERE estado = 'vacio' AND apartamento_usuario_id IS NOT NULL";
    $result = Database::fetchOne($sql);
    echo "Controles con estado 'vacio' pero apartamento_usuario_id asignado: {$result['total']}\n";
}

echo "\n=== FIN DEL DIAGNÓSTICO ===\n";
?>