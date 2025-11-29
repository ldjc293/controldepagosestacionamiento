<?php
/**
 * CRON: Actualizar Tasa de Cambio BCV
 *
 * Se ejecuta diariamente para obtener la tasa de cambio actual del BCV
 * Actualiza la tasa en la base de datos para cálculos de conversión
 *
 * Configurar en crontab:
 * 0 10 * * * /usr/bin/php /path/to/actualizar_tasa_bcv.php
 *
 * O en Windows Task Scheduler:
 * Programa: php.exe
 * Argumentos: C:\xampp\htdocs\controldepagosestacionamiento\cron\actualizar_tasa_bcv.php
 * Programación: Diario, 10:00
 */

// Solo permitir ejecución desde línea de comandos
if (php_sapi_name() !== 'cli') {
    die('Este script solo puede ejecutarse desde línea de comandos');
}

// Cargar configuración
require_once __DIR__ . '/../config/config.php';

echo "[" . date('Y-m-d H:i:s') . "] Iniciando actualización de tasa BCV...\n";

try {
    $db = Database::getInstance();

    // OPCIÓN 1: Obtener desde API del BCV (si está disponible)
    $tasaNueva = obtenerTasaDesdeBCV();

    // OPCIÓN 2: Si falla el API, usar fuente alternativa
    if ($tasaNueva === null) {
        echo "[" . date('Y-m-d H:i:s') . "] API del BCV no disponible, intentando fuente alternativa...\n";
        $tasaNueva = obtenerTasaAlternativa();
    }

    // OPCIÓN 3: Si todo falla, mantener la tasa actual
    if ($tasaNueva === null) {
        echo "[" . date('Y-m-d H:i:s') . "] ✗ No se pudo obtener tasa actualizada\n";
        writeLog("CRON: No se pudo actualizar tasa BCV - APIs no disponibles", 'warning');
        exit(1);
    }

    // Validar que la tasa sea razonable (entre 10 y 100 Bs por USD)
    if ($tasaNueva < 10 || $tasaNueva > 100) {
        echo "[" . date('Y-m-d H:i:s') . "] ✗ Tasa inválida recibida: $tasaNueva\n";
        writeLog("CRON ERROR: Tasa BCV inválida recibida: $tasaNueva", 'error');
        exit(1);
    }

    // Obtener tasa actual
    $tasaActual = Database::fetchOne(
        "SELECT tasa_usd_bs FROM tasa_cambio_bcv ORDER BY fecha_registro DESC LIMIT 1"
    );

    // Insertar nueva tasa en la tabla tasa_cambio_bcv
    $db->prepare("INSERT INTO tasa_cambio_bcv (tasa_usd_bs, fuente) VALUES (?, ?)")->execute([$tasaNueva, 'CRON']);

    $variacion = $tasaActual ? (($tasaNueva - $tasaActual) / $tasaActual) * 100 : 0;
    $signo = $variacion >= 0 ? '+' : '';

    echo "[" . date('Y-m-d H:i:s') . "] ✓ Tasa actualizada: $tasaNueva Bs/USD (Variación: {$signo}" . number_format($variacion, 2) . "%)\n";

    writeLog("CRON: Tasa BCV actualizada a $tasaNueva Bs/USD (Variación: {$signo}" . number_format($variacion, 2) . "%)", 'info');

    exit(0); // Éxito

} catch (Exception $e) {
    echo "[" . date('Y-m-d H:i:s') . "] ✗ Error: " . $e->getMessage() . "\n";

    writeLog("CRON ERROR: Falló actualización de tasa BCV - " . $e->getMessage(), 'error');

    exit(1); // Error
}

/**
 * Intenta obtener la tasa desde el BCV
 */
function obtenerTasaDesdeBCV(): ?float
{
    try {
        // URL del API del BCV (esta es una URL de ejemplo, debe verificarse la URL real)
        $url = 'https://www.bcv.org.ve/';

        // Configurar contexto para la petición
        $context = stream_context_create([
            'http' => [
                'timeout' => 10,
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
            ]
        ]);

        // Intentar obtener el HTML
        $html = @file_get_contents($url, false, $context);

        if ($html === false) {
            return null;
        }

        // Buscar la tasa en el HTML usando regex
        // El BCV suele publicar la tasa en un formato específico
        // Este patrón debe ajustarse según el formato actual del sitio
        if (preg_match('/USD.*?([0-9]+[,.]?[0-9]*)/i', $html, $matches)) {
            $tasa = str_replace(',', '.', $matches[1]);
            return (float) $tasa;
        }

        return null;

    } catch (Exception $e) {
        return null;
    }
}

/**
 * Obtiene la tasa desde una fuente alternativa (DolarToday, Monitor Dólar, etc.)
 */
function obtenerTasaAlternativa(): ?float
{
    try {
        // Fuente alternativa 1: DolarToday API (si está disponible)
        $url = 'https://s3.amazonaws.com/dolartoday/data.json';

        $context = stream_context_create([
            'http' => [
                'timeout' => 10,
                'user_agent' => 'Mozilla/5.0'
            ]
        ]);

        $json = @file_get_contents($url, false, $context);

        if ($json === false) {
            return null;
        }

        $data = json_decode($json, true);

        // DolarToday suele devolver USD.transferencia
        if (isset($data['USD']['transferencia'])) {
            return (float) $data['USD']['transferencia'];
        }

        return null;

    } catch (Exception $e) {
        return null;
    }
}
