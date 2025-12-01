<?php
/**
 * Check if notifications table exists and has correct structure
 */

require_once 'config/database.php';

echo "<h1>Notifications Table Structure Check</h1>";

try {
    // Check if table exists
    $result = Database::fetchAll("SHOW TABLES LIKE 'notificaciones'");
    if (empty($result)) {
        echo "<p style='color: red;'>❌ Table 'notificaciones' does NOT exist!</p>";
        echo "<p>This means the database wasn't properly initialized.</p>";
        echo "<p>Please run the database initialization script: <code>database/init.php</code></p>";
        exit;
    }

    echo "<p style='color: green;'>✅ Table 'notificaciones' exists</p>";

    // Check table structure
    echo "<h2>Table Structure:</h2>";
    $columns = Database::fetchAll("DESCRIBE notificaciones");

    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr style='background: #f0f0f0;'><th>Column</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";

    $hasUsuarioId = false;
    foreach ($columns as $col) {
        echo "<tr>";
        echo "<td>{$col['Field']}</td>";
        echo "<td>{$col['Type']}</td>";
        echo "<td>{$col['Null']}</td>";
        echo "<td>{$col['Key']}</td>";
        echo "<td>{$col['Default']}</td>";
        echo "<td>{$col['Extra']}</td>";
        echo "</tr>";

        if ($col['Field'] === 'usuario_id') {
            $hasUsuarioId = true;
        }
    }
    echo "</table>";

    if ($hasUsuarioId) {
        echo "<p style='color: green;'>✅ Column 'usuario_id' exists</p>";
    } else {
        echo "<p style='color: red;'>❌ Column 'usuario_id' does NOT exist!</p>";
        echo "<p>The table structure is incorrect. Expected columns:</p>";
        echo "<ul>";
        echo "<li>id (INT, PRIMARY KEY, AUTO_INCREMENT)</li>";
        echo "<li>usuario_id (INT, NOT NULL)</li>";
        echo "<li>tipo (ENUM with specific values)</li>";
        echo "<li>titulo (VARCHAR)</li>";
        echo "<li>mensaje (TEXT)</li>";
        echo "<li>leido (BOOLEAN, DEFAULT FALSE)</li>";
        echo "<li>fecha_creacion (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP)</li>";
        echo "<li>fecha_lectura (DATETIME, NULL)</li>";
        echo "<li>email_enviado (BOOLEAN, DEFAULT FALSE)</li>";
        echo "<li>fecha_email (DATETIME, NULL)</li>";
        echo "</ul>";
    }

    // Check sample data
    echo "<h2>Sample Data (last 5 records):</h2>";
    $sample = Database::fetchAll("SELECT id, usuario_id, tipo, titulo, leido, fecha_creacion FROM notificaciones ORDER BY id DESC LIMIT 5");

    if (empty($sample)) {
        echo "<p>No notification records found in database.</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr style='background: #f0f0f0;'><th>ID</th><th>Usuario ID</th><th>Tipo</th><th>Titulo</th><th>Leido</th><th>Fecha</th></tr>";
        foreach ($sample as $row) {
            echo "<tr>";
            echo "<td>{$row['id']}</td>";
            echo "<td>{$row['usuario_id']}</td>";
            echo "<td>{$row['tipo']}</td>";
            echo "<td>" . substr($row['titulo'], 0, 50) . "...</td>";
            echo "<td>" . ($row['leido'] ? 'Sí' : 'No') . "</td>";
            echo "<td>{$row['fecha_creacion']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }

    // Test a simple query
    echo "<h2>Query Test:</h2>";
    try {
        $testQuery = Database::fetchOne("SELECT COUNT(*) as total FROM notificaciones WHERE usuario_id = 1");
        echo "<p style='color: green;'>✅ Query test passed: Found {$testQuery['total']} notifications for user ID 1</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Query test failed: " . $e->getMessage() . "</p>";
    }

} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database error: " . $e->getMessage() . "</p>";
    echo "<p>Please check your database configuration and ensure the database is properly initialized.</p>";
}