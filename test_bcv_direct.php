<?php
/**
 * Test directo para verificar la extracción de tasa BCV
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Test BCV Directo</title></head><body>";
echo "<h1>Test de Extracción de Tasa BCV</h1>";

try {
    $url = 'https://www.bcv.org.ve/';

    echo "<h2>1. Consultando página del BCV...</h2>";

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
        echo "<p style='color: red;'>Error: No se pudo obtener la página del BCV</p>";
        exit;
    }

    echo "<p style='color: green;'>✓ Página obtenida correctamente (" . strlen($html) . " bytes)</p>";

    echo "<h2>2. Probando patrones de extracción...</h2>";

    // Patrones de búsqueda para extraer la tasa USD
    $patterns = [
        1 => '/<strong>D[oó]lar.*?<\/strong>.*?<strong[^>]*>\s*([\d,\.]+)\s*<\/strong>/is',
        2 => '/<div[^>]*class="[^"]*moneda[^"]*"[^>]*>.*?USD.*?<strong[^>]*>\s*([\d,\.]+)\s*<\/strong>/is',
        3 => '/USD.*?<strong[^>]*>\s*([\d,\.]+)\s*<\/strong>/is',
        4 => '/<div[^>]*id="dolar"[^>]*>.*?<strong[^>]*>\s*([\d,\.]+)\s*<\/strong>/is',
        5 => '/<td[^>]*>.*?USD.*?<\/td>.*?<td[^>]*>\s*([\d,\.]+)\s*<\/td>/is'
    ];

    $encontrado = false;

    foreach ($patterns as $i => $pattern) {
        echo "<h3>Patrón $i:</h3>";
        echo "<pre>" . htmlspecialchars($pattern) . "</pre>";

        if (preg_match($pattern, $html, $matches)) {
            echo "<p style='color: blue;'><strong>✓ Coincidencia encontrada!</strong></p>";
            echo "<p><strong>Match completo:</strong></p>";
            echo "<pre style='background: #f0f0f0; padding: 10px;'>" . htmlspecialchars(substr($matches[0], 0, 500)) . "</pre>";
            echo "<p><strong>Número extraído (raw):</strong> [" . htmlspecialchars($matches[1]) . "]</p>";

            // Limpiar el número
            $tasaStr = trim($matches[1]);
            echo "<p><strong>Después de trim:</strong> [" . htmlspecialchars($tasaStr) . "]</p>";

            $tasaStr = str_replace('.', '', $tasaStr);
            echo "<p><strong>Sin puntos:</strong> [" . htmlspecialchars($tasaStr) . "]</p>";

            $tasaStr = str_replace(',', '.', $tasaStr);
            echo "<p><strong>Coma por punto:</strong> [" . htmlspecialchars($tasaStr) . "]</p>";

            $tasa = floatval($tasaStr);
            echo "<p><strong>Como float:</strong> " . $tasa . "</p>";

            if ($tasa >= 1 && $tasa <= 100000) {
                echo "<p style='color: green; font-size: 20px;'><strong>✓✓✓ TASA VÁLIDA: $tasa Bs/USD</strong></p>";
                $encontrado = true;
                break;
            } else {
                echo "<p style='color: orange;'>✗ Tasa fuera de rango válido (1-100000)</p>";
            }
        } else {
            echo "<p style='color: gray;'>✗ Sin coincidencia</p>";
        }
        echo "<hr>";
    }

    if (!$encontrado) {
        echo "<h2 style='color: red;'>No se pudo extraer una tasa válida</h2>";
        echo "<h3>Buscando fragmentos con 'USD' o 'dolar':</h3>";

        // Buscar manualmente en el HTML
        if (preg_match_all('/(<div[^>]*(?:dolar|USD)[^>]*>.*?<\/div>)/is', $html, $divMatches, PREG_SET_ORDER)) {
            echo "<p>Encontrados " . count($divMatches) . " divs con 'dolar' o 'USD':</p>";
            foreach (array_slice($divMatches, 0, 3) as $i => $match) {
                echo "<h4>Fragmento " . ($i + 1) . ":</h4>";
                echo "<pre style='background: #f0f0f0; padding: 10px; max-height: 300px; overflow: auto;'>";
                echo htmlspecialchars($match[0]);
                echo "</pre>";
            }
        }
    }

} catch (Exception $e) {
    echo "<p style='color: red;'><strong>Excepción:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "</body></html>";
