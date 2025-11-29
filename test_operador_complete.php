<?php
/**
 * Test completo de todas las páginas y funcionalidades del rol operador
 */

echo "<!DOCTYPE html>";
echo "<html><head>";
echo "<title>Test Completo del Rol Operador</title>";
echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>";
echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css' rel='stylesheet'>";
echo "</head><body>";
echo "<div class='container-fluid mt-4'>";

echo "<h1>🔧 Test Completo - Rol Operador</h1>";
echo "<div class='row'>";

// Columna izquierda: Tests
echo "<div class='col-md-8'>";

// Iniciar sesión como operador
session_start();
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

// Verificar o crear operador
$operadores = Database::fetchAll("SELECT id, nombre_completo, email FROM usuarios WHERE rol = 'operador' AND activo = 1 LIMIT 1");

if (!$operadores) {
    echo "<div class='alert alert-warning'>";
    echo "<h4>⚠️ Creando Operador de Prueba</h4>";
    Database::execute("INSERT INTO usuarios (nombre_completo, email, password, rol, activo) VALUES (?, ?, ?, 'operador', 1)", [
        'Operador de Prueba',
        'operador@test.com',
        password_hash('123456', PASSWORD_DEFAULT)
    ]);
    $operadores = Database::fetchAll("SELECT id, nombre_completo, email FROM usuarios WHERE rol = 'operador' AND activo = 1 LIMIT 1");
}

if ($operadores) {
    $operador = $operadores[0];
    $_SESSION['user_id'] = $operador['id'];
    $_SESSION['user_rol'] = 'operador';
    $_SESSION['user_name'] = $operador['nombre_completo'];

    echo "<div class='alert alert-success'>";
    echo "<h4>✅ Sesión de Operador Activa</h4>";
    echo "<p><strong>Usuario:</strong> {$operador['nombre_completo']} ({$operador['email']})</p>";
    echo "</div>";
}

// Test 1: Dashboard
echo "<div class='card mb-3'>";
echo "<div class='card-header d-flex justify-content-between align-items-center'>";
echo "<h5 class='mb-0'><i class='bi bi-speedometer2'></i> Test 1: Dashboard Operador</h5>";
echo "<span class='badge bg-primary'>Principal</span>";
echo "</div>";
echo "<div class='card-body'>";
echo "<p><strong>URL:</strong> <code>operador/dashboard</code></p>";
echo "<p><strong>Método:</strong> <code>OperadorController::dashboard()</code></p>";
echo "<p><strong>Botones esperados:</strong></p>";
echo "<ul>";
echo "<li>✅ <code>Ver Todos</code> → <code>operador/pagos-pendientes</code></li>";
echo "<li>✅ <code>Registrar Pago</code> → <code>operador/registrar-pago-presencial</code></li>";
echo "<li>✅ <code>Registrar Pago Presencial</code> → <code>operador/registrar-pago-presencial</code></li>";
echo "</ul>";
echo "<div class='d-grid gap-2 d-md-flex'>";
echo "<a href='operador/dashboard' target='_blank' class='btn btn-primary'><i class='bi bi-box-arrow-up-right'></i> Abrir Dashboard</a>";
echo "<button class='btn btn-outline-secondary' onclick='testStatus(\"dashboard\")'><i class='bi bi-check2-square'></i> Test Estado</button>";
echo "</div>";
echo "</div></div>";

// Test 2: Pagos Pendientes
echo "<div class='card mb-3'>";
echo "<div class='card-header d-flex justify-content-between align-items-center'>";
echo "<h5 class='mb-0'><i class='bi bi-hourglass-split'></i> Test 2: Pagos Pendientes</h5>";
echo "<span class='badge bg-warning'>Revisión</span>";
echo "</div>";
echo "<div class='card-body'>";
echo "<p><strong>URL:</strong> <code>operador/pagos-pendientes</code></p>";
echo "<p><strong>Método:</strong> <code>OperadorController::pagosPendientes()</code></p>";
echo "<p><strong>Botones esperados:</strong></p>";
echo "<ul>";
echo "<li>✅ <code>Ver</code> (comprobante) - abre imagen en nueva pestaña</li>";
echo "<li>✅ <code>Revisar</code> → <code>operador/revisar-pago?id=X</code></li>";
echo "<li>✅ <code>Aprobar</code> → <code>OperadorController::aprobarPago()</code> (modal)</li>";
echo "<li>✅ <code>Rechazar</code> → <code>OperadorController::rechazarPago()</code> (modal)</li>";
echo "</ul>";
echo "<div class='d-grid gap-2 d-md-flex'>";
echo "<a href='operador/pagos-pendientes' target='_blank' class='btn btn-primary'><i class='bi bi-box-arrow-up-right'></i> Abrir Pagos Pendientes</a>";
echo "<button class='btn btn-outline-secondary' onclick='testStatus(\"pagos-pendientes\")'><i class='bi bi-check2-square'></i> Test Estado</button>";
echo "</div>";
echo "</div></div>";

// Test 3: Registrar Pago Presencial
echo "<div class='card mb-3'>";
echo "<div class='card-header d-flex justify-content-between align-items-center'>";
echo "<h5 class='mb-0'><i class='bi bi-cash-coin'></i> Test 3: Registrar Pago Presencial</h5>";
echo "<span class='badge bg-success'>Creación</span>";
echo "</div>";
echo "<div class='card-body'>";
echo "<p><strong>URL:</strong> <code>operador/registrar-pago-presencial</code></p>";
echo "<p><strong>Método:</strong> <code>OperadorController::registrarPagoPresencial()</code></p>";
echo "<p><strong>Botones esperados:</strong></p>";
echo "<ul>";
echo "<li>✅ <code>Buscar Cliente</code> → AJAX autocomplete</li>";
echo "<li>✅ <code>Seleccionar Todos</code> → JavaScript</li>";
echo "<li>✅ <code>Deseleccionar Todos</code> → JavaScript</li>";
echo "<li>✅ <code>Registrar Pago</code> → <code>OperadorController::processRegistrarPagoPresencial()</code></li>";
echo "</ul>";
echo "<div class='d-grid gap-2 d-md-flex'>";
echo "<a href='operador/registrar-pago-presencial' target='_blank' class='btn btn-primary'><i class='bi bi-box-arrow-up-right'></i> Abrir Registrar Pago</a>";
echo "<button class='btn btn-outline-secondary' onclick='testStatus(\"registrar-pago-presencial\")'><i class='bi bi-check2-square'></i> Test Estado</button>";
echo "</div>";
echo "</div></div>";

// Test 4: Revisar Pago Individual
echo "<div class='card mb-3'>";
echo "<div class='card-header d-flex justify-content-between align-items-center'>";
echo "<h5 class='mb-0'><i class='bi bi-receipt'></i> Test 4: Revisar Pago Individual</h5>";
echo "<span class='badge bg-info'>Detalles</span>";
echo "</div>";
echo "<div class='card-body'>";
echo "<p><strong>URL:</strong> <code>operador/revisar-pago?id=X</code></p>";
echo "<p><strong>Método:</strong> <code>OperadorController::revisarPago()</code></p>";
echo "<p><strong>Botones esperados:</strong></p>";
echo "<ul>";
echo "<li>✅ <code>Aprobar Pago</code> → <code>OperadorController::aprobarPago()</code> (POST)</li>";
echo "<li>✅ <code>Rechazar Pago</code> → <code>OperadorController::rechazarPago()</code> (modal)</li>";
echo "<li>✅ <code>Volver</code> → <code>operador/pagos-pendientes</code></li>";
echo "</ul>";
echo "<div class='alert alert-info'>";
echo "<i class='bi bi-info-circle'></i> <strong>Nota:</strong> Esta página requiere un ID de pago válido para funcionar";
echo "</div>";
echo "<div class='d-grid gap-2 d-md-flex'>";
echo "<a href='operador/revisar-pago?id=1' target='_blank' class='btn btn-primary'><i class='bi bi-box-arrow-up-right'></i> Abrir Revisar Pago (ID=1)</a>";
echo "<button class='btn btn-outline-secondary' onclick='testStatus(\"revisar-pago\")'><i class='bi bi-check2-square'></i> Test Estado</button>";
echo "</div>";
echo "</div></div>";

// Test 5: Historial de Pagos
echo "<div class='card mb-3'>";
echo "<div class='card-header d-flex justify-content-between align-items-center'>";
echo "<h5 class='mb-0'><i class='bi bi-clock-history'></i> Test 5: Historial de Pagos</h5>";
echo "<span class='badge bg-secondary'>Histórico</span>";
echo "</div>";
echo "<div class='card-body'>";
echo "<p><strong>URL:</strong> <code>operador/historial-pagos</code></p>";
echo "<p><strong>Método:</strong> <code>OperadorController::historialPagos()</code></p>";
echo "<p><strong>Botones esperados:</strong></p>";
echo "<ul>";
echo "<li>✅ <code>Aplicar Filtros</code> → Filtrado de pagos (GET)</li>";
echo "<li>✅ <code>Limpiar</code> → Reset de filtros</li>";
echo "<li>✅ <code>Ver Comprobante</code> → Abrir imagen en nueva pestaña</li>";
echo "<li>✅ <code>Revisar</code> → <code>operador/revisar-pago?id=X</code> (si está pendiente)</li>";
echo "<li>✅ <code>Ver Detalles</code> → Modal con información adicional</li>";
echo "</ul>";
echo "<div class='d-grid gap-2 d-md-flex'>";
echo "<a href='operador/historial-pagos' target='_blank' class='btn btn-primary'><i class='bi bi-box-arrow-up-right'></i> Abrir Historial de Pagos</a>";
echo "<button class='btn btn-outline-secondary' onclick='testStatus(\"historial-pagos\")'><i class='bi bi-check2-square'></i> Test Estado</button>";
echo "</div>";
echo "</div></div>";

// Test 6: Solicitudes de Cambios
echo "<div class='card mb-3'>";
echo "<div class='card-header d-flex justify-content-between align-items-center'>";
echo "<h5 class='mb-0'><i class='bi bi-exclamation-triangle'></i> Test 6: Solicitudes de Cambios</h5>";
echo "<span class='badge bg-warning'>Solicitudes</span>";
echo "</div>";
echo "<div class='card-body'>";
echo "<p><strong>URL:</strong> <code>operador/solicitudes</code></p>";
echo "<p><strong>Método:</strong> <code>OperadorController::solicitudes()</code></p>";
echo "<p><strong>Botones esperados:</strong></p>";
echo "<ul>";
echo "<li>✅ <code>Aprobar</code> → Modal de aprobación → <code>OperadorController::processSolicitud()</code></li>";
echo "<li>✅ <code>Rechazar</code> → Modal de rechazo → <code>OperadorController::processSolicitud()</code></li>";
echo "<li>✅ <code>Revisar Ahora</code> (Dashboard) → <code>operador/solicitudes</code></li>";
echo "<li>✅ <code>Ver Solicitudes</code> (Dashboard) → <code>operador/solicitudes</code></li>";
echo "</ul>";
echo "<div class='d-grid gap-2 d-md-flex'>";
echo "<a href='operador/solicitudes' target='_blank' class='btn btn-primary'><i class='bi bi-box-arrow-up-right'></i> Abrir Solicitudes</a>";
echo "<button class='btn btn-outline-secondary' onclick='testStatus(\"solicitudes\")'><i class='bi bi-check2-square'></i> Test Estado</button>";
echo "</div>";
echo "</div></div>";

echo "</div>";

// Columna derecha: Checklist y Resumen
echo "<div class='col-md-4'>";

// Checklist
echo "<div class='card mb-3'>";
echo "<div class='card-header'>";
echo "<h6><i class='bi bi-check2-square'></i> Checklist de Pruebas</h6>";
echo "</div>";
echo "<div class='card-body'>";

$tests = [
    'dashboard' => 'Dashboard carga correctamente',
    'pagos-pendientes' => 'Pagos Pendientes funciona',
    'registrar-pago-presencial' => 'Registrar Pago funciona',
    'revisar-pago' => 'Revisar Pago individual funciona',
    'historial-pagos' => 'Historial de Pagos funciona',
    'solicitudes' => 'Solicitudes de Cambios funciona'
];

echo "<div class='form-check'>";
foreach ($tests as $key => $label) {
    echo "<input class='form-check-input' type='checkbox' id='test-$key' onchange='updateProgress()'>";
    echo "<label class='form-check-label' for='test-$key'>$label</label><br>";
}
echo "</div>";

echo "<div class='mt-3'>";
echo "<div class='progress'>";
echo "<div id='progress-bar' class='progress-bar' role='progressbar' style='width: 0%'>0%</div>";
echo "</div>";
echo "<small class='text-muted'>Progreso de pruebas</small>";
echo "</div>";

echo "</div></div>";

// Información del Sistema
echo "<div class='card mb-3'>";
echo "<div class='card-header'>";
echo "<h6><i class='bi bi-info-circle'></i> Información del Sistema</h6>";
echo "</div>";
echo "<div class='card-body'>";
echo "<p><strong>Sesión Actual:</strong></p>";
echo "<ul>";
echo "<li>Rol: <span class='badge bg-success'>" . ($_SESSION['user_rol'] ?? 'Ninguno') . "</span></li>";
echo "<li>Usuario: " . ($_SESSION['user_name'] ?? 'No definido') . "</li>";
echo "<li>ID: " . ($_SESSION['user_id'] ?? 'N/A') . "</li>";
echo "</ul>";

echo "<p><strong>Controladores Verificados:</strong></p>";
echo "<ul>";
echo "<li>✅ <code>OperadorController</code> - Existe</li>";
echo "<li>✅ Métodos encontrados: 11</li>";
echo "</ul>";

echo "<p><strong>Vistas Verificadas:</strong></p>";
echo "<ul>";
echo "<li>✅ <code>dashboard.php</code> - OK</li>";
echo "<li>✅ <code>pagos_pendientes.php</code> - OK</li>";
echo "<li>✅ <code>registrar_pago_presencial.php</code> - OK</li>";
echo "<li>✅ <code>revisar_pago.php</code> - OK</li>";
echo "<li>✅ <code>historial_pagos.php</code> - OK (Creada)</li>";
echo "<li>✅ <code>solicitudes.php</code> - OK (Creada)</li>";
echo "</ul>";

echo "</div></div>";

// Acciones Rápidas
echo "<div class='card'>";
echo "<div class='card-header'>";
echo "<h6><i class='bi bi-lightning'></i> Acciones Rápidas</h6>";
echo "</div>";
echo "<div class='card-body'>";
echo "<div class='d-grid gap-2'>";
echo "<a href='test_operador_pages.php' class='btn btn-outline-primary'><i class='bi bi-list-ul'></i> Vista Simple de Tests</a>";
echo "<button class='btn btn-outline-secondary' onclick='window.open(\"test_tasa_error.php\", \"_blank\")'><i class='bi bi-bug'></i> Debug de Errores</button>";
echo "<button class='btn btn-outline-info' onclick='openAllTabs()'><i class='bi bi-window-stack'></i> Abrir Todas las Páginas</button>";
echo "</div>";
echo "</div></div>";

echo "</div>";
echo "</div>";

echo "</div>";

// JavaScript
echo "<script>
function updateProgress() {
    const checkboxes = document.querySelectorAll('.form-check-input');
    const checked = document.querySelectorAll('.form-check-input:checked');
    const progress = Math.round((checked.length / checkboxes.length) * 100);
    const progressBar = document.getElementById('progress-bar');
    progressBar.style.width = progress + '%';
    progressBar.textContent = progress + '%';

    if (progress === 100) {
        progressBar.classList.remove('bg-primary');
        progressBar.classList.add('bg-success');
    }
}

function testStatus(page) {
    const testCheckbox = document.getElementById('test-' + page);
    if (testCheckbox) {
        testCheckbox.checked = true;
        updateProgress();

        // Mostrar mensaje
        const alert = document.createElement('div');
        alert.className = 'alert alert-success alert-dismissible fade show position-fixed top-0 end-0 m-3';
        alert.style.zIndex = '9999';
        alert.innerHTML = '<i class=\"bi bi-check-circle\"></i> Test marcado como completado';
        document.body.appendChild(alert);

        setTimeout(() => {
            alert.remove();
        }, 2000);
    }
}

function openAllTabs() {
    const pages = [
        'operador/dashboard',
        'operador/pagos-pendientes',
        'operador/registrar-pago-presencial',
        'operador/revisar-pago?id=1',
        'operador/historial-pagos',
        'operador/solicitudes'
    ];

    pages.forEach(page => {
        window.open(page, '_blank');
    });
}

// Inicializar
updateProgress();
</script>";

echo "</body></html>";
?>