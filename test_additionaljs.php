<?php
/**
 * Test para verificar que $additionalJS funciona
 */

// Simular lo que hace configuracion.php
$csrfToken = 'test_token_123';
$appUrl = 'http://localhost/test';

$additionalJS = <<<JS
<script>
console.log('TEST: additionalJS está funcionando');
const URL_BASE = '{$appUrl}';
const CSRF_TOKEN = '{$csrfToken}';
console.log('URL_BASE:', URL_BASE);
console.log('CSRF_TOKEN:', CSRF_TOKEN);
</script>
JS;

echo "<!DOCTYPE html><html><head><title>Test</title></head><body>";
echo "<h1>Test de \$additionalJS</h1>";

echo "<h2>Valor de \$additionalJS:</h2>";
echo "<pre>" . htmlspecialchars($additionalJS) . "</pre>";

echo "<h2>Verificación de isset(\$additionalJS):</h2>";
echo "<p>isset(\$additionalJS): " . (isset($additionalJS) ? 'TRUE' : 'FALSE') . "</p>";

echo "<h2>Incluir el JavaScript:</h2>";
if (isset($additionalJS)) {
    echo $additionalJS;
} else {
    echo "<p style='color: red;'>ERROR: \$additionalJS no está definido</p>";
}

echo "</body></html>";
