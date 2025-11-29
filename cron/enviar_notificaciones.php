<?php
/**
 * CRON: Enviar Notificaciones de Morosidad
 *
 * Se ejecuta diariamente
 * Envía emails a clientes con mensualidades vencidas
 *
 * Configurar en crontab:
 * 0 9 * * * /usr/bin/php /path/to/enviar_notificaciones.php
 *
 * O en Windows Task Scheduler:
 * Programa: php.exe
 * Argumentos: C:\xampp\htdocs\controldepagosestacionamiento\cron\enviar_notificaciones.php
 * Programación: Diario, 09:00
 */

// Solo permitir ejecución desde línea de comandos
if (php_sapi_name() !== 'cli') {
    die('Este script solo puede ejecutarse desde línea de comandos');
}

// Cargar configuración
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/models/Mensualidad.php';
require_once __DIR__ . '/../app/models/Usuario.php';
require_once __DIR__ . '/../helpers/MailHelper.php';

echo "[" . date('Y-m-d H:i:s') . "] Iniciando envío de notificaciones...\n";

try {
    // Obtener clientes con mensualidades vencidas
    $morosos = Mensualidad::getVencidas(1); // 1+ meses vencidos

    $enviados = 0;
    $errores = 0;

    foreach ($morosos as $moroso) {
        $usuario = Usuario::findById($moroso['usuario_id']);

        if (!$usuario || !$usuario->activo) {
            continue;
        }

        // Calcular deuda total
        $deudaInfo = Mensualidad::calcularDeudaTotal($usuario->id);

        // Determinar tipo de notificación según meses vencidos
        if ($deudaInfo['total_vencidas'] >= MESES_BLOQUEO) {
            // Notificación de bloqueo
            $enviado = MailHelper::sendBlockNotification(
                $usuario->email,
                $usuario->nombre_completo,
                $deudaInfo['total_vencidas'],
                $deudaInfo['deuda_total_usd']
            );
        } else {
            // Alerta de morosidad
            $enviado = MailHelper::sendMorosityAlert(
                $usuario->email,
                $usuario->nombre_completo,
                $deudaInfo['total_vencidas'],
                $deudaInfo['deuda_total_usd']
            );
        }

        if ($enviado) {
            $enviados++;
            echo "[" . date('Y-m-d H:i:s') . "] ✓ Email enviado a: {$usuario->email}\n";
        } else {
            $errores++;
            echo "[" . date('Y-m-d H:i:s') . "] ✗ Error enviando a: {$usuario->email}\n";
        }

        // Esperar 1 segundo entre emails para no saturar
        sleep(1);
    }

    echo "[" . date('Y-m-d H:i:s') . "] Finalizado - Enviados: $enviados, Errores: $errores\n";

    writeLog("CRON: Notificaciones enviadas - Exitosos: $enviados, Errores: $errores", 'info');

    exit(0); // Éxito

} catch (Exception $e) {
    echo "[" . date('Y-m-d H:i:s') . "] ✗ Error: " . $e->getMessage() . "\n";

    writeLog("CRON ERROR: Falló envío de notificaciones - " . $e->getMessage(), 'error');

    exit(1); // Error
}
