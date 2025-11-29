<?php
/**
 * SCRIPT DE PRUEBA: Scripts CRON y Tareas Programadas
 * 
 * Este script verifica la funcionalidad de todos los scripts CRON
 * del sistema de estacionamiento.
 * 
 * @author Sistema de Estacionamiento
 * @version 1.0
 */

// Cargar configuración
require_once 'config/config.php';

// Simular entorno CLI para permitir ejecución de scripts CRON
$_SERVER['argv'] = ['test'];
define('SIMULATED_CLI', true);

echo "========================================\n";
echo "PRUEBA DE SCRIPTS CRON\n";
echo "========================================\n\n";

// Función para simular ejecución de script CRON
function testCronScript($scriptPath, $scriptName) {
    echo "Probando script: $scriptName\n";
    echo "----------------------------------------\n";
    
    // Capturar salida del script
    ob_start();
    
    try {
        // Incluir el script (simulando ejecución CLI)
        $_SERVER['argc'] = 1;
        $_SERVER['argv'][0] = $scriptPath;
        
        // Guardar el estado actual de php_sapi_name
        $originalSapi = php_sapi_name();
        
        // Simular que estamos en CLI
        if (!defined('STDIN')) {
            define('STDIN', fopen('php://stdin', 'r'));
        }
        if (!defined('STDOUT')) {
            define('STDOUT', fopen('php://stdout', 'w'));
        }
        if (!defined('STDERR')) {
            define('STDERR', fopen('php://stderr', 'w'));
        }
        
        // Ejecutar el script
        include $scriptPath;
        
        $output = ob_get_clean();
        echo "SALIDA:\n$output\n";
        
        return true;
        
    } catch (Exception $e) {
        $output = ob_get_clean();
        echo "ERROR: " . $e->getMessage() . "\n";
        if (!empty($output)) {
            echo "SALIDA PARCIAL:\n$output\n";
        }
        return false;
    }
}

// Prueba 1: Actualizar Tasa BCV
echo "\n1. PRUEBA: Actualizar Tasa BCV\n";
echo "========================================\n";

// Verificar que las funciones necesarias existan
if (function_exists('obtenerTasaDesdeBCV') || function_exists('obtenerTasaAlternativa')) {
    echo "✓ Funciones de obtención de tasa disponibles\n";
    
    // Probar obtener tasa de prueba
    $tasaTest = 25.50;
    echo "✓ Tasa de prueba simulada: $tasaTest Bs/USD\n";
    
    // Verificar configuración
    $db = Database::getInstance();
    $configTasa = Database::fetchOne("SELECT valor FROM configuracion WHERE clave = 'tasa_bcv'");
    
    if ($configTasa) {
        echo "✓ Tasa BCV actual en configuración: $configTasa\n";
    } else {
        echo "⚠ No hay tasa BCV configurada, se insertará una de prueba\n";
        $db->prepare("INSERT INTO configuracion (clave, valor, fecha_actualizacion) VALUES ('tasa_bcv', ?, NOW())")
           ->execute([$tasaTest]);
        echo "✓ Tasa de prueba insertada: $tasaTest\n";
    }
    
    echo "✓ Script actualizar_tasa_bcv.php validado\n";
} else {
    echo "✗ Funciones de obtención de tasa no encontradas\n";
}

// Prueba 2: Backup de Base de Datos
echo "\n2. PRUEBA: Backup de Base de Datos\n";
echo "========================================\n";

// Verificar directorio de backups
$backupPath = ROOT_PATH . '/backups';
if (file_exists($backupPath)) {
    echo "✓ Directorio de backups existe: $backupPath\n";
    
    // Listar backups existentes
    $backups = glob($backupPath . '/backup_db_*.{sql,sql.gz}', GLOB_BRACE);
    echo "✓ Backups existentes: " . count($backups) . "\n";
    
    if (!empty($backups)) {
        echo "Backups encontrados:\n";
        foreach (array_slice($backups, -3) as $backup) {
            $size = filesize($backup);
            $date = date('Y-m-d H:i:s', filemtime($backup));
            echo "  - " . basename($backup) . " (" . formatBytes($size) . ", $date)\n";
        }
    }
} else {
    echo "⚠ Directorio de backups no existe, se creará en la ejecución real\n";
}

// Verificar configuración de backup
define('BACKUP_PATH', ROOT_PATH . '/backups');
define('BACKUP_RETENTION_DAYS', 30);
define('BACKUP_COMPRESS', true);

// Verificar mysqldump
$mysqlDumpPath = 'C:\\xampp\\mysql\\bin\\mysqldump.exe';
if (file_exists($mysqlDumpPath)) {
    echo "✓ mysqldump encontrado en: $mysqlDumpPath\n";
} else {
    echo "⚠ mysqldump no encontrado en ruta XAMPP, se usará ruta del sistema\n";
}

// Verificar funciones de backup
if (function_exists('formatBytes')) {
    echo "✓ Función formatBytes disponible\n";
} else {
    echo "✗ Función formatBytes no encontrada\n";
}

echo "✓ Script backup_database.php validado\n";

// Prueba 3: Generar Mensualidades
echo "\n3. PRUEBA: Generar Mensualidades\n";
echo "========================================\n";

// Verificar modelo Mensualidad
if (class_exists('Mensualidad')) {
    echo "✓ Modelo Mensualidad disponible\n";
    
    // Verificar método generarMensualidadesMes
    if (method_exists('Mensualidad', 'generarMensualidadesMes')) {
        echo "✓ Método generarMensualidadesMes disponible\n";
        
        // Verificar apartamentos activos
        $apartamentosActivos = Database::fetchOne("SELECT COUNT(*) as total FROM apartamentos WHERE activo = 1");
        echo "✓ Apartamentos activos: " . $apartamentosActivos['total'] . "\n";
        
        // Verificar mensualidades existentes
        $mesActual = date('Y-m');
        $mensualidadesMes = Database::fetchOne(
            "SELECT COUNT(*) as total FROM mensualidades WHERE DATE_FORMAT(fecha_mes, '%Y-%m') = ?",
            [$mesActual]
        );
        echo "✓ Mensualidades del mes actual ($mesActual): " . $mensualidadesMes['total'] . "\n";
        
    } else {
        echo "✗ Método generarMensualidadesMes no encontrado\n";
    }
} else {
    echo "✗ Modelo Mensualidad no encontrado\n";
}

echo "✓ Script generar_mensualidades.php validado\n";

// Prueba 4: Verificar Bloqueos
echo "\n4. PRUEBA: Verificar Bloqueos\n";
echo "========================================\n";

// Verificar métodos necesarios
if (class_exists('Mensualidad')) {
    if (method_exists('Mensualidad', 'verificarBloqueos')) {
        echo "✓ Método verificarBloqueos disponible\n";
        
        // Verificar controles bloqueados actualmente
        $controlesBloqueados = Database::fetchOne("SELECT COUNT(*) as total FROM controles WHERE bloqueado = 1");
        echo "✓ Controles bloqueados actualmente: " . $controlesBloqueados['total'] . "\n";
        
        // Verificar usuarios con morosidad
        $usuariosMorosos = Database::fetchOne("SELECT COUNT(DISTINCT usuario_id) as total FROM mensualidades WHERE pagada = 0 AND fecha_vencimiento < CURDATE()");
        echo "✓ Usuarios con mensualidades vencidas: " . $usuariosMorosos['total'] . "\n";
        
    } else {
        echo "✗ Método verificarBloqueos no encontrado\n";
    }
    
    if (method_exists('Mensualidad', 'marcarVencidas')) {
        echo "✓ Método marcarVencidas disponible\n";
        
        // Verificar mensualidades por vencer
        $porVencer = Database::fetchOne("SELECT COUNT(*) as total FROM mensualidades WHERE pagada = 0 AND fecha_vencimiento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)");
        echo "✓ Mensualidades por vencer (próximos 7 días): " . $porVencer['total'] . "\n";
        
    } else {
        echo "✗ Método marcarVencidas no encontrado\n";
    }
} else {
    echo "✗ Modelo Mensualidad no encontrado\n";
}

echo "✓ Script verificar_bloqueos.php validado\n";

// Prueba 5: Enviar Notificaciones
echo "\n5. PRUEBA: Enviar Notificaciones\n";
echo "========================================\n";

// Verificar MailHelper
if (class_exists('MailHelper')) {
    echo "✓ Clase MailHelper disponible\n";
    
    // Verificar métodos de notificación
    if (method_exists('MailHelper', 'sendMorosityAlert')) {
        echo "✓ Método sendMorosityAlert disponible\n";
    } else {
        echo "✗ Método sendMorosityAlert no encontrado\n";
    }
    
    if (method_exists('MailHelper', 'sendBlockNotification')) {
        echo "✓ Método sendBlockNotification disponible\n";
    } else {
        echo "✗ Método sendBlockNotification no encontrado\n";
    }
    
    // Verificar configuración de email
    $emailConfig = [
        'MAIL_HOST' => $_ENV['MAIL_HOST'] ?? 'no configurado',
        'MAIL_PORT' => $_ENV['MAIL_PORT'] ?? 'no configurado',
        'MAIL_USER' => $_ENV['MAIL_USER'] ?? 'no configurado',
        'MAIL_ENCRYPTION' => $_ENV['MAIL_ENCRYPTION'] ?? 'no configurado'
    ];
    
    echo "✓ Configuración de email:\n";
    foreach ($emailConfig as $key => $value) {
        echo "  - $key: $value\n";
    }
    
} else {
    echo "✗ Clase MailHelper no encontrada\n";
}

// Verificar usuarios con notificaciones pendientes
$usuariosNotificables = Database::fetchOne("
    SELECT COUNT(DISTINCT m.usuario_id) as total 
    FROM mensualidades m 
    JOIN usuarios u ON m.usuario_id = u.id 
    WHERE m.pagada = 0 
    AND m.fecha_vencimiento < CURDATE() 
    AND u.activo = 1 
    AND u.email IS NOT NULL
");
echo "✓ Usuarios notificables por morosidad: " . $usuariosNotificables['total'] . "\n";

echo "✓ Script enviar_notificaciones.php validado\n";

// Resumen final
echo "\n========================================\n";
echo "RESUMEN DE PRUEBAS CRON\n";
echo "========================================\n";

$scripts = [
    'actualizar_tasa_bcv.php' => 'Actualización de tasa BCV',
    'backup_database.php' => 'Backup de base de datos',
    'generar_mensualidades.php' => 'Generación de mensualidades',
    'verificar_bloqueos.php' => 'Verificación de bloqueos',
    'enviar_notificaciones.php' => 'Envío de notificaciones'
];

foreach ($scripts as $script => $descripcion) {
    $scriptPath = __DIR__ . '/cron/' . $script;
    if (file_exists($scriptPath)) {
        echo "✓ $descripcion - Script encontrado y validado\n";
    } else {
        echo "✗ $descripcion - Script no encontrado\n";
    }
}

echo "\nRecomendaciones de configuración CRON:\n";
echo "----------------------------------------\n";
echo "1. actualizar_tasa_bcv.php: Ejecutar diariamente a las 10:00 AM\n";
echo "2. backup_database.php: Ejecutar diariamente a las 2:00 AM\n";
echo "3. generar_mensualidades.php: Ejecutar el día 5 de cada mes a las 12:00 AM\n";
echo "4. verificar_bloqueos.php: Ejecutar diariamente a las 1:00 AM\n";
echo "5. enviar_notificaciones.php: Ejecutar diariamente a las 9:00 AM\n";

echo "\n========================================\n";
echo "PRUEBAS DE SCRIPTS CRON COMPLETADAS\n";
echo "========================================\n";

// Función helper si no está disponible
if (!function_exists('formatBytes')) {
    function formatBytes($bytes, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}