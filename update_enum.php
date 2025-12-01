<?php
require_once 'config/database.php';

try {
    $sql = "ALTER TABLE solicitudes_cambios MODIFY COLUMN tipo_solicitud 
            ENUM('cambio_cantidad_controles',
                 'suspension_control',
                 'desactivacion_control',
                 'desincorporar_control',
                 'reportar_perdido',
                 'agregar_control',
                 'comprar_control',
                 'solicitud_personalizada') NOT NULL";
    
    Database::execute($sql);
    echo "<h1>Base de datos actualizada correctamente</h1>";
    echo "<p>Se han agregado los nuevos tipos de solicitud al ENUM.</p>";
    
    // Verificar el cambio
    $cols = Database::fetchAll("SHOW COLUMNS FROM solicitudes_cambios LIKE 'tipo_solicitud'");
    echo "<pre>";
    print_r($cols);
    echo "</pre>";

} catch (Exception $e) {
    echo "<h1>Error al actualizar la base de datos</h1>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>
