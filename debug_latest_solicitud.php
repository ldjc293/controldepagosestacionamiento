<?php
require_once 'config/database.php';
try {
    $sql = "SELECT * FROM solicitudes_cambios ORDER BY id DESC LIMIT 1";
    $latest = Database::fetchAll($sql)[0] ?? null;
    print_r($latest);
} catch(Exception $e) { echo $e->getMessage(); }
?>
