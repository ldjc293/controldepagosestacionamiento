<?php
require_once 'config/database.php';

echo "<h1>Debug Solicitudes</h1>";

// 1. Ver última solicitud
try {
    $sql = "SELECT * FROM solicitudes_cambios ORDER BY id DESC LIMIT 1";
    $result = Database::fetchAll($sql);
    
    if (empty($result)) {
        echo "<p>No hay solicitudes en la base de datos.</p>";
    } else {
        $latest = $result[0];
        echo "<h2>Última Solicitud (ID: " . $latest['id'] . ")</h2>";
        echo "<pre>";
        print_r($latest);
        echo "</pre>";
        
        echo "<p><strong>Tipo Solicitud:</strong> [" . $latest['tipo_solicitud'] . "]</p>";
        echo "<p><strong>Motivo:</strong> [" . $latest['motivo'] . "]</p>";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
