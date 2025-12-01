<?php
require_once 'config/database.php';
try {
    $cols = Database::fetchAll("DESCRIBE solicitudes_cambios");
    foreach($cols as $col) { echo $col['Field'] . "\n"; }
} catch(Exception $e) { echo $e->getMessage(); }
?>
