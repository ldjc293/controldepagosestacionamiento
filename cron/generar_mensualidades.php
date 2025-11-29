<?php
/**
 * CRON: Generar Mensualidades
 *
 * Se ejecuta el día 5 de cada mes
 * Genera las mensualidades para todos los apartamentos activos
 *
 * Configurar en crontab:
 * 0 0 5 * * /usr/bin/php /path/to/generar_mensualidades.php
 *
 * O en Windows Task Scheduler:
 * Programa: php.exe
 * Argumentos: C:\xampp\htdocs\controldepagosestacionamiento\cron\generar_mensualidades.php
 * Programación: Mensual, día 5, 00:00
 */

// Solo permitir ejecución desde línea de comandos
if (php_sapi_name() !== 'cli') {
    die('Este script solo puede ejecutarse desde línea de comandos');
}

// Cargar configuración
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/models/Mensualidad.php';

echo "[" . date('Y-m-d H:i:s') . "] Iniciando generación de mensualidades...\n";

try {
    // Generar mensualidades del mes
    $generadas = Mensualidad::generarMensualidadesMes();

    echo "[" . date('Y-m-d H:i:s') . "] ✓ Mensualidades generadas exitosamente: $generadas\n";

    writeLog("CRON: Mensualidades generadas automáticamente: $generadas", 'info');

    exit(0); // Éxito

} catch (Exception $e) {
    echo "[" . date('Y-m-d H:i:s') . "] ✗ Error: " . $e->getMessage() . "\n";

    writeLog("CRON ERROR: Falló generación de mensualidades - " . $e->getMessage(), 'error');

    exit(1); // Error
}
