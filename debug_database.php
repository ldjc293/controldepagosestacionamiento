<?php
echo "<h1>Debug Database Class</h1>";

echo "<h2>Paso 1: Verificar archivos</h2>";
$files = [
    'config/config.php',
    'config/database.php',
    'vendor/autoload.php'
];

foreach ($files as $file) {
    if (file_exists(__DIR__ . '/' . $file)) {
        echo "<p style='color: green;'>✅ $file existe</p>";
    } else {
        echo "<p style='color: red;'>❌ $file NO existe</p>";
    }
}

echo "<h2>Paso 2: Cargar archivos</h2>";

try {
    echo "<p>Cargando config.php...</p>";
    require_once __DIR__ . '/config/config.php';
    echo "<p style='color: green;'>✅ config.php cargado</p>";
} catch (Error $e) {
    echo "<p style='color: red;'>❌ Error en config.php: " . $e->getMessage() . "</p>";
    die();
}

try {
    echo "<p>Cargando database.php...</p>";
    require_once __DIR__ . '/config/database.php';
    echo "<p style='color: green;'>✅ database.php cargado</p>";
} catch (Error $e) {
    echo "<p style='color: red;'>❌ Error en database.php: " . $e->getMessage() . "</p>";
    die();
}

echo "<h2>Paso 3: Verificar clase Database</h2>";
if (class_exists('Database')) {
    echo "<p style='color: green;'>✅ Clase Database existe</p>";

    echo "<h2>Paso 4: Probar conexión</h2>";
    try {
        $test = Database::fetchAll("SELECT 1 as test");
        echo "<p style='color: green;'>✅ Conexión Database funciona</p>";
        echo "<pre>" . print_r($test, true) . "</pre>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error en conexión Database: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Clase Database NO existe</p>";

    echo "<h3>Clases definidas:</h3>";
    $classes = get_declared_classes();
    foreach ($classes as $class) {
        if (strpos($class, 'Database') !== false) {
            echo "<p>- $class</p>";
        }
    }
}

echo "<h2>Paso 5: Verificar autoload</h2>";
if (function_exists('spl_autoload_functions')) {
    $autoloaders = spl_autoload_functions();
    echo "<p>Autoloaders registrados:</p>";
    echo "<pre>" . print_r($autoloaders, true) . "</pre>";
} else {
    echo "<p>No hay autoloaders registrados</p>";
}
?>