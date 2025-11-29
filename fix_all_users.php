<?php
/**
 * Script to check and fix ALL users with encoding issues
 */

require_once __DIR__ . '/config/database.php';

echo "=== Checking and Fixing All Users ===\n\n";

try {
    $db = Database::getInstance();
    
    // Get all users
    $sql = "SELECT id, nombre_completo, email FROM usuarios";
    $stmt = $db->query($sql);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Found " . count($users) . " users\n\n";
    
    // Correct names mapping
    $corrections = [
        'Ana Rodr??guez' => 'Ana Rodríguez',
        'Carlos Mart??nez' => 'Carlos Martínez',
        'Ing. Miguel S??nchez' => 'Ing. Miguel Sánchez',
        'Jos?? P??rez' => 'José Pérez',
        'Mar??a Gonz??lez' => 'María González',
        'Ram??n L??pez' => 'Ramón López',
    ];
    
    $db->beginTransaction();
    
    foreach ($users as $user) {
        $currentName = $user['nombre_completo'];
        $needsUpdate = false;
        $newName = $currentName;
        
        // Check if name needs correction
        foreach ($corrections as $bad => $good) {
            if (strpos($currentName, '??') !== false || strpos($currentName, '├') !== false) {
                $needsUpdate = true;
                // Try to match and correct
                foreach ($corrections as $badName => $goodName) {
                    if (stripos($currentName, str_replace('??', '', $badName)) !== false) {
                        $newName = $goodName;
                        break;
                    }
                }
                break;
            }
        }
        
        if ($needsUpdate) {
            echo "Updating user ID {$user['id']}: '{$currentName}' -> '{$newName}'\n";
            $updateSql = "UPDATE usuarios SET nombre_completo = ? WHERE id = ?";
            $stmt = $db->prepare($updateSql);
            $stmt->execute([$newName, $user['id']]);
        } else {
            echo "User ID {$user['id']}: '{$currentName}' - OK\n";
        }
    }
    
    $db->commit();
    echo "\n✓ All users processed!\n";
    
} catch (Exception $e) {
    if (isset($db)) {
        $db->rollBack();
    }
    echo "\n✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
