<?php
/**
 * Debug simple del estado de cuenta
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/app/models/Usuario.php';
require_once __DIR__ . '/app/models/Mensualidad.php';
require_once __DIR__ . '/app/models/Pago.php';

// Simular un usuario cliente
try {
    $usuarios = Database::fetchAll("SELECT id, nombre_completo, email FROM usuarios WHERE rol = 'cliente' AND activo = 1 LIMIT 1");

    if ($usuarios && count($usuarios) > 0) {
        $usuario = (object)$usuarios[0]; // Convertir a objeto
        $mensualidades = Mensualidad::getAllByUsuario($usuario->id);
        $pagos = Pago::getByUsuario($usuario->id);
        $deudaInfo = Mensualidad::calcularDeudaTotal($usuario->id);

        echo "<!DOCTYPE html>";
        echo "<html><head>";
        echo "<title>Estado de Cuenta Debug</title>";
        echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>";
        echo "</head><body>";
        echo "<div class='container mt-4'>";
        echo "<h1>Estado de Cuenta - Debug</h1>";
        echo "<p>Usuario: " . htmlspecialchars($usuario->nombre_completo) . "</p>";

        echo "<h2>Información de Deuda</h2>";
        echo "<pre>" . print_r($deudaInfo, true) . "</pre>";

        echo "<h2>Mensualidades (" . count($mensualidades) . ")</h2>";
        if (count($mensualidades) > 0) {
            echo "<table class='table'>";
            echo "<tr><th>Mes</th><th>Año</th><th>Estado</th><th>Monto USD</th></tr>";
            foreach ($mensualidades as $m) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($m['mes']) . "</td>";
                echo "<td>" . htmlspecialchars($m['anio']) . "</td>";
                echo "<td>" . htmlspecialchars($m['estado']) . "</td>";
                echo "<td>$" . number_format($m['monto_usd'], 2) . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }

        echo "</div></body></html>";

    } else {
        echo "No se encontraron usuarios clientes";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>