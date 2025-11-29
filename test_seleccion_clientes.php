<?php
/**
 * Test específico para verificar selección de mensualidades de clientes
 */

echo "<!DOCTYPE html>";
echo "<html><head>";
echo "<title>Test Selección Clientes</title>";
echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>";
echo "</head><body>";
echo "<div class='container mt-4'>";

echo "<h1>👥 Test Selección de Mensualidades por Cliente</h1>";

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
    echo "<h4>✅ Sesión de Operador Activa</h4>";
    echo "</div>";

    // Buscar todos los clientes con apartamentos
    $clientes = Database::fetchAll("
        SELECT u.id, u.nombre_completo, u.email, u.cedula,
               COUNT(m.id) as total_mensualidades,
               COUNT(CASE WHEN m.estado = 'pendiente' THEN 1 END) as pendientes
        FROM usuarios u
        JOIN apartamento_usuario au ON au.usuario_id = u.id
        LEFT JOIN mensualidades m ON m.apartamento_usuario_id = au.id
        WHERE u.rol = 'cliente' AND u.activo = 1 AND au.activo = 1
        GROUP BY u.id, u.nombre_completo, u.email, u.cedula
        HAVING total_mensualidades > 0
        ORDER BY u.nombre_completo
        LIMIT 5
    ");

    if ($clientes) {
        echo "<div class='card mb-3'>";
        echo "<div class='card-header'>";
        echo "<h5>Clientes Disponibles con Mensualidades</h5>";
        echo "</div>";
        echo "<div class='card-body'>";

        foreach ($clientes as $cliente) {
            echo "<div class='row mb-4 p-3 border rounded'>";
            echo "<div class='col-md-6'>";
            echo "<h6 class='mb-1'>" . htmlspecialchars($cliente['nombre_completo']) . "</h6>";
            echo "<small class='text-muted d-block'>" . htmlspecialchars($cliente['email']) . "</small>";
            echo "<small class='text-muted d-block'>Cédula: " . htmlspecialchars($cliente['cedula'] ?? 'N/A') . "</small>";
            echo "<div class='mt-2'>";
            echo "<span class='badge bg-primary'>{$cliente['total_mensualidades']} mensualidades</span> ";
            echo "<span class='badge bg-warning'>{$cliente['pendientes']} pendientes</span>";
            echo "</div>";
            echo "</div>";
            echo "<div class='col-md-6'>";

            // Analizar mensualidades de este cliente
            try {
                $mensualidades = Mensualidad::getMensualidadesParaPagoAdelantado($cliente['id'], 12);

                if ($mensualidades) {
                    $pendientes = 0;
                    $futuras = 0;
                    $proximos3meses = 0;

                    // Contar tipos de mensualidades
                    foreach ($mensualidades as $m) {
                        $fechaVencimiento = new DateTime($m->fecha_vencimiento);
                        $hoy = new DateTime();
                        $mesesDiff = ($fechaVencimiento->format('Y') - $hoy->format('Y')) * 12 +
                                   ($fechaVencimiento->format('n') - $hoy->format('n'));

                        if ($mesesDiff > 0) {
                            $futuras++;
                            if ($mesesDiff <= 3) $proximos3meses++;
                        } else {
                            $pendientes++;
                        }
                    }

                    echo "<div class='alert alert-info mb-2'>";
                    echo "<strong>Disponibles:</strong><br>";
                    echo "• <span class='badge bg-info'>{$futuras}</span> Meses futuros<br>";
                    echo "• <span class='badge bg-warning'>{$pendientes}</span> Meses pendientes<br>";
                    echo "• <span class='badge bg-success'>{$proximos3meses}</span> Próximos 3 meses";
                    echo "</div>";

                    echo "<div class='btn-group-vertical btn-group-sm d-grid gap-1'>";
                    echo "<a href='operador/registrar-pago-presencial?buscar=" . urlencode($cliente['nombre_completo']) . "&modo=adelantado' target='_blank' class='btn btn-success'>";
                    echo "<i class='bi bi-calendar-plus'></i> Pagos Adelantados (12 meses)";
                    echo "</a>";

                    if ($futuras > 0) {
                        echo "<small class='text-muted d-block mt-2'>✅ Tiene meses futuros disponibles</small>";
                    } else {
                        echo "<small class='text-warning d-block mt-2'>⚠️ No tiene meses futuros generados</small>";
                    }

                    echo "</div>";
                } else {
                    echo "<div class='alert alert-warning'>";
                    echo "<i class='bi bi-exclamation-triangle'></i> No hay mensualidades disponibles";
                    echo "</div>";
                }
            } catch (Exception $e) {
                echo "<div class='alert alert-danger'>";
                echo "<strong>Error:</strong> " . $e->getMessage();
                echo "</div>";
            }

            echo "</div>";
            echo "</div>";
        }

        echo "</div></div>";

        // Sección de prueba directa
        echo "<div class='card'>";
        echo "<div class='card-header'>";
        echo "<h5>Prueba Directa - María González</h5>";
        echo "</div>";
        echo "<div class='card-body'>";

        // Buscar específicamente a María González
        $maria = Database::fetchAll("
            SELECT u.id, u.nombre_completo
            FROM usuarios u
            WHERE u.nombre_completo LIKE '%Maria%' OR u.nombre_completo LIKE '%maria%'
            AND u.rol = 'cliente' AND u.activo = 1
            LIMIT 1
        ");

        if ($maria) {
            $maria = $maria[0];
            echo "<div class='alert alert-success'>";
            echo "<h6>✅ Cliente encontrado: " . htmlspecialchars($maria['nombre_completo']) . "</h6>";
            echo "</div>";

            echo "<div class='row'>";
            echo "<div class='col-md-6'>";
            echo "<h6>Botones de Prueba:</h6>";
            echo "<div class='d-grid gap-2'>";
            echo "<a href='operador/registrar-pago-presencial?buscar=" . urlencode($maria['nombre_completo']) . "' target='_blank' class='btn btn-primary'>";
            echo "<i class='bi bi-search'></i> Buscar Normal (6 meses)";
            echo "</a>";
            echo "<a href='operador/registrar-pago-presencial?buscar=" . urlencode($maria['nombre_completo']) . "&modo=adelantado' target='_blank' class='btn btn-success'>";
            echo "<i class='bi bi-calendar-plus'></i> Pagos Adelantados (12 meses)";
            echo "</a>";
            echo "</div>";
            echo "</div>";
            echo "<div class='col-md-6'>";
            echo "<h6>Verificación de Funciones:</h6>";
            echo "<div class='alert alert-info'>";
            echo "<strong>Para verificar los botones:</strong><br>";
            echo "1. Haz clic en uno de los enlaces arriba<br>";
            echo "2. Busca los botones 'Seleccionar Todos' y 'Deseleccionar'<br>";
            echo "3. Prueba los botones 'Meses Futuros' y 'Próximos 3 Meses'<br>";
            echo "4. Verifica que los checkboxes se marquen/desmarquen correctamente";
            echo "</div>";
            echo "</div>";
            echo "</div>";
        } else {
            echo "<div class='alert alert-warning'>";
            echo "<h6>⚠️ Cliente 'María' no encontrado</h6>";
            echo "<p>No se encontró un cliente con nombre María. Mostrando todos los clientes disponibles arriba.</p>";
            echo "</div>";
        }

        echo "</div></div>";

    } else {
        echo "<div class='alert alert-warning'>";
        echo "<h4>⚠️ No hay clientes con mensualidades</h4>";
        echo "</div>";
    }

} else {
    echo "<div class='alert alert-danger'>";
    echo "<h4>❌ Error: No hay operadores activos</h4>";
    echo "</div>";
}

echo "</div>";
echo "</body></html>";
?>