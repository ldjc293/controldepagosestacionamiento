<?php
/**
 * Script to manually update María González with correct UTF-8 encoding
 */

require_once __DIR__ . '/config/database.php';

echo "=== Updating María González ===\n\n";

try {
    $db = Database::getInstance();
    
    // Update with correct UTF-8 string
    $sql = "UPDATE usuarios SET nombre_completo = ? WHERE email = ?";
    $stmt = $db->prepare($sql);
    $stmt->execute(['María González', 'maria.gonzalez@gmail.com']);
    
    echo "✓ Updated successfully!\n\n";
    
    // Verify
    $sql = "SELECT nombre_completo, email FROM usuarios WHERE email = ?";
    $stmt = $db->prepare($sql);
    $stmt->execute(['maria.gonzalez@gmail.com']);
    $result = $stmt->fetch();
    
    echo "Current value: " . $result['nombre_completo'] . "\n";
    echo "Email: " . $result['email'] . "\n";
    
} catch (Exception $e) {
    echo "\n✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
