<?php
/**
 * Test para simular la actualización de tasa BCV
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/app/helpers/functions.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Test Actualizar BCV</title></head><body>";
echo "<h1>Test de Actualización de Tasa BCV</h1>";

echo "<h2>1. Estado actual de la base de datos</h2>";
$sql = "SELECT * FROM tasa_cambio_bcv ORDER BY fecha_registro DESC LIMIT 1";
$tasaActual = Database::fetchOne($sql);

if ($tasaActual) {
    echo "<div style='background: #f0f0f0; padding: 10px; margin: 10px 0;'>";
    echo "<strong>Última tasa registrada:</strong><br>";
    echo "Tasa: " . $tasaActual['tasa_usd_bs'] . " Bs/USD<br>";
    echo "Fecha: " . $tasaActual['fecha_registro'] . "<br>";
    echo "Fuente: " . $tasaActual['fuente'] . "<br>";
    echo "</div>";
} else {
    echo "<p>No hay tasas registradas</p>";
}

echo "<h2>2. Consultando tasa desde BCV...</h2>";

try {
    $url = 'https://www.bcv.org.ve/';

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
    curl_close($ch);

    if ($httpCode !== 200 || !$html) {
        echo "<p style='color: red;'>Error consultando BCV: HTTP $httpCode</p>";
    } else {
        echo "<p style='color: green;'>✓ Página obtenida correctamente</p>";

        $patterns = [
            '/<strong>D[oó]lar.*?<\/strong>.*?<strong[^>]*>\s*([\d,\.]+)\s*<\/strong>/is',
            '/<div[^>]*class="[^"]*moneda[^"]*"[^>]*>.*?USD.*?<strong[^>]*>\s*([\d,\.]+)\s*<\/strong>/is',
            '/USD.*?<strong[^>]*>\s*([\d,\.]+)\s*<\/strong>/is',
            '/<div[^>]*id="dolar"[^>]*>.*?<strong[^>]*>\s*([\d,\.]+)\s*<\/strong>/is',
            '/<td[^>]*>.*?USD.*?<\/td>.*?<td[^>]*>\s*([\d,\.]+)\s*<\/td>/is'
        ];

        $tasaExtraida = null;
        $patronUsado = null;

        foreach ($patterns as $i => $pattern) {
            if (preg_match($pattern, $html, $matches)) {
                $tasaStr = trim($matches[1]);
                $tasaStr = str_replace('.', '', $tasaStr);
                $tasaStr = str_replace(',', '.', $tasaStr);
                $tasa = floatval($tasaStr);

                if ($tasa >= 1 && $tasa <= 100000) {
                    $tasaExtraida = $tasa;
                    $patronUsado = $i + 1;
                    echo "<p style='color: green;'><strong>✓ Tasa extraída: $tasa Bs/USD (patrón $patronUsado)</strong></p>";
                    break;
                }
            }
        }

        if ($tasaExtraida) {
            echo "<h2>3. Insertando en base de datos...</h2>";

            $sql = "INSERT INTO tasa_cambio_bcv (tasa_usd_bs, fecha_registro, registrado_por, fuente)
                    VALUES (?, NOW(), 1, 'BCV Automático - Test')";

            try {
                Database::execute($sql, [$tasaExtraida]);
                echo "<p style='color: green; font-size: 18px;'><strong>✓✓✓ ÉXITO: Tasa guardada en base de datos!</strong></p>";

                // Verificar que se guardó
                $sql = "SELECT * FROM tasa_cambio_bcv ORDER BY fecha_registro DESC LIMIT 1";
                $nuevaTasa = Database::fetchOne($sql);

                echo "<div style='background: #d4edda; padding: 15px; border: 2px solid #28a745; margin: 10px 0;'>";
                echo "<strong>Nueva tasa registrada:</strong><br>";
                echo "Tasa: " . $nuevaTasa['tasa_usd_bs'] . " Bs/USD<br>";
                echo "Fecha: " . $nuevaTasa['fecha_registro'] . "<br>";
                echo "Fuente: " . $nuevaTasa['fuente'] . "<br>";
                echo "</div>";

            } catch (Exception $e) {
                echo "<p style='color: red;'><strong>Error al guardar:</strong> " . $e->getMessage() . "</p>";
            }

        } else {
            echo "<p style='color: red;'><strong>✗ No se pudo extraer una tasa válida</strong></p>";
        }
    }

} catch (Exception $e) {
    echo "<p style='color: red;'><strong>Excepción:</strong> " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='admin/configuracion'>Ver página de configuración</a></p>";
echo "</body></html>";
