<?php
/**
 * Test de funcionalidad de pagos adelantados para operadores
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/app/models/Usuario.php';
require_once __DIR__ . '/app/models/Mensualidad.php';

echo "<!DOCTYPE html>";
echo "<html><head>";
echo "<title>Test Pagos Adelantados</title>";
echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>";
echo "</head><body>";
echo "<div class='container mt-4'>";

echo "<h1>💰 Test de Pagos Adelantados</h1>";

// Iniciar sesión como operador
session_start();
$operadores = Database::fetchAll("SELECT id, nombre_completo FROM usuarios WHERE rol = 'operador' AND activo = 1 LIMIT 1");

if ($operadores) {
    $operador = $operadores[0];
    $_SESSION['user_id'] = $operador['id'];
    $_SESSION['user_rol'] = 'operador';
    $_SESSION['user_name'] = $operador['nombre_completo'];

    echo "<div class='alert alert-success'>";
    echo "<h4>✅ Sesión de Operador Activa</h4>";
    echo "<p>Usuario: {$operador['nombre_completo']}</p>";
    echo "</div>";

    // Test 1: Verificar clientes disponibles
    echo "<div class='card mb-3'>";
    echo "<div class='card-header'>";
    echo "<h5>Test 1: Clientes Disponibles</h5>";
    echo "</div>";
    echo "<div class='card-body'>";

    $clientes = Database::fetchAll("SELECT id, nombre_completo, email FROM usuarios WHERE rol = 'cliente' AND activo = 1 LIMIT 3");

    if ($clientes) {
        echo "<h6>Clientes encontrados:</h6>";
        echo "<div class='row'>";
        foreach ($clientes as $cliente) {
            echo "<div class='col-md-4 mb-3'>";
            echo "<div class='card'>";
            echo "<div class='card-body'>";
            echo "<h6>" . htmlspecialchars($cliente['nombre_completo']) . "</h6>";
            echo "<p class='mb-2'><small>" . htmlspecialchars($cliente['email']) . "</small></p>";
            echo "<a href='operador/registrar-pago-presencial?buscar=" . urlencode($cliente['nombre_completo']) . "' class='btn btn-primary btn-sm'>Probar Pagos Adelantados</a>";
            echo "</div>";
            echo "</div>";
            echo "</div>";
        }
        echo "</div>";
    } else {
        echo "<p class='text-warning'>No hay clientes para probar</p>";
    }
    echo "</div></div>";

    // Test 2: Verificar método getMensualidadesParaPagoAdelantado
    if ($clientes) {
        $primerCliente = $clientes[0];
        echo "<div class='card mb-3'>";
        echo "<div class='card-header'>";
        echo "<h5>Test 2: Mensualidades para Pago Adelantado</h5>";
        echo "</div>";
        echo "<div class='card-body'>";
        echo "<p><strong>Cliente:</strong> " . htmlspecialchars($primerCliente['nombre_completo']) . "</p>";

        try {
            $mensualidades = Mensualidad::getMensualidadesParaPagoAdelantado($primerCliente['id'], 6);

            echo "<h6>Resultados:</h6>";
            echo "<p class='text-success'>✅ Método funciona: " . count($mensualidades) . " mensualidades encontradas</p>";

            if (count($mensualidades) > 0) {
                echo "<table class='table table-sm'>";
                echo "<tr><th>ID</th><th>Mes</th><th>Año</th><th>Estado</th><th>Vencimiento</th><th>Monto USD</th><th>Tipo</th></tr>";
                foreach ($mensualidades as $mensualidad) {
                    $esFutura = ($mensualidad->anio > date('Y')) ||
                               ($mensualidad->anio == date('Y') && $mensualidad->mes > date('m'));

                    echo "<tr>";
                    echo "<td>{$mensualidad->id}</td>";
                    echo "<td>" . date('F', mktime(0,0,0,$mensualidad->mes,1)) . "</td>";
                    echo "<td>{$mensualidad->anio}</td>";
                    echo "<td><span class='badge bg-warning'>" . htmlspecialchars($mensualidad->estado) . "</span></td>";
                    echo "<td>" . date('d/m/Y', strtotime($mensualidad->fecha_vencimiento)) . "</td>";
                    echo "<td>$" . number_format($mensualidad->monto_usd, 2) . "</td>";
                    echo "<td>" . ($esFutura ? '<span class="badge bg-info">Futuro</span>' : '<span class="badge bg-secondary">Pendiente</span>') . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            }
        } catch (Exception $e) {
            echo "<div class='alert alert-danger'>";
            echo "<h6>Error:</h6>";
            echo "<p>" . $e->getMessage() . "</p>";
            echo "</div>";
        }
        echo "</div></div>";
    }

    // Test 3: Verificar vista actual
    echo "<div class='card mb-3'>";
    echo "<div class='card-header'>";
    echo "<h5>Test 3: Acceso a Vista de Registro</h5>";
    echo "</div>";
    echo "<div class='card-body'>";
    echo "<p>Para probar la vista completa:</p>";
    echo "<ol>";
    echo "<li>Haz clic en uno de los botones 'Probar Pagos Adelantados' arriba</li>";
    echo "<li>Verifica que la página muestre las mensualidades futuras</li>";
    echo "<li>Busca los botones de 'Meses Futuros' o pagos adelantados</li>";
    echo "<li>Prueba seleccionar pagos de meses futuros</li>";
    echo "</ol>";
    echo "<div class='text-center mt-3'>";
    echo "<a href='operador/registrar-pago-presencial' target='_blank' class='btn btn-primary btn-lg'>";
    echo "<i class='bi bi-box-arrow-up-right'></i> Ir a Registrar Pago Presencial";
    echo "</a>";
    echo "</div>";
    echo "</div></div>";

} else {
    echo "<div class='alert alert-danger'>";
    echo "<h4>❌ Error: No hay operadores activos</h4>";
    echo "<p>No se puede realizar la prueba sin una cuenta de operador.</p>";
    echo "</div>";
}

echo "</div>";
echo "</body></html>";
?>