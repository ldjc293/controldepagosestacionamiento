<?php

// Inicialización básica
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>";
echo "<html><head>";
echo "<title>Test Minimal</title>";
echo "</head><body style='background: white;'>";
echo "<h1>Test Minimal</h1>";

try {
    require_once __DIR__ . '/config/config.php';
    require_once __DIR__ . '/config/database.php';
    require_once __DIR__ . '/app/models/Usuario.php';
    require_once __DIR__ . '/app/models/Mensualidad.php';
    require_once __DIR__ . '/app/models/Pago.php';

    echo "<p style='color: green;'>✓ Todos los archivos cargados</p>";

    // Buscar cliente
    $usuarios = Database::fetchAll("SELECT id, nombre_completo FROM usuarios WHERE rol = 'cliente' AND activo = 1 LIMIT 1");

    if ($usuarios) {
        $usuarioId = $usuarios[0]['id'];
        echo "<p style='color: green;'>✓ Cliente encontrado: ID $usuarioId</p>";

        // Probar métodos
        $mensualidades = Mensualidad::getAllByUsuario($usuarioId);
        echo "<p style='color: green;'>✓ getAllByUsuario: " . count($mensualidades) . " resultados</p>";

        $pagos = Pago::getByUsuario($usuarioId);
        echo "<p style='color: green;'>✓ getByUsuario: " . count($pagos) . " resultados</p>";

        $deudaInfo = Mensualidad::calcularDeudaTotal($usuarioId);
        echo "<p style='color: green;'>✓ calcularDeudaTotal funciona</p>";

        // Mostrar resultados
        echo "<h2>Resultados:</h2>";
        echo "<pre>" . print_r($deudaInfo, true) . "</pre>";

    } else {
        echo "<p style='color: orange;'>No hay clientes</p>";
    }

} catch (Error $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
} catch (Exception $e) {
    echo "<p style='color: red;'>Exception: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "</body></html>";
?>