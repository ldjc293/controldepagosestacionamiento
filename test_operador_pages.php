<?php
/**
 * Script de prueba para todas las páginas del rol operador
 */

echo "<!DOCTYPE html>";
echo "<html><head>";
echo "<title>Test de Páginas de Operador</title>";
echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>";
echo "</head><body>";
echo "<div class='container mt-4'>";

echo "<h1>🔧 Test de Páginas y Botones del Rol Operador</h1>";

// Iniciar sesión como operador
session_start();
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

// Buscar un operador
$operadores = Database::fetchAll("SELECT id, nombre_completo, email FROM usuarios WHERE rol = 'operador' AND activo = 1 LIMIT 1");

if (!$operadores) {
    echo "<div class='alert alert-warning'>";
    echo "<h4>⚠️ No hay operadores activos</h4>";
    echo "<p>Creando un operador de prueba...</p>";

    // Crear operador de prueba
    Database::execute("INSERT INTO usuarios (nombre_completo, email, password, rol, activo) VALUES (?, ?, ?, 'operador', 1)", [
        'Operador de Prueba',
        'operador@test.com',
        password_hash('123456', PASSWORD_DEFAULT)
    ]);

    $operadores = Database::fetchAll("SELECT id, nombre_completo, email FROM usuarios WHERE rol = 'operador' AND activo = 1 LIMIT 1");

    if ($operadores) {
        echo "<p class='text-success'>✅ Operador de prueba creado</p>";
    }
}

if ($operadores) {
    $operador = $operadores[0];
    $_SESSION['user_id'] = $operador['id'];
    $_SESSION['user_rol'] = 'operador';
    $_SESSION['user_name'] = $operador['nombre_completo'];

    echo "<div class='alert alert-success'>";
    echo "<h4>✅ Sesión de Operador Establecida</h4>";
    echo "<p><strong>Nombre:</strong> {$operador['nombre_completo']}</p>";
    echo "<p><strong>Email:</strong> {$operador['email']}</p>";
    echo "</div>";

    echo "<div class='row'>";
    echo "<div class='col-md-6'>";

    // Lista de páginas del operador
    $paginasOperador = [
        [
            'nombre' => 'Dashboard',
            'url' => 'operador/dashboard',
            'descripcion' => 'Panel principal del operador',
            'botones_esperados' => ['Ver Todo', 'Registrar Pago']
        ],
        [
            'nombre' => 'Pagos Pendientes',
            'url' => 'operador/pagos-pendientes',
            'descripcion' => 'Lista de pagos pendientes de aprobación',
            'botones_esperados' => ['Aprobar', 'Rechazar', 'Ver Detalles']
        ],
        [
            'nombre' => 'Registrar Pago Presencial',
            'url' => 'operador/registrar-pago-presencial',
            'descripcion' => 'Formulario para registrar pagos en persona',
            'botones_esperados' => ['Buscar Cliente', 'Seleccionar Todo', 'Registrar Pago']
        ],
        [
            'nombre' => 'Revisar Pago',
            'url' => 'operador/revisar-pago',
            'descripcion' => 'Revisión detallada de pagos específicos',
            'botones_esperados' => ['Aprobar', 'Rechazar', 'Volver']
        ]
    ];

    echo "<h3>📋 Páginas Disponibles del Rol Operador</h3>";
    echo "<div class='list-group mb-4'>";

    foreach ($paginasOperador as $pagina) {
        echo "<div class='list-group-item'>";
        echo "<h5 class='mb-1'>{$pagina['nombre']}</h5>";
        echo "<p class='mb-1'>{$pagina['descripcion']}</p>";
        echo "<small><strong>Botones esperados:</strong> " . implode(', ', $pagina['botones_esperados']) . "</small><br>";
        echo "<a href='{$pagina['url']}' target='_blank' class='btn btn-primary btn-sm mt-2'>Abrir Página</a>";
        echo "</div>";
    }

    echo "</div>";
    echo "</div>";

    echo "<div class='col-md-6'>";
    echo "<h3>🧪 Pruebas Automáticas</h3>";

    // Test 1: Dashboard
    echo "<div class='card mb-3'>";
    echo "<div class='card-body'>";
    echo "<h5>Test 1: Acceso a Dashboard</h5>";
    echo "<p><a href='operador/dashboard' target='_blank' class='btn btn-outline-primary'>Test Dashboard</a></p>";
    echo "</div></div>";

    // Test 2: Pagos Pendientes
    echo "<div class='card mb-3'>";
    echo "<div class='card-body'>";
    echo "<h5>Test 2: Pagos Pendientes</h5>";
    echo "<p><a href='operador/pagos-pendientes' target='_blank' class='btn btn-outline-primary'>Test Pagos Pendientes</a></p>";
    echo "</div></div>";

    // Test 3: Registrar Pago
    echo "<div class='card mb-3'>";
    echo "<div class='card-body'>";
    echo "<h5>Test 3: Registrar Pago Presencial</h5>";
    echo "<p><a href='operador/registrar-pago-presencial' target='_blank' class='btn btn-outline-primary'>Test Registrar Pago</a></p>";
    echo "</div></div>";

    // Test 4: Verificación de rutas
    echo "<div class='card mb-3'>";
    echo "<div class='card-body'>";
    echo "<h5>Test 4: Verificación de Rutas del Router</h5>";
    echo "<p>Las rutas del operador están configuradas en:</p>";
    echo "<ul>";
    echo "<li><code>operador/dashboard</code> → ClienteController::dashboard()</li>";
    echo "<li><code>operador/pagos-pendientes</code> → OperadorController::pagosPendientes()</li>";
    echo "<li><code>operador/registrar-pago-presencial</code> → OperadorController::registrarPagoPresencial()</li>";
    echo "<li><code>operador/revisar-pago</code> → OperadorController::revisarPago()</li>";
    echo "</ul>";
    echo "</div></div>";

    echo "</div>";
    echo "</div>";

    echo "<div class='alert alert-info'>";
    echo "<h4>📝 Instrucciones de Prueba</h4>";
    echo "<ol>";
    echo "<li>Haz clic en cada enlace para abrir la página en una nueva pestaña</li>";
    echo "<li>Verifica que la página cargue sin errores (500, 404, página en rosa, etc.)</li>";
    echo "<li>Verifica que todos los botones esperados estén presentes y funcionen</li>";
    echo "<li>Prueba cada botón para confirmar que realiza la acción esperada</li>";
    echo "<li>Reporta cualquier error o mal funcionamiento</li>";
    echo "</ol>";
    echo "</div>";

} else {
    echo "<div class='alert alert-danger'>";
    echo "<h4>❌ Error: No se pudo crear ni encontrar operadores</h4>";
    echo "<p>No es posible realizar las pruebas sin una cuenta de operador activa.</p>";
    echo "</div>";
}

echo "</div>";
echo "</body></html>";
?>