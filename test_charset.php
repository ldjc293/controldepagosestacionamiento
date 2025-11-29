<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test de Charset</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .success { color: green; }
        .error { color: red; }
        table { border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>🧪 Test de Configuración de Charset</h1>

<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

echo "<h2>1. Configuración de PHP</h2>";
echo "<ul>";
echo "<li>mb_internal_encoding(): <strong>" . mb_internal_encoding() . "</strong></li>";
echo "<li>mb_http_output(): <strong>" . mb_http_output() . "</strong></li>";
echo "<li>default_charset: <strong>" . ini_get('default_charset') . "</strong></li>";
echo "</ul>";

echo "<h2>2. Configuración de MySQL</h2>";
try {
    $pdo = Database::getInstance();

    // Verificar charset de la conexión
    $stmt = $pdo->query("SHOW VARIABLES LIKE 'character_set%'");
    echo "<table>";
    echo "<tr><th>Variable</th><th>Valor</th></tr>";
    while ($row = $stmt->fetch()) {
        $class = (strpos($row['Value'], 'utf8mb4') !== false) ? 'success' : 'error';
        echo "<tr><td>{$row['Variable_name']}</td><td class='$class'><strong>{$row['Value']}</strong></td></tr>";
    }
    echo "</table>";

    echo "<h2>3. Test de Caracteres Especiales</h2>";

    // Insertar un registro de prueba con acentos
    $testText = "Prueba de acentos: áéíóú ÁÉÍÓÚ ñÑ ¿? ¡!";
    echo "<p><strong>Texto de prueba:</strong> $testText</p>";

    // Crear tabla temporal si no existe
    $pdo->exec("CREATE TABLE IF NOT EXISTS test_charset (
        id INT AUTO_INCREMENT PRIMARY KEY,
        texto VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Limpiar registros anteriores
    $pdo->exec("DELETE FROM test_charset");

    // Insertar texto con acentos
    $stmt = $pdo->prepare("INSERT INTO test_charset (texto) VALUES (?)");
    $stmt->execute([$testText]);

    // Leer el texto
    $stmt = $pdo->query("SELECT texto FROM test_charset ORDER BY id DESC LIMIT 1");
    $result = $stmt->fetch();

    echo "<p><strong>Texto recuperado de BD:</strong> " . htmlspecialchars($result['texto']) . "</p>";

    if ($result['texto'] === $testText) {
        echo "<p class='success'>✅ Los caracteres especiales se guardaron y recuperaron correctamente</p>";
    } else {
        echo "<p class='error'>❌ Hay un problema con los caracteres especiales</p>";
        echo "<p>Esperado: " . htmlspecialchars($testText) . "</p>";
        echo "<p>Obtenido: " . htmlspecialchars($result['texto']) . "</p>";
    }

    // Limpiar tabla de prueba
    $pdo->exec("DROP TABLE test_charset");

    echo "<h2>4. Test de Datos Reales</h2>";

    // Obtener algunos usuarios
    $stmt = $pdo->query("SELECT nombre_completo, email FROM usuarios LIMIT 5");
    echo "<table>";
    echo "<tr><th>Nombre Completo</th><th>Email</th></tr>";
    while ($row = $stmt->fetch()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['nombre_completo']) . "</td>";
        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";

    echo "<h2>✅ Configuración Completada</h2>";
    echo "<p>Si ves correctamente los acentos y caracteres especiales arriba, la configuración es correcta.</p>";

} catch (Exception $e) {
    echo "<p class='error'>Error: " . $e->getMessage() . "</p>";
}
?>

</body>
</html>
