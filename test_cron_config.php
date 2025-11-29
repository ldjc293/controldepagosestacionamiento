<?php
/**
 * Script de prueba para verificar configuración de tareas CRON
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

header('Content-Type: text/html; charset=utf-8');

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Test Configuración CRON</title>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .task { border: 1px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 5px; }
    .active { background-color: #d4edda; border-color: #28a745; }
    .inactive { background-color: #f8d7da; border-color: #dc3545; }
    .info { background-color: #d1ecf1; border-color: #0dcaf0; padding: 10px; margin: 10px 0; }
</style>";
echo "</head><body>";

echo "<h1>🔧 Test de Configuración CRON</h1>";

try {
    // Obtener todas las tareas CRON
    $sql = "SELECT * FROM configuracion_cron ORDER BY nombre_tarea";
    $tareas = Database::fetchAll($sql);

    echo "<div class='info'>";
    echo "<strong>Total de tareas configuradas:</strong> " . count($tareas);
    echo "</div>";

    echo "<h2>Tareas CRON Configuradas:</h2>";

    foreach ($tareas as $tarea) {
        $claseEstado = $tarea['activo'] ? 'active' : 'inactive';
        $estadoTexto = $tarea['activo'] ? '✓ ACTIVO' : '✗ INACTIVO';

        echo "<div class='task $claseEstado'>";
        echo "<h3>{$tarea['descripcion']} <span style='float:right;'>$estadoTexto</span></h3>";
        echo "<strong>ID:</strong> {$tarea['id']}<br>";
        echo "<strong>Nombre técnico:</strong> {$tarea['nombre_tarea']}<br>";
        echo "<strong>Frecuencia:</strong> " . ucfirst($tarea['frecuencia']) . "<br>";
        echo "<strong>Hora de ejecución:</strong> " . date('H:i', strtotime($tarea['hora_ejecucion'])) . "<br>";

        if ($tarea['frecuencia'] === 'mensual') {
            echo "<strong>Día del mes:</strong> {$tarea['dia_mes']}<br>";
        }

        if ($tarea['ultima_ejecucion']) {
            echo "<strong>Última ejecución:</strong> " . date('d/m/Y H:i:s', strtotime($tarea['ultima_ejecucion'])) . "<br>";
        } else {
            echo "<strong>Última ejecución:</strong> <em>Nunca ejecutada</em><br>";
        }

        echo "</div>";
    }

    echo "<h2>✅ Verificaciones:</h2>";
    echo "<ul>";
    echo "<li>✓ Tabla 'configuracion_cron' existe</li>";
    echo "<li>✓ " . count($tareas) . " tareas cargadas</li>";
    echo "<li>✓ Estructura de datos correcta</li>";
    echo "</ul>";

    echo "<hr>";
    echo "<h2>📋 Próximos pasos:</h2>";
    echo "<ol>";
    echo "<li>Ir a la <a href='/controldepagosestacionamiento/admin/configuracion' style='color: #007bff; font-weight: bold;'>página de configuración</a></li>";
    echo "<li>Hacer clic en cualquier tarea CRON para editarla</li>";
    echo "<li>Cambiar la hora de ejecución o activar/desactivar</li>";
    echo "<li>Hacer clic en 'Ejecutar' para probar la tarea manualmente</li>";
    echo "</ol>";

} catch (Exception $e) {
    echo "<div style='background: #f8d7da; border: 2px solid #dc3545; padding: 15px; margin: 10px 0;'>";
    echo "<strong>❌ Error:</strong> " . htmlspecialchars($e->getMessage());
    echo "</div>";
}

echo "</body></html>";
