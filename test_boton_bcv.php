<?php
/**
 * Test específico del botón BCV
 */

require_once __DIR__ . '/config/config.php';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Botón BCV</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Test Botón Actualizar BCV</h1>

        <div class="card">
            <div class="card-body">
                <h5>Variables de Configuración</h5>
                <pre>
APP_URL: <?= APP_URL ?>
CSRF_TOKEN: <?= generateCSRFToken() ?>
Session User ID: <?= $_SESSION['user_id'] ?? 'NO LOGUEADO' ?>
Session User Rol: <?= $_SESSION['user_rol'] ?? 'NO LOGUEADO' ?>
                </pre>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-body">
                <h5>Test del Botón</h5>
                <button type="button" class="btn btn-primary" id="btnTestBCV" onclick="testActualizarBCV(this)">
                    <i class="bi bi-arrow-repeat"></i> Probar Actualización BCV
                </button>
                <div id="resultado" class="mt-3"></div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-body">
                <h5>Console Log</h5>
                <pre id="consoleLog" style="background: #000; color: #0f0; padding: 10px; max-height: 400px; overflow-y: auto;"></pre>
            </div>
        </div>
    </div>

    <script>
        const URL_BASE = '<?= APP_URL ?>';
        const CSRF_TOKEN = '<?= generateCSRFToken() ?>';

        function log(message, data = null) {
            const consoleLog = document.getElementById('consoleLog');
            const timestamp = new Date().toLocaleTimeString();
            let logMessage = `[${timestamp}] ${message}`;
            if (data) {
                logMessage += '\n' + JSON.stringify(data, null, 2);
            }
            consoleLog.textContent += logMessage + '\n';
            console.log(message, data);
        }

        log('Script iniciado');
        log('URL_BASE', URL_BASE);
        log('CSRF_TOKEN', CSRF_TOKEN);

        function testActualizarBCV(btn) {
            log('Función testActualizarBCV llamada');

            const resultado = document.getElementById('resultado');
            resultado.innerHTML = '<div class="alert alert-info">Enviando petición...</div>';

            btn.disabled = true;
            const originalHTML = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Consultando BCV...';

            log('Preparando fetch a:', URL_BASE + '/admin/actualizarTasaBCV');

            fetch(URL_BASE + '/admin/actualizarTasaBCV', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    csrf_token: CSRF_TOKEN
                })
            })
            .then(response => {
                log('Response recibido', {
                    status: response.status,
                    statusText: response.statusText,
                    ok: response.ok
                });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                return response.json();
            })
            .then(data => {
                log('Data recibido', data);

                if (data.success) {
                    resultado.innerHTML = `
                        <div class="alert alert-success">
                            <strong>✓ Éxito:</strong> ${data.message}<br>
                            <strong>Tasa:</strong> ${data.tasa}<br>
                            <strong>Fecha:</strong> ${data.fecha}<br>
                            <strong>Fuente:</strong> ${data.fuente}
                        </div>
                    `;
                } else {
                    resultado.innerHTML = `
                        <div class="alert alert-danger">
                            <strong>✗ Error:</strong> ${data.message}
                        </div>
                    `;
                }

                btn.disabled = false;
                btn.innerHTML = originalHTML;
            })
            .catch(error => {
                log('Error capturado', {
                    message: error.message,
                    stack: error.stack
                });

                resultado.innerHTML = `
                    <div class="alert alert-danger">
                        <strong>✗ Error de conexión:</strong> ${error.message}
                    </div>
                `;

                btn.disabled = false;
                btn.innerHTML = originalHTML;
            });
        }

        log('Script cargado completamente');
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
