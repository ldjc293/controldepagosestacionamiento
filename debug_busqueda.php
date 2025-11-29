<?php
/**
 * Debug del error de búsqueda: columna 'leida'
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/app/models/Usuario.php';

echo "<h1>Debug: Error en Búsqueda de Clientes</h1>";

// Test 1: Verificar que el método buscarClientes existe
echo "<h2>1. Verificar Método buscarClientes</h2>";
if (method_exists('Usuario', 'buscarClientes')) {
    echo "<p style='color: green;'>✅ Método buscarClientes existe</p>";
} else {
    echo "<p style='color: red;'>❌ Método buscarClientes NO existe</p>";
}

// Test 2: Probar con diferentes términos de búsqueda
$terminosPrueba = ['María', 'cliente', 'test', 'Ana', 'Carlos'];

foreach ($terminosPrueba as $termino) {
    echo "<h3>Probando búsqueda con: '$termino'</h3>";

    try {
        echo "<p>Intentando Usuario::buscarClientes('$termino')...</p>";
        $resultados = Usuario::buscarClientes($termino, 5);

        if (is_array($resultados)) {
            echo "<p style='color: green;'>✅ Búsqueda exitosa: " . count($resultados) . " resultados</p>";
            if (count($resultados) > 0) {
                echo "<table class='table table-bordered'>";
                echo "<tr><th>ID</th><th>Nombre</th><th>Email</th><th>Cédula</th></tr>";
                foreach ($resultados as $cliente) {
                    echo "<tr>";
                    echo "<td>{$cliente['id']}</td>";
                    echo "<td>" . htmlspecialchars($cliente['nombre_completo']) . "</td>";
                    echo "<td>" . htmlspecialchars($cliente['email']) . "</td>";
                    echo "<td>" . htmlspecialchars($cliente['cedula']) . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            }
        } else {
            echo "<p style='color: orange;'>⚠️ La búsqueda devolvió: " . var_export($resultados, true) . "</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error en Usuario::buscarClientes():</p>";
        echo "<pre>" . $e->getMessage() . "\n" . $e->getTraceAsString() . "</pre>";
    }

    echo "<hr>";
}

// Test 3: Probar método buscarCliente individual
echo "<h2>2. Probar Método buscarCliente</h2>";

$terminosCliente = ['María', 'cliente1@email.com'];

foreach ($terminosCliente as $termino) {
    echo "<h3>Probando buscarCliente con: '$termino'</h3>";

    try {
        echo "<p>Intentando Usuario::buscarCliente('$termino')...</p>";
        $cliente = Usuario::buscarCliente($termino);

        if ($cliente) {
            echo "<p style='color: green;'>✅ Cliente encontrado: {$cliente->nombre_completo}</p>";
            echo "<p>ID: {$cliente->id}, Email: {$cliente->email}</p>";
        } else {
            echo "<p style='color: orange;'>⚠️ Cliente no encontrado</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error en Usuario::buscarCliente():</p>";
        echo "<pre>" . $e->getMessage() . "\n" . $e->getTraceAsString() . "</pre>";
    }

    echo "<hr>";
}

// Test 4: Verificar consulta SQL manual
echo "<h2>3. Consulta SQL Manual</h2>";

$terminoSQL = 'María';
echo "<p>Probando consulta manual con: '$terminoSQL'</p>";

try {
    $sql = "SELECT id, nombre_completo, email, cedula
            FROM usuarios
            WHERE rol = 'cliente'
            AND activo = TRUE
            AND (cedula LIKE ? OR email = ? OR nombre_completo LIKE ?)
            ORDER BY nombre_completo
            LIMIT 5";

    $params = [
        "%$terminoSQL%",
        $terminoSQL,
        "%$terminoSQL%"
    ];

    echo "<p><strong>SQL:</strong> " . htmlspecialchars($sql) . "</p>";
    echo "<p><strong>Parámetros:</strong> " . implode(', ', $params) . "</p>";

    $resultados = Database::fetchAll($sql, $params);

    if (is_array($resultados)) {
        echo "<p style='color: green;'>✅ Consulta SQL exitosa: " . count($resultados) . " resultados</p>";
        if (count($resultados) > 0) {
            echo "<table class='table table-bordered'>";
            echo "<tr><th>ID</th><th>Nombre</th><th>Email</th><th>Cédula</th></tr>";
            foreach ($resultados as $row) {
                echo "<tr>";
                echo "<td>{$row['id']}</td>";
                echo "<td>" . htmlspecialchars($row['nombre_completo']) . "</td>";
                echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                echo "<td>" . htmlspecialchars($row['cedula']) . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    } else {
        echo "<p style='color: orange;'>⚠️ La consulta devolvió: " . var_export($resultados, true) . "</p>";
    }

} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error en consulta SQL:</p>";
    echo "<pre>" . $e->getMessage() . "\n" . $e->getTraceAsString() . "</pre>";
}

echo "<hr>";
echo "<h2>4. Información de Depuración</h2>";

// Mostrar algunos clientes existentes para prueba
echo "<h3>Clientes existentes en la BD:</h3>";
$sql = "SELECT id, nombre_completo, email, cedula FROM usuarios WHERE rol = 'cliente' AND activo = 1 LIMIT 5";
$clientes = Database::fetchAll($sql);

if ($clientes) {
    echo "<table class='table table-bordered'>";
    echo "<tr><th>ID</th><th>Nombre</th><th>Email</th><th>Cédula</th></tr>";
    foreach ($clientes as $cliente) {
        echo "<tr>";
        echo "<td>{$cliente['id']}</td>";
        echo "<td>" . htmlspecialchars($cliente['nombre_completo']) . "</td>";
        echo "<td>" . htmlspecialchars($cliente['email']) . "</td>";
        echo "<td>" . htmlspecialchars($cliente['cedula']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

echo "<style>
table { border-collapse: collapse; width: 100%; margin: 10px 0; }
th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
th { background-color: #f2f2f2; }
</style>";
?>