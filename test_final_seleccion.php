<?php
/**
 * Test final para verificar selección de mensualidades con todos los clientes
 */

echo "<!DOCTYPE html>";
echo "<html><head>";
echo "<title>Test Final - Selección de Mensualidades</title>";
echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>";
echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css' rel='stylesheet'>";
echo "</head><body>";
echo "<div class='container mt-4'>";

echo "<h1>🎯 Test Final - Selección de Mensualidades</h1>";

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
    echo "<h4>✅ Sesión de Operador: " . htmlspecialchars($operador['nombre_completo']) . "</h4>";
    echo "</div>";

    // Lista de clientes prioritarios para probar
    $clientesPrioritarios = [
        'Maria Gonzalez',
        'Ana Rodriguez',
        'Juan Perez',
        'Carlos Martinez'
    ];

    echo "<div class='card mb-4'>";
    echo "<div class='card-header'>";
    echo "<h5>👥 Clientes para Probar Selección</h5>";
    echo "</div>";
    echo "<div class='card-body'>";

    foreach ($clientesPrioritarios as $nombreCliente) {
        $cliente = Database::fetchAll("
            SELECT u.id, u.nombre_completo, u.email, u.cedula
            FROM usuarios u
            WHERE u.nombre_completo LIKE ? AND u.rol = 'cliente' AND u.activo = 1
            LIMIT 1
        ", ['%' . $nombreCliente . '%']);

        if ($cliente) {
            $cliente = $cliente[0];

            echo "<div class='row mb-4 p-3 border rounded bg-light'>";
            echo "<div class='col-md-4'>";
            echo "<h6 class='text-primary'>" . htmlspecialchars($cliente['nombre_completo']) . "</h6>";
            echo "<small class='text-muted d-block'>" . htmlspecialchars($cliente['email']) . "</small>";
            echo "<small class='text-muted d-block'>Cédula: " . htmlspecialchars($cliente['cedula'] ?? 'N/A') . "</small>";
            echo "</div>";
            echo "<div class='col-md-8'>";

            // Analizar mensualidades
            try {
                $mensualidades = Mensualidad::getMensualidadesParaPagoAdelantado($cliente['id'], 12);

                if ($mensualidades) {
                    $pendientes = 0;
                    $futuras = 0;
                    $totalMonto = 0;

                    foreach ($mensualidades as $m) {
                        $fechaVencimiento = new DateTime($m->fecha_vencimiento);
                        $hoy = new DateTime();

                        if ($fechaVencimiento > $hoy) {
                            $futuras++;
                        } else {
                            $pendientes++;
                        }

                        $totalMonto += $m->monto_usd;
                    }

                    echo "<div class='row'>";
                    echo "<div class='col-md-6'>";
                    echo "<div class='alert alert-info mb-2'>";
                    echo "<strong>📊 Resumen:</strong><br>";
                    echo "• <span class='badge bg-info'>{$futuras}</span> Meses futuros<br>";
                    echo "• <span class='badge bg-warning'>{$pendientes}</span> Pendientes<br>";
                    echo "• <span class='badge bg-primary'>{$totalMonto}</span> Total USD<br>";
                    echo "• <span class='badge bg-secondary'>" . count($mensualidades) . "</span> Total";
                    echo "</div>";
                    echo "</div>";
                    echo "<div class='col-md-6'>";
                    echo "<strong>🔗 Enlaces de Prueba:</strong><br>";
                    echo "<div class='btn-group-vertical btn-group-sm d-grid gap-1'>";
                    echo "<a href='operador/registrar-pago-presencial?buscar=" . urlencode($cliente['nombre_completo']) . "' target='_blank' class='btn btn-primary'>";
                    echo "<i class='bi bi-search'></i> Búsqueda Normal";
                    echo "</a>";
                    echo "<a href='operador/registrar-pago-presencial?buscar=" . urlencode($cliente['nombre_completo']) . "&modo=adelantado' target='_blank' class='btn btn-success'>";
                    echo "<i class='bi bi-calendar-plus'></i> Modo Adelantado";
                    echo "</a>";
                    echo "</div>";
                    echo "</div>";
                    echo "</div>";

                    echo "<div class='alert alert-success mt-2'>";
                    echo "<strong>✅ Funciones a Verificar:</strong><br>";
                    echo "1. <strong>Seleccionar Todos</strong> - Marca todas las mensualidades<br>";
                    echo "2. <strong>Deseleccionar</strong> - Limpia todas las selecciones<br>";
                    echo "3. <strong>Meses Futuros</strong> - Selecciona solo pagos futuros<br>";
                    echo "4. <strong>Próximos 3 Meses</strong> - Primeros 3 meses visibles<br>";
                    if ($futuras > 3) {
                        echo "5. <strong>Próximos 6 Meses</strong> - Solo en modo adelantado<br>";
                    }
                    echo "6. <strong>Generar más meses</strong> - Si no hay suficientes futuras";
                    echo "</div>";

                } else {
                    echo "<div class='alert alert-warning'>";
                    echo "<strong>⚠️ Sin mensualidades</strong><br>";
                    echo "Este cliente no tiene mensualidades disponibles. Usa los botones para generarlas.";
                    echo "</div>";
                }

            } catch (Exception $e) {
                echo "<div class='alert alert-danger'>";
                echo "<strong>❌ Error:</strong> " . $e->getMessage();
                echo "</div>";
            }

            echo "</div>";
            echo "</div>";
        }
    }

    echo "</div></div>";

    // Sección de pruebas rápidas
    echo "<div class='card'>";
    echo "<div class='card-header'>";
    echo "<h5>🧪 Pruebas Automáticas</h5>";
    echo "</div>";
    echo "<div class='card-body'>";
    echo "<p>Para verificar rápidamente la funcionalidad:</p>";
    echo "<ol>";
    echo "<li>Haz clic en los enlaces de arriba para cada cliente</li>";
    echo "<li>Verifica que se carguen las mensualidades en la lista</li>";
    echo "<li>Prueba cada botón de selección (Todos, Deseleccionar, Meses Futuros, etc.)</li>";
    echo "<li>Confirma que los checkboxes se marquen/desmarquen correctamente</li>";
    echo "<li>Verifica que el contador de seleccionados se actualice</li>";
    echo "</ol>";
    echo "</div></div>";

} else {
    echo "<div class='alert alert-danger'>";
    echo "<h4>❌ Error: No hay operadores activos</h4>";
    echo "</div>";
}

echo "</div>";
echo "</body></html>";
?>