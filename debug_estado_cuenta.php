<?php
/**
 * Debug del error en estado de cuenta
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/app/models/Usuario.php';
require_once __DIR__ . '/app/models/Mensualidad.php';
require_once __DIR__ . '/app/models/Pago.php';

echo "<h1>Debug: Estado de Cuenta</h1>";

// Simular un usuario cliente
echo "<h2>1. Buscar usuario cliente</h2>";

try {
    $usuarios = Database::fetchAll("SELECT id, nombre_completo, email FROM usuarios WHERE rol = 'cliente' AND activo = 1 LIMIT 1");

    if ($usuarios && count($usuarios) > 0) {
        $usuario = $usuarios[0];
        echo "<p style='color: green;'>✅ Usuario encontrado: {$usuario['nombre_completo']} (ID: {$usuario['id']})</p>";

        echo "<h2>2. Probar getAllByUsuario</h2>";
        $mensualidades = Mensualidad::getAllByUsuario($usuario['id']);
        echo "<p style='color: green;'>✅ getAllByUsuario funcionó: " . count($mensualidades) . " mensualidades</p>";

        echo "<h2>3. Probar Pago::getByUsuario</h2>";
        $pagos = Pago::getByUsuario($usuario['id']);
        echo "<p style='color: green;'>✅ Pago::getByUsuario funcionó: " . count($pagos) . " pagos</p>";

        echo "<h2>4. Probar calcularDeudaTotal</h2>";
        $deudaInfo = Mensualidad::calcularDeudaTotal($usuario['id']);
        echo "<p style='color: green;'>✅ calcularDeudaTotal funcionó:</p>";
        echo "<pre>" . print_r($deudaInfo, true) . "</pre>";

    } else {
        echo "<p style='color: orange;'>⚠️ No se encontraron usuarios clientes</p>";
    }

} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error:</p>";
    echo "<pre>" . $e->getMessage() . "\n" . $e->getTraceAsString() . "</pre>";
}

echo "<hr>";
echo "<h2>5. Verificar Archivos</h2>";

$archivos = [
    'app/models/Usuario.php',
    'app/models/Mensualidad.php',
    'app/models/Pago.php',
    'app/controllers/ClienteController.php',
    'app/views/cliente/estado_cuenta.php'
];

foreach ($archivos as $archivo) {
    $ruta = __DIR__ . '/' . $archivo;
    if (file_exists($ruta)) {
        echo "<p style='color: green;'>✅ $archivo existe</p>";
    } else {
        echo "<p style='color: red;'>❌ $archivo NO existe</p>";
    }
}
?>