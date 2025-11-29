<?php
/**
 * Actualización manual de tasa BCV
 * Este script simula lo que haría el botón "Actualizar desde BCV"
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

// La sesión ya fue iniciada en config.php

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'></head><body>";
echo "<h1>Actualización Manual de Tasa BCV</h1>";

try {
    echo "<h2>Paso 1: Consultar BCV</h2>";

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
        echo "<p style='color: red;'>Error: HTTP $httpCode</p>";
        exit;
    }

    echo "<p style='color: green;'>✓ Página BCV obtenida</p>";

    echo "<h2>Paso 2: Extraer tasa</h2>";

    $patterns = [
        '/<strong>D[oó]lar.*?<\/strong>.*?<strong[^>]*>\s*([\d,\.]+)\s*<\/strong>/is',
        '/<div[^>]*class="[^"]*moneda[^"]*"[^>]*>.*?USD.*?<strong[^>]*>\s*([\d,\.]+)\s*<\/strong>/is',
        '/USD.*?<strong[^>]*>\s*([\d,\.]+)\s*<\/strong>/is',
        '/<div[^>]*id="dolar"[^>]*>.*?<strong[^>]*>\s*([\d,\.]+)\s*<\/strong>/is',
        '/<td[^>]*>.*?USD.*?<\/td>.*?<td[^>]*>\s*([\d,\.]+)\s*<\/td>/is'
    ];

    $tasa = null;
    foreach ($patterns as $i => $pattern) {
        if (preg_match($pattern, $html, $matches)) {
            $tasaStr = trim($matches[1]);
            echo "<p>Patrón " . ($i + 1) . " encontró: [$tasaStr]</p>";

            $tasaStr = str_replace('.', '', $tasaStr);
            $tasaStr = str_replace(',', '.', $tasaStr);
            $tasaFloat = floatval($tasaStr);

            if ($tasaFloat >= 1 && $tasaFloat <= 100000) {
                $tasa = $tasaFloat;
                echo "<p style='color: green; font-size: 18px;'><strong>✓ Tasa encontrada: $tasa Bs/USD</strong></p>";
                break;
            }
        }
    }

    if (!$tasa) {
        echo "<p style='color: red;'>No se pudo extraer la tasa</p>";
        exit;
    }

    echo "<h2>Paso 3: Guardar en base de datos</h2>";

    $sql = "INSERT INTO tasa_cambio_bcv (tasa_usd_bs, fecha_registro, registrado_por, fuente)
            VALUES (?, NOW(), 1, 'BCV Automático')";

    Database::execute($sql, [$tasa]);

    echo "<p style='color: green; font-size: 20px;'><strong>✓✓✓ ÉXITO!</strong></p>";
    echo "<p>Tasa $tasa Bs/USD guardada correctamente</p>";

    echo "<h2>Verificación:</h2>";
    $sqlVerif = "SELECT * FROM tasa_cambio_bcv ORDER BY fecha_registro DESC LIMIT 1";
    $ultima = Database::fetchOne($sqlVerif);

    echo "<div style='background: #d4edda; border: 2px solid #28a745; padding: 15px; margin: 20px 0;'>";
    echo "<strong>Última tasa en BD:</strong><br>";
    echo "ID: " . $ultima['id'] . "<br>";
    echo "Tasa: " . $ultima['tasa_usd_bs'] . " Bs/USD<br>";
    echo "Fecha: " . $ultima['fecha_registro'] . "<br>";
    echo "Fuente: " . $ultima['fuente'] . "<br>";
    echo "</div>";

    echo "<p><a href='/controldepagosestacionamiento/admin/configuracion' style='font-size: 18px; background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Ir a Configuración</a></p>";

} catch (Exception $e) {
    echo "<p style='color: red;'><strong>Error:</strong> " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "</body></html>";
