<?php
/**
 * Script to manually correct user names with accent issues.
 */
require_once __DIR__ . '/config/database.php';

echo "=== Fixing specific user names ===\n\n";

$corrections = [
    1 => 'Ing. Miguel Sánchez',
    2 => 'Carmen Méndez',
    4 => 'María González',
    5 => 'Roberto Díaz',
    7 => 'Juan Pérez',
    8 => 'Ana Rodríguez',
    9 => 'Carlos Martínez',
];

try {
    $db = Database::getInstance();
    $db->beginTransaction();
    foreach ($corrections as $id => $name) {
        $stmt = $db->prepare('UPDATE usuarios SET nombre_completo = ? WHERE id = ?');
        $stmt->execute([$name, $id]);
        echo "Updated ID $id to '$name'\n";
    }
    $db->commit();
    echo "\nAll specified users updated successfully.\n";
} catch (Exception $e) {
    if (isset($db)) $db->rollBack();
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
