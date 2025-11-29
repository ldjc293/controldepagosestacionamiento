<?php
/**
 * Script de prueba para consultar tasa BCV
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Test BCV</title></head><body>";
echo "<h1>Test de Consulta BCV</h1>";
echo "<p>Consultando la tasa del BCV...</p>";

try {
    $url = 'https://www.bcv.org.ve/';

    // Inicializar cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');

    $html = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    echo "<p><strong>HTTP Code:</strong> $httpCode</p>";

    if ($error) {
        echo "<p style='color: red;'><strong>cURL Error:</strong> $error</p>";
    }

    if ($httpCode !== 200 || !$html) {
        echo "<p style='color: red;'>Error consultando BCV: HTTP $httpCode</p>";
        echo "</body></html>";
        exit;
    }

    echo "<p style='color: green;'>Página BCV obtenida correctamente</p>";
    echo "<p><strong>Tamaño del HTML:</strong> " . strlen($html) . " bytes</p>";

    // Patrones de búsqueda para extraer la tasa USD
    $patterns = [
        '/<strong>D[oó]lar.*?<\/strong>.*?<strong[^>]*>([\d,\.]+)<\/strong>/is',
        '/<div[^>]*class="[^"]*moneda[^"]*"[^>]*>.*?USD.*?<strong[^>]*>([\d,\.]+)<\/strong>/is',
        '/USD.*?<strong[^>]*>([\d,\.]+)<\/strong>/is',
        '/<td[^>]*>.*?USD.*?<\/td>.*?<td[^>]*>([\d,\.]+)<\/td>/is',
        '/<[^>]*(?:id|name)="[^"]*dolar[^"]*"[^>]*>.*?([\d,\.]+).*?<\/[^>]+>/is'
    ];

    $encontrado = false;
    foreach ($patterns as $i => $pattern) {
        if (preg_match($pattern, $html, $matches)) {
            echo "<p style='color: blue;'><strong>Patrón " . ($i + 1) . " encontró coincidencia:</strong></p>";
            echo "<pre>" . htmlspecialchars($matches[0]) . "</pre>";

            // Limpiar el número
            $tasaStr = $matches[1];
            $tasaStr = str_replace('.', '', $tasaStr);
            $tasaStr = str_replace(',', '.', $tasaStr);
            $tasa = floatval($tasaStr);

            echo "<p><strong>Tasa extraída:</strong> $tasa Bs/USD</p>";

            if ($tasa >= 1 && $tasa <= 1000) {
                echo "<p style='color: green; font-size: 20px;'><strong>✓ Tasa válida: $tasa Bs/USD</strong></p>";
                $encontrado = true;
                break;
            } else {
                echo "<p style='color: orange;'>Tasa fuera de rango razonable</p>";
            }
        }
    }

    if (!$encontrado) {
        echo "<p style='color: red;'><strong>No se pudo extraer la tasa del HTML</strong></p>";
        echo "<p>Guardando HTML para inspección...</p>";

        // Mostrar primeros 2000 caracteres del HTML
        echo "<h3>Muestra del HTML (primeros 2000 caracteres):</h3>";
        echo "<pre style='background: #f0f0f0; padding: 10px; overflow: auto; max-height: 400px;'>";
        echo htmlspecialchars(substr($html, 0, 2000));
        echo "</pre>";
    }

} catch (Exception $e) {
    echo "<p style='color: red;'><strong>Excepción:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "</body></html>";
