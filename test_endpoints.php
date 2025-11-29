<?php
/**
 * Script de prueba para endpoints de configuración
 * Acceder a: http://localhost/controldepagosestacionamiento/test_endpoints.php
 */

// Cargar config.php primero (esto iniciará la sesión con las configuraciones correctas)
require_once __DIR__ . '/config/config.php';

// Verificar autenticación (SOLO PARA PRUEBAS)
if (!isset($_SESSION['user_id'])) {
    echo "<h1>⚠️ No autenticado</h1>";
    echo "<p>Por favor, inicia sesión como administrador primero:</p>";
    echo '<a href="http://localhost/controldepagosestacionamiento/auth/login">Ir a Login</a>';
    exit;
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test de Endpoints - Configuración</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .test-result { margin: 10px 0; padding: 15px; border-radius: 5px; }
        .success { background-color: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
        .error { background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
        .info { background-color: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 5px; max-height: 300px; overflow-y: auto; }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1>🧪 Test de Endpoints de Configuración</h1>
        <p class="text-muted">Usuario autenticado: <?= htmlspecialchars($_SESSION['user_email'] ?? 'N/A') ?> (<?= htmlspecialchars($_SESSION['user_rol'] ?? 'N/A') ?>)</p>
        <hr>

        <!-- Test 1: Actualizar Tasa BCV -->
        <div class="card mb-4">
            <div class="card-header">
                <h5>1️⃣ Actualizar Tasa BCV</h5>
            </div>
            <div class="card-body">
                <button class="btn btn-primary" onclick="testActualizarBCV()">
                    <i class="bi bi-arrow-repeat"></i> Probar Actualización BCV
                </button>
                <div id="result-bcv" class="mt-3"></div>
            </div>
        </div>

        <!-- Test 2: Limpiar Caché -->
        <div class="card mb-4">
            <div class="card-header">
                <h5>2️⃣ Limpiar Caché</h5>
            </div>
            <div class="card-body">
                <button class="btn btn-secondary" onclick="testLimpiarCache()">
                    <i class="bi bi-trash"></i> Probar Limpiar Caché
                </button>
                <div id="result-cache" class="mt-3"></div>
            </div>
        </div>

        <!-- Test 3: Verificar Integridad -->
        <div class="card mb-4">
            <div class="card-header">
                <h5>3️⃣ Verificar Integridad</h5>
            </div>
            <div class="card-body">
                <button class="btn btn-info" onclick="testVerificarIntegridad()">
                    <i class="bi bi-check2-square"></i> Probar Verificación
                </button>
                <div id="result-integridad" class="mt-3"></div>
            </div>
        </div>

        <!-- Test 4: Exportar BD -->
        <div class="card mb-4">
            <div class="card-header">
                <h5>4️⃣ Exportar Base de Datos</h5>
            </div>
            <div class="card-body">
                <a href="<?= url('admin/exportarBaseDatos?csrf_token=' . generateCSRFToken()) ?>"
                   class="btn btn-success"
                   target="_blank">
                    <i class="bi bi-download"></i> Descargar Backup
                </a>
                <p class="text-muted mt-2">Este botón descargará directamente el archivo</p>
            </div>
        </div>
    </div>

    <script>
        const URL_BASE = '<?= APP_URL ?>';
        const CSRF_TOKEN = '<?= generateCSRFToken() ?>';

        async function testActualizarBCV() {
            const resultDiv = document.getElementById('result-bcv');
            resultDiv.innerHTML = '<div class="info test-result">⏳ Consultando BCV...</div>';

            try {
                const response = await fetch(URL_BASE + '/admin/actualizarTasaBCV', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ csrf_token: CSRF_TOKEN })
                });

                const data = await response.json();

                if (data.success) {
                    resultDiv.innerHTML = `
                        <div class="success test-result">
                            <strong>✅ Éxito:</strong> ${data.message}
                            <pre>${JSON.stringify(data, null, 2)}</pre>
                        </div>
                    `;
                } else {
                    resultDiv.innerHTML = `
                        <div class="error test-result">
                            <strong>❌ Error:</strong> ${data.message}
                            <pre>${JSON.stringify(data, null, 2)}</pre>
                        </div>
                    `;
                }
            } catch (error) {
                resultDiv.innerHTML = `
                    <div class="error test-result">
                        <strong>❌ Error de conexión:</strong> ${error.message}
                    </div>
                `;
            }
        }

        async function testLimpiarCache() {
            const resultDiv = document.getElementById('result-cache');
            resultDiv.innerHTML = '<div class="info test-result">⏳ Limpiando caché...</div>';

            try {
                const response = await fetch(URL_BASE + '/admin/limpiarCache', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ csrf_token: CSRF_TOKEN })
                });

                const data = await response.json();

                if (data.success) {
                    resultDiv.innerHTML = `
                        <div class="success test-result">
                            <strong>✅ Éxito:</strong> ${data.message}
                            <pre>${JSON.stringify(data, null, 2)}</pre>
                        </div>
                    `;
                } else {
                    resultDiv.innerHTML = `
                        <div class="error test-result">
                            <strong>❌ Error:</strong> ${data.message}
                            <pre>${JSON.stringify(data, null, 2)}</pre>
                        </div>
                    `;
                }
            } catch (error) {
                resultDiv.innerHTML = `
                    <div class="error test-result">
                        <strong>❌ Error:</strong> ${error.message}
                    </div>
                `;
            }
        }

        async function testVerificarIntegridad() {
            const resultDiv = document.getElementById('result-integridad');
            resultDiv.innerHTML = '<div class="info test-result">⏳ Verificando integridad...</div>';

            try {
                const response = await fetch(URL_BASE + '/admin/verificarIntegridad', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ csrf_token: CSRF_TOKEN })
                });

                const data = await response.json();

                const className = data.success ? 'success' : 'error';
                const icon = data.success ? '✅' : '⚠️';

                resultDiv.innerHTML = `
                    <div class="${className} test-result">
                        <strong>${icon} Resultado:</strong>
                        <pre style="white-space: pre-wrap;">${data.message}</pre>
                    </div>
                `;
            } catch (error) {
                resultDiv.innerHTML = `
                    <div class="error test-result">
                        <strong>❌ Error:</strong> ${error.message}
                    </div>
                `;
            }
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
