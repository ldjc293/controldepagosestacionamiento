<?php
/**
 * Script para agregar la columna meses_bloqueo a la tabla configuracion_tarifas
 * Este script soluciona el error: "Column not found: 1054 Unknown column 'meses_bloqueo' in 'field list'"
 */

require_once 'config/database.php';

echo "<h2>Agregando columna 'meses_bloqueo' a la tabla 'configuracion_tarifas'</h2>";

try {
    // Verificar si la columna ya existe
    $sql = "SHOW COLUMNS FROM configuracion_tarifas LIKE 'meses_bloqueo'";
    $result = Database::fetchAll($sql);
    
    if (count($result) > 0) {
        echo "<div style='color: blue;'>La columna 'meses_bloqueo' ya existe en la tabla configuracion_tarifas</div>";
    } else {
        // Agregar la columna meses_bloqueo
        $sql = "ALTER TABLE configuracion_tarifas
                ADD COLUMN meses_bloqueo INT NOT NULL DEFAULT 2
                COMMENT 'Meses de mora para bloqueo automático de controles'
                AFTER monto_mensual_usd";
        
        Database::execute($sql);
        
        echo "<div style='color: green;'>✓ Columna 'meses_bloqueo' agregada correctamente</div>";
        
        // Actualizar registros existentes con un valor por defecto
        $sql = "UPDATE configuracion_tarifas SET meses_bloqueo = 2 WHERE meses_bloqueo IS NULL OR meses_bloqueo = 0";
        Database::execute($sql);
        
        echo "<div style='color: green;'>✓ Registros existentes actualizados con meses_bloqueo = 2</div>";
    }
    
    // Verificar la estructura final de la tabla
    echo "<h3>Estructura actual de la tabla configuracion_tarifas:</h3>";
    $sql = "DESCRIBE configuracion_tarifas";
    $result = Database::fetchAll($sql);
    
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Clave</th><th>Predeterminado</th><th>Extra</th></tr>";
    
    foreach ($result as $row) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
        echo "<td>" . ($row['Null'] === 'NO' ? 'NO' : 'YES') . "</td>";
        echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Default'] ?? '') . "</td>";
        echo "<td>" . htmlspecialchars($row['Extra']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h3>Probando el sistema...</h3>";
    
    // Probar una consulta SELECT que incluye la columna meses_bloqueo
    $sql = "SELECT * FROM configuracion_tarifas WHERE activo = 1 LIMIT 1";
    $config = Database::fetchOne($sql);
    
    if ($config) {
        echo "<div style='color: green;'>✓ Consulta de prueba exitosa. Configuración actual:</div>";
        echo "<ul>";
        echo "<li>Monto mensual USD: " . htmlspecialchars($config['monto_mensual_usd']) . "</li>";
        echo "<li>Meses de bloqueo: " . htmlspecialchars($config['meses_bloqueo']) . "</li>";
        echo "<li>Activo: " . ($config['activo'] ? 'Sí' : 'No') . "</li>";
        echo "</ul>";
    } else {
        // Insertar un registro de configuración si no existe
        echo "<div style='color: orange;'>⚠ No hay configuración activa. Creando una configuración por defecto...</div>";
        
        $sql = "INSERT INTO configuracion_tarifas
                (monto_mensual_usd, meses_bloqueo, fecha_vigencia_inicio, activo, creado_por, fecha_creacion)
                VALUES (5.00, 2, NOW(), 1, 1, NOW())";
        
        Database::execute($sql);
        
        echo "<div style='color: green;'>✓ Configuración por defecto creada correctamente</div>";
    }
    
    echo "<h3 style='color: green;'>✓ ¡Proceso completado con éxito!</h3>";
    echo "<p>La columna 'meses_bloqueo' ha sido agregada correctamente. Ahora debería poder guardar la configuración sin errores.</p>";
    echo "<p><a href='index.php'>Volver al inicio</a></p>";
    
} catch (Exception $e) {
    echo "<div style='color: red; padding: 10px; border: 1px solid red;'>";
    echo "<h3>Error:</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}
?>