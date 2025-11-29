<?php
/**
 * Debug específico para pagos adelantados del operador
 */

echo "<!DOCTYPE html>";
echo "<html><head>";
echo "<title>Debug Pagos Adelantados Operador</title>";
echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>";
echo "</head><body>";
echo "<div class='container mt-4'>";

echo "<h1>🔧 Debug - Pagos Adelantados del Operador</h1>";

// Iniciar sesión como operador
session_start();
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/app/models/Usuario.php';
require_once __DIR__ . '/app/models/Mensualidad.php';

// Buscar operador
$operadores = Database::fetchAll("SELECT id, nombre_completo FROM usuarios WHERE rol = 'operador' AND activo = 1 LIMIT 1");

if ($operadores) {
    $operador = $operadores[0];
    $_SESSION['user_id'] = $operador['id'];
    $_SESSION['user_rol'] = 'operador';
    $_SESSION['user_name'] = $operador['nombre_completo'];

    echo "<div class='alert alert-success'>";
    echo "<h4>✅ Sesión de Operador Establecida</h4>";
    echo "<p>Usuario: {$operador['nombre_completo']}</p>";
    echo "</div>";

    // Test 1: Verificar clientes
    echo "<div class='card mb-3'>";
    echo "<div class='card-header'>";
    echo "<h5>Test 1: Clientes con Mensualidades</h5>";
    echo "</div>";
    echo "<div class='card-body'>";

    $clientes = Database::fetchAll("
        SELECT u.id, u.nombre_completo, u.email, COUNT(m.id) as total_mensualidades
        FROM usuarios u
        JOIN apartamento_usuario au ON au.usuario_id = u.id
        LEFT JOIN mensualidades m ON m.apartamento_usuario_id = au.id
        WHERE u.rol = 'cliente' AND u.activo = 1 AND au.activo = 1
        GROUP BY u.id, u.nombre_completo, u.email
        ORDER BY total_mensualidades DESC
        LIMIT 3
    ");

    if ($clientes) {
        echo "<h6>Clientes con mensualidades:</h6>";
        foreach ($clientes as $cliente) {
            echo "<div class='row mb-3'>";
            echo "<div class='col-md-8'>";
            echo "<strong>" . htmlspecialchars($cliente['nombre_completo']) . "</strong><br>";
            echo "<small class='text-muted'>" . htmlspecialchars($cliente['email']) . "</small><br>";
            echo "<span class='badge bg-primary'>{$cliente['total_mensualidades']} mensualidades</span>";
            echo "</div>";
            echo "<div class='col-md-4'>";
            echo "<div class='btn-group-vertical'>";
            echo "<a href='operador/registrar-pago-presencial?buscar=" . urlencode($cliente['nombre_completo']) . "' class='btn btn-sm btn-outline-primary mb-1'>Normal (6 meses)</a>";
            echo "<a href='operador/registrar-pago-presencial?buscar=" . urlencode($cliente['nombre_completo']) . "&modo=adelantado' class='btn btn-sm btn-success mb-1'>Adelantado (12 meses)</a>";
            echo "</div>";
            echo "</div>";
            echo "</div>";
        }
    } else {
        echo "<p class='text-warning'>No hay clientes con mensualidades</p>";
    }
    echo "</div></div>";

    // Test 2: Analizar mensualidades de un cliente específico
    if ($clientes) {
        $primerCliente = $clientes[0];
        echo "<div class='card mb-3'>";
        echo "<div class='card-header'>";
        echo "<h5>Test 2: Análisis de Mensualidades para " . htmlspecialchars($primerCliente['nombre_completo']) . "</h5>";
        echo "</div>";
        echo "<div class='card-body'>";

        try {
            // Probar con 6 meses
            echo "<h6>Mensualidades para Pago Adelantado (6 meses):</h6>";
            $mensualidades6 = Mensualidad::getMensualidadesParaPagoAdelantado($primerCliente['id'], 6);

            if ($mensualidades6) {
                $pendientes6 = 0;
                $futuras6 = 0;

                echo "<table class='table table-sm'>";
                echo "<tr><th>ID</th><th>Mes</th><th>Año</th><th>Vencimiento</th><th>Estado</th><th>Tipo</th><th>Data-Tipo</th></tr>";

                foreach ($mensualidades6 as $m) {
                    $fechaVencimiento = new DateTime($m->fecha_vencimiento);
                    $hoy = new DateTime();
                    $esFutura = $fechaVencimiento > $hoy;

                    if ($esFutura) {
                        $futuras6++;
                        $tipo = 'futuro';
                        $badge = 'bg-info';
                    } else {
                        $pendientes6++;
                        $tipo = 'pendiente';
                        $badge = 'bg-warning';
                    }

                    echo "<tr>";
                    echo "<td>{$m->id}</td>";
                    echo "<td>" . date('F', mktime(0,0,0,$m->mes,1,$m->anio)) . "</td>";
                    echo "<td>{$m->anio}</td>";
                    echo "<td>" . date('d/m/Y', strtotime($m->fecha_vencimiento)) . "</td>";
                    echo "<td><span class='badge {$badge}'>" . htmlspecialchars($m->estado) . "</span></td>";
                    echo "<td>" . ($esFutura ? '<span class="badge bg-info">Futura</span>' : '<span class="badge bg-secondary">Pendiente</span>') . "</td>";
                    echo "<td><code>data-tipo=\"{$tipo}\"</code></td>";
                    echo "</tr>";
                }
                echo "</table>";

                echo "<div class='alert alert-info'>";
                echo "<strong>Resumen (6 meses):</strong><br>";
                echo "• Pendientes: {$pendientes6}<br>";
                echo "• Futuras: {$futuras6}<br>";
                echo "• Total: " . count($mensualidades6);
                echo "</div>";
            }

            echo "<hr>";

            // Probar con 12 meses
            echo "<h6>Mensualidades para Pago Adelantado (12 meses):</h6>";
            $mensualidades12 = Mensualidad::getMensualidadesParaPagoAdelantado($primerCliente['id'], 12);

            if ($mensualidades12) {
                $pendientes12 = 0;
                $futuras12 = 0;

                foreach ($mensualidades12 as $m) {
                    $fechaVencimiento = new DateTime($m->fecha_vencimiento);
                    $hoy = new DateTime();
                    if ($fechaVencimiento > $hoy) {
                        $futuras12++;
                    } else {
                        $pendientes12++;
                    }
                }

                echo "<div class='alert alert-success'>";
                echo "<strong>Resumen (12 meses):</strong><br>";
                echo "• Pendientes: {$pendientes12}<br>";
                echo "• Futuras: {$futuras12}<br>";
                echo "• Total: " . count($mensualidades12);
                echo "</div>";
            }

        } catch (Exception $e) {
            echo "<div class='alert alert-danger'>";
            echo "<h6>Error:</h6>";
            echo "<p>" . $e->getMessage() . "</p>";
            echo "</div>";
        }
        echo "</div></div>";
    }

    // Test 3: Verificar funciones JavaScript esperadas
    echo "<div class='card mb-3'>";
    echo "<div class='card-header'>";
    echo "<h5>Test 3: Verificación de Funciones JavaScript</h5>";
    echo "</div>";
    echo "<div class='card-body'>";
    echo "<p>Las siguientes funciones JavaScript deberían estar disponibles:</p>";
    echo "<ul>";
    echo "<li>✅ <code>seleccionarPorTipo(tipo)</code> - Selecciona por tipo ('futuro' o 'pendiente')</li>";
    echo "<li>✅ <code>seleccionarSiguientesMeses(cantidad)</code> - Selecciona primeros N meses</li>";
    echo "<li>✅ <code>seleccionarTodos()</code> - Selecciona todos</li>";
    echo "<li>✅ <code>deseleccionarTodos()</code> - Deselecciona todos</li>";
    echo "</ul>";
    echo "</div></div>";

    // Test 4: Acceso directo
    echo "<div class='card'>";
    echo "<div class='card-header'>";
    echo "<h5>Test 4: Acceso Directo</h5>";
    echo "</div>";
    echo "<div class='card-body'>";
    echo "<p>Para probar manualmente:</p>";
    echo "<div class='d-grid gap-2'>";
    echo "<a href='operador/registrar-pago-presencial?buscar=" . urlencode($clientes[0]['nombre_completo']) . "&modo=adelantado' target='_blank' class='btn btn-success'>";
    echo "<i class='bi bi-box-arrow-up-right'></i> Probar Pagos Adelantados con " . htmlspecialchars($clientes[0]['nombre_completo']);
    echo "</a>";
    echo "</div>";
    echo "</div></div>";

} else {
    echo "<div class='alert alert-danger'>";
    echo "<h4>❌ Error: No hay operadores activos</h4>";
    echo "</div>";
}

echo "</div>";
echo "</body></html>";
?>