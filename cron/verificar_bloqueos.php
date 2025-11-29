<?php
/**
 * CRON: Verificar Bloqueos por Morosidad
 *
 * Se ejecuta diariamente
 * Bloquea controles de clientes con 4+ meses de morosidad
 *
 * Configurar en crontab:
 * 0 1 * * * /usr/bin/php /path/to/verificar_bloqueos.php
 *
 * O en Windows Task Scheduler:
 * Programa: php.exe
 * Argumentos: C:\xampp\htdocs\controldepagosestacionamiento\cron\verificar_bloqueos.php
 * Programación: Diario, 01:00
 */

// Solo permitir ejecución desde línea de comandos
if (php_sapi_name() !== 'cli') {
    die('Este script solo puede ejecutarse desde línea de comandos');
}

// Cargar configuración
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/models/Mensualidad.php';

echo "[" . date('Y-m-d H:i:s') . "] Iniciando verificación de bloqueos...\n";

try {
    // Verificar y bloquear controles
    $bloqueados = Mensualidad::verificarBloqueos();

    echo "[" . date('Y-m-d H:i:s') . "] ✓ Controles bloqueados: $bloqueados\n";

    // Marcar mensualidades vencidas
    $vencidas = Mensualidad::marcarVencidas();

    echo "[" . date('Y-m-d H:i:s') . "] ✓ Mensualidades marcadas como vencidas: $vencidas\n";

    writeLog("CRON: Verificación de bloqueos - Bloqueados: $bloqueados, Vencidas: $vencidas", 'info');

    exit(0); // Éxito

} catch (Exception $e) {
    echo "[" . date('Y-m-d H:i:s') . "] ✗ Error: " . $e->getMessage() . "\n";

    writeLog("CRON ERROR: Falló verificación de bloqueos - " . $e->getMessage(), 'error');

    exit(1); // Error
}
