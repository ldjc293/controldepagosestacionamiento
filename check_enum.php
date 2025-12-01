<?php
require_once 'config/database.php';
try {
    $cols = Database::fetchAll("SHOW COLUMNS FROM solicitudes_cambios LIKE 'tipo_solicitud'");
    print_r($cols);
} catch(Exception $e) { echo $e->getMessage(); }
?>
