<?php
/**
 * CRON: Backup Automático de Base de Datos
 *
 * Ejecuta backup diario de la base de datos MySQL
 * - Crea archivo .sql con dump completo
 * - Comprime el archivo con gzip
 * - Mantiene backups de últimos 30 días
 * - Registra en logs el resultado
 *
 * Configuración recomendada:
 * - Crontab: 0 2 * * * /usr/bin/php /ruta/a/cron/backup_database.php
 * - Windows Task Scheduler: Diario a las 2:00 AM
 *
 * @author Sistema de Estacionamiento
 * @version 1.0
 */

// Cargar configuración
require_once __DIR__ . '/../config/config.php';

// ============================================================================
// CONFIGURACIÓN DEL BACKUP
// ============================================================================

// Directorio de backups
define('BACKUP_PATH', ROOT_PATH . '/backups');
define('BACKUP_RETENTION_DAYS', 30); // Días de retención
define('BACKUP_COMPRESS', true); // Comprimir con gzip

// ============================================================================
// FUNCIONES
// ============================================================================

/**
 * Crear directorio de backups si no existe
 */
function ensureBackupDirectory(): bool
{
    if (!file_exists(BACKUP_PATH)) {
        if (!mkdir(BACKUP_PATH, 0755, true)) {
            writeLog("ERROR: No se pudo crear directorio de backups: " . BACKUP_PATH, 'error');
            return false;
        }
        writeLog("Directorio de backups creado: " . BACKUP_PATH, 'info');
    }
    return true;
}

/**
 * Ejecutar backup de la base de datos
 */
function executeBackup(): bool
{
    $timestamp = date('Y-m-d_His');
    $backupFile = BACKUP_PATH . "/backup_db_{$timestamp}.sql";

    writeLog("Iniciando backup de base de datos...", 'info');

    // Construir comando mysqldump (Windows compatible)
    $mysqlDumpPath = 'C:\\xampp\\mysql\\bin\\mysqldump.exe'; // Ruta XAMPP Windows
    if (!file_exists($mysqlDumpPath)) {
        $mysqlDumpPath = 'mysqldump'; // Fallback para Linux/Mac
    }

    $command = sprintf(
        '"%s" --user=%s --password=%s --host=%s --port=%d %s > "%s" 2>&1',
        $mysqlDumpPath,
        $_ENV['DB_USER'] ?? 'root',
        $_ENV['DB_PASS'] ?? '',
        $_ENV['DB_HOST'] ?? 'localhost',
        (int)($_ENV['DB_PORT'] ?? 3306),
        $_ENV['DB_NAME'] ?? 'estacionamiento_db',
        $backupFile
    );

    // Ocultar password en logs
    $commandLog = str_replace(
        $_ENV['DB_PASS'] ?? '',
        '***REDACTED***',
        $command
    );
    writeLog("Comando: $commandLog", 'debug');

    // Ejecutar mysqldump
    exec($command, $output, $returnVar);

    // Verificar resultado
    if ($returnVar !== 0) {
        writeLog("ERROR: mysqldump falló con código: $returnVar", 'error');
        if (!empty($output)) {
            writeLog("Output: " . implode("\n", $output), 'error');
        }

        // Verificar si el archivo se creó parcialmente y eliminarlo
        if (file_exists($backupFile)) {
            unlink($backupFile);
        }
        return false;
    }

    // Verificar que el archivo se creó y tiene contenido
    if (!file_exists($backupFile) || filesize($backupFile) === 0) {
        writeLog("ERROR: Archivo de backup vacío o no creado", 'error');
        if (file_exists($backupFile)) {
            unlink($backupFile);
        }
        return false;
    }

    $backupSize = filesize($backupFile);
    writeLog("Backup creado exitosamente: $backupFile (" . formatBytes($backupSize) . ")", 'info');

    // Comprimir backup
    if (BACKUP_COMPRESS && compressBackup($backupFile)) {
        $compressedSize = filesize($backupFile . '.gz');
        $compressionRatio = round((1 - $compressedSize / $backupSize) * 100, 2);
        writeLog("Backup comprimido: {$backupFile}.gz (" . formatBytes($compressedSize) . ", {$compressionRatio}% reducción)", 'info');
    }

    return true;
}

/**
 * Comprimir archivo de backup
 */
function compressBackup(string $backupFile): bool
{
    // Intentar con gzip nativo (Linux/Mac)
    if (function_exists('exec')) {
        exec("gzip -9 \"$backupFile\" 2>&1", $output, $returnVar);
        if ($returnVar === 0 && file_exists($backupFile . '.gz')) {
            return true;
        }
    }

    // Fallback: PHP gzencode (más lento pero multiplataforma)
    if (function_exists('gzencode')) {
        $content = file_get_contents($backupFile);
        if ($content !== false) {
            $compressed = gzencode($content, 9);
            if ($compressed !== false) {
                if (file_put_contents($backupFile . '.gz', $compressed) !== false) {
                    unlink($backupFile); // Eliminar archivo sin comprimir
                    return true;
                }
            }
        }
    }

    writeLog("ADVERTENCIA: No se pudo comprimir el backup", 'warning');
    return false;
}

/**
 * Eliminar backups antiguos
 */
function cleanOldBackups(): void
{
    $threshold = time() - (BACKUP_RETENTION_DAYS * 24 * 60 * 60);
    $deletedCount = 0;
    $deletedSize = 0;

    $pattern = BACKUP_PATH . '/backup_db_*.{sql,sql.gz}';
    $files = glob($pattern, GLOB_BRACE);

    if ($files === false) {
        writeLog("ADVERTENCIA: No se pudo listar archivos de backup", 'warning');
        return;
    }

    foreach ($files as $file) {
        if (filemtime($file) < $threshold) {
            $size = filesize($file);
            if (unlink($file)) {
                $deletedCount++;
                $deletedSize += $size;
                writeLog("Backup antiguo eliminado: " . basename($file), 'info');
            } else {
                writeLog("ERROR: No se pudo eliminar backup antiguo: " . basename($file), 'error');
            }
        }
    }

    if ($deletedCount > 0) {
        writeLog("Limpieza completada: $deletedCount archivo(s) eliminado(s) (" . formatBytes($deletedSize) . " liberados)", 'info');
    } else {
        writeLog("Limpieza completada: No hay backups antiguos para eliminar", 'info');
    }
}

/**
 * Obtener estadísticas de backups
 */
function getBackupStats(): array
{
    $pattern = BACKUP_PATH . '/backup_db_*.{sql,sql.gz}';
    $files = glob($pattern, GLOB_BRACE);

    if ($files === false) {
        return ['count' => 0, 'total_size' => 0, 'oldest' => null, 'newest' => null];
    }

    $totalSize = 0;
    $times = [];

    foreach ($files as $file) {
        $totalSize += filesize($file);
        $times[] = filemtime($file);
    }

    return [
        'count' => count($files),
        'total_size' => $totalSize,
        'oldest' => !empty($times) ? date('Y-m-d H:i:s', min($times)) : null,
        'newest' => !empty($times) ? date('Y-m-d H:i:s', max($times)) : null
    ];
}

/**
 * Formatear bytes a formato legible
 */
function formatBytes(int $bytes, int $precision = 2): string
{
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];

    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }

    return round($bytes, $precision) . ' ' . $units[$i];
}

/**
 * Registrar actividad en la tabla de logs
 */
function logToDatabase(bool $success, string $details): void
{
    try {
        require_once CONFIG_PATH . '/database.php';

        $sql = "INSERT INTO logs_actividad
                (modulo, accion, datos_nuevos, fecha_hora, ip_address)
                VALUES (?, ?, ?, NOW(), ?)";

        Database::execute($sql, [
            'cron_backup',
            $success ? 'backup_exitoso' : 'backup_fallido',
            $details,
            $_SERVER['SERVER_ADDR'] ?? '127.0.0.1'
        ]);
    } catch (Exception $e) {
        writeLog("ADVERTENCIA: No se pudo registrar en tabla logs_actividad: " . $e->getMessage(), 'warning');
    }
}

// ============================================================================
// EJECUCIÓN PRINCIPAL
// ============================================================================

$startTime = microtime(true);
$success = false;
$errorMessage = '';

try {
    writeLog("========================================", 'info');
    writeLog("INICIANDO CRON: Backup de Base de Datos", 'info');
    writeLog("Fecha: " . date('Y-m-d H:i:s'), 'info');
    writeLog("========================================", 'info');

    // Verificar que estamos en CLI (opcional, comentar si se ejecuta desde web)
    // if (PHP_SAPI !== 'cli') {
    //     throw new Exception("Este script debe ejecutarse desde CLI");
    // }

    // Crear directorio de backups
    if (!ensureBackupDirectory()) {
        throw new Exception("No se pudo preparar directorio de backups");
    }

    // Ejecutar backup
    if (!executeBackup()) {
        throw new Exception("Falló la ejecución del backup");
    }

    // Limpiar backups antiguos
    cleanOldBackups();

    // Obtener estadísticas
    $stats = getBackupStats();
    writeLog("Estadísticas de backups:", 'info');
    writeLog("  - Total de backups: {$stats['count']}", 'info');
    writeLog("  - Espacio utilizado: " . formatBytes($stats['total_size']), 'info');
    writeLog("  - Backup más antiguo: {$stats['oldest']}", 'info');
    writeLog("  - Backup más reciente: {$stats['newest']}", 'info');

    $success = true;
    $details = "Backup completado exitosamente. Total de backups: {$stats['count']}";

} catch (Exception $e) {
    $errorMessage = $e->getMessage();
    writeLog("ERROR CRÍTICO: $errorMessage", 'error');
    $details = "Backup fallido: $errorMessage";
}

// Registrar en base de datos
logToDatabase($success, $details);

// Log final
$executionTime = round(microtime(true) - $startTime, 2);
writeLog("========================================", 'info');
writeLog("CRON FINALIZADO " . ($success ? "EXITOSAMENTE" : "CON ERRORES"), $success ? 'info' : 'error');
writeLog("Tiempo de ejecución: {$executionTime}s", 'info');
writeLog("========================================", 'info');

// Salir con código apropiado
exit($success ? 0 : 1);
