<?php
/**
 * Script de depuración para verificar el JavaScript generado
 */

require_once __DIR__ . '/config/config.php';

$csrfToken = generateCSRFToken();
$appUrl = APP_URL;

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'></head><body>";
echo "<h1>Debug JavaScript Variables</h1>";
echo "<pre>";
echo "APP_URL definido: " . (defined('APP_URL') ? 'SI' : 'NO') . "\n";
echo "APP_URL value: " . (defined('APP_URL') ? APP_URL : 'N/A') . "\n";
echo "\$appUrl variable: " . $appUrl . "\n";
echo "\$csrfToken variable: " . $csrfToken . "\n";
echo "</pre>";

echo "<h2>JavaScript que se generaría:</h2>";
echo "<pre>";

$additionalJS = <<<JS
<script>
// Variables globales
const URL_BASE = '{$appUrl}';
const CSRF_TOKEN = '{$csrfToken}';

function actualizarTasaAutomatica(btn) {
JS;
$additionalJS .= <<<'JS'

    if (!confirm('Test')) {
        return;
    }

    console.log('URL_BASE:', URL_BASE);
    console.log('CSRF_TOKEN:', CSRF_TOKEN);

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
    .then(response => response.json())
    .then(data => {
        console.log('Response:', data);
    });
}
</script>
JS;

echo htmlspecialchars($additionalJS);
echo "</pre>";

echo "<h2>Probar la función:</h2>";
echo $additionalJS;
echo '<button onclick="actualizarTasaAutomatica(this)">Test Button</button>';

echo "</body></html>";
