<?php
/**
 * Test específico para la funcionalidad de solicitudes
 */

echo "<!DOCTYPE html>";
echo "<html><head>";
echo "<title>Test Solicitudes</title>";
echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>";
echo "</head><body>";
echo "<div class='container mt-4'>";

echo "<h1>📋 Test de Funcionalidad de Solicitudes</h1>";

// Iniciar sesión como operador
session_start();
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

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

    // Test 1: Verificar tabla solicitudes_cambios
    echo "<div class='card mb-3'>";
    echo "<div class='card-header'>";
    echo "<h5>Test 1: Estructura de Tabla solicitudes_cambios</h5>";
    echo "</div>";
    echo "<div class='card-body'>";

    $columns = Database::fetchAll("SHOW COLUMNS FROM solicitudes_cambios");
    echo "<h6>Columnas encontradas:</h6>";
    echo "<table class='table table-sm'>";
    echo "<tr><th>Columna</th><th>Tipo</th><th>Nulo</th><th>Clave</th></tr>";
    foreach ($columns as $col) {
        echo "<tr>";
        echo "<td>{$col['Field']}</td>";
        echo "<td>{$col['Type']}</td>";
        echo "<td>{$col['Null']}</td>";
        echo "<td>{$col['Key']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "</div></div>";

    // Test 2: Probar consulta corregida
    echo "<div class='card mb-3'>";
    echo "<div class='card-header'>";
    echo "<h5>Test 2: Consulta SQL Corregida</h5>";
    echo "</div>";
    echo "<div class='card-body'>";

    try {
        $sql = "SELECT s.*, u.nombre_completo as solicitante_nombre
                FROM solicitudes_cambios s
                JOIN apartamento_usuario au ON au.id = s.apartamento_usuario_id
                JOIN usuarios u ON u.id = au.usuario_id
                WHERE s.estado = 'pendiente'
                ORDER BY s.fecha_solicitud DESC";

        echo "<h6>SQL Query:</h6>";
        echo "<code style='display: block; background: #f5f5f5; padding: 10px; border-radius: 4px;'>" . htmlspecialchars($sql) . "</code>";

        $solicitudes = Database::fetchAll($sql);

        echo "<h6>Resultados:</h6>";
        echo "<p class='text-success'>✅ Consulta ejecutada correctamente</p>";
        echo "<p><strong>Solicitudes pendientes:</strong> " . count($solicitudes) . "</p>";

        if (count($solicitudes) > 0) {
            echo "<table class='table table-sm'>";
            echo "<tr><th>ID</th><th>Solicitante</th><th>Tipo</th><th>Fecha</th></tr>";
            foreach ($solicitudes as $sol) {
                echo "<tr>";
                echo "<td>{$sol['id']}</td>";
                echo "<td>" . htmlspecialchars($sol['solicitante_nombre']) . "</td>";
                echo "<td>" . htmlspecialchars($sol['tipo_solicitud']) . "</td>";
                echo "<td>{$sol['fecha_solicitud']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p class='text-info'>ℹ️ No hay solicitudes pendientes</p>";
        }

    } catch (Exception $e) {
        echo "<div class='alert alert-danger'>";
        echo "<h6>Error en consulta:</h6>";
        echo "<p>" . $e->getMessage() . "</p>";
        echo "</div>";
    }

    echo "</div></div>";

    // Test 3: Probar método del controlador
    echo "<div class='card mb-3'>";
    echo "<div class='card-header'>";
    echo "<h5>Test 3: Método getSolicitudesPendientes()</h5>";
    echo "</div>";
    echo "<div class='card-body'>";

    try {
        // Cargar controlador
        require_once __DIR__ . '/app/controllers/OperadorController.php';
        require_once __DIR__ . '/app/models/Usuario.php';

        $controller = new OperadorController();

        // Usar reflexión para llamar al método privado
        $reflection = new ReflectionClass($controller);
        $method = $reflection->getMethod('getSolicitudesPendientes');
        $method->setAccessible(true);

        $solicitudes = $method->invoke($controller);

        echo "<p class='text-success'>✅ Método getSolicitudesPendientes() funciona correctamente</p>";
        echo "<p><strong>Solicitudes encontradas:</strong> " . count($solicitudes) . "</p>";

    } catch (Exception $e) {
        echo "<div class='alert alert-danger'>";
        echo "<h6>Error en método:</h6>";
        echo "<p>" . $e->getMessage() . "</p>";
        echo "</div>";
    }

    echo "</div></div>";

    // Test 4: Botón de solicitudes en dashboard
    echo "<div class='card mb-3'>";
    echo "<div class='card-header'>";
    echo "<h5>Test 4: Prueba de Página</h5>";
    echo "</div>";
    echo "<div class='card-body'>";
    echo "<p>Para probar el botón de solicitudes en el dashboard:</p>";
    echo "<ol>";
    echo "<li>Haz clic en el siguiente enlace para abrir el dashboard del operador</li>";
    echo "<li>Busca la sección 'Solicitudes Pendientes'</li>";
    echo "<li>Haz clic en el botón 'Ver Todas' o 'Revisar Ahora'</li>";
    echo "<li>Verifica que la página cargue sin errores</li>";
    echo "</ol>";
    echo "<div class='text-center mt-3'>";
    echo "<a href='operador/dashboard' target='_blank' class='btn btn-primary btn-lg'>";
    echo "<i class='bi bi-box-arrow-up-right'></i> Abrir Dashboard del Operador";
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