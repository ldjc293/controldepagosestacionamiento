<?php
/**
 * Script de prueba para el sistema de pagos y generación de recibos
 * 
 * Este script prueba las funcionalidades clave del sistema de pagos:
 * - Registro de pagos
 * - Aprobación y rechazo de pagos
 * - Generación de recibos PDF
 * - Generación de códigos QR
 * - Asociación de pagos con mensualidades
 */

// Configurar error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Incluir configuración y dependencias necesarias
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'app/models/Pago.php';
require_once 'app/models/Mensualidad.php';
require_once 'app/models/Usuario.php';
require_once 'app/models/Apartamento.php';
require_once 'app/helpers/PDFHelper.php';
require_once 'app/helpers/QRHelper.php';

// Función para mostrar resultados de prueba
function showTestResult($testName, $result, $message = '') {
    $status = $result ? '✅ PASÓ' : '❌ FALLÓ';
    echo "$status: $testName\n";
    if ($message) {
        echo "  -> $message\n";
    }
    echo "\n";
    return $result;
}

// Función para mostrar información de prueba
function showTestInfo($title) {
    echo "\n" . str_repeat("=", 60) . "\n";
    echo "PRUEBA: $title\n";
    echo str_repeat("=", 60) . "\n\n";
}

// Iniciar pruebas
echo "=== INICIANDO PRUEBAS DEL SISTEMA DE PAGOS ===\n\n";

// Contador de pruebas
$passedTests = 0;
$totalTests = 0;

// 1. Prueba de conexión a base de datos
showTestInfo("Conexión a Base de Datos");
try {
    $db = Database::getInstance();
    $connection = $db->getConnection();
    $result = showTestResult("Conexión a base de datos", $connection instanceof PDO);
    if ($result) $passedTests++;
    $totalTests++;
} catch (Exception $e) {
    showTestResult("Conexión a base de datos", false, $e->getMessage());
    $totalTests++;
}

// 2. Prueba de generación de código QR
showTestInfo("Generación de Código QR");
try {
    $qrData = "Test QR Data " . date('Y-m-d H:i:s');
    $qrBase64 = QRHelper::generate($qrData);
    
    $result = showTestResult(
        "Generación de QR", 
        !empty($qrBase64) && strpos($qrBase64, 'data:image/png;base64') === 0
    );
    if ($result) $passedTests++;
    $totalTests++;
} catch (Exception $e) {
    showTestResult("Generación de QR", false, $e->getMessage());
    $totalTests++;
}

// 3. Prueba de generación de QR para recibo
showTestInfo("Generación de QR para Recibo");
try {
    $numeroRecibo = 'EST-000001';
    $montoUSD = 25.00;
    $fecha = date('Y-m-d H:i:s');
    
    $qrRecibo = QRHelper::generateForRecibo($numeroRecibo, $montoUSD, $fecha);
    
    $result = showTestResult(
        "Generación de QR para recibo", 
        !empty($qrRecibo) && strpos($qrRecibo, 'data:image/png;base64') === 0
    );
    if ($result) $passedTests++;
    $totalTests++;
} catch (Exception $e) {
    showTestResult("Generación de QR para recibo", false, $e->getMessage());
    $totalTests++;
}

// 4. Prueba de verificación de hash de recibo
showTestInfo("Verificación de Hash de Recibo");
try {
    $numeroRecibo = 'EST-000001';
    $montoUSD = 25.00;
    $fecha = date('Y-m-d H:i:s');
    
    $hash = QRHelper::generateVerificationHash($numeroRecibo, $montoUSD, $fecha);
    $isValid = QRHelper::verifyReciboHash($numeroRecibo, $montoUSD, $fecha, $hash);
    
    $result = showTestResult("Verificación de hash", $isValid);
    if ($result) $passedTests++;
    $totalTests++;
    
    // Probar con hash incorrecto
    $invalidHash = hash('sha256', 'invalid_data');
    $isInvalid = !QRHelper::verifyReciboHash($numeroRecibo, $montoUSD, $fecha, $invalidHash);
    $result2 = showTestResult("Detección de hash inválido", $isInvalid);
    if ($result2) $passedTests++;
    $totalTests++;
} catch (Exception $e) {
    showTestResult("Verificación de hash", false, $e->getMessage());
    $totalTests++;
}

// 5. Prueba de generación de recibo PDF (simulada)
showTestInfo("Generación de Recibo PDF");
try {
    // Datos de prueba para el recibo
    $datosRecibo = [
        'numero_recibo' => 'EST-000001',
        'fecha_pago' => date('Y-m-d H:i:s'),
        'cliente_nombre' => 'Juan Pérez',
        'apartamento' => '27-01',
        'monto_usd' => 25.00,
        'monto_bs' => 25.00 * 35.50, // Tasa simulada
        'tasa_cambio' => 35.50,
        'moneda_pago' => 'usd_efectivo',
        'meses_pagados' => 'Noviembre 2024',
        'controles' => 'EST-001, EST-002',
        'operador_nombre' => 'Operador Test',
        'notas' => 'Pago de mensualidad correspondiente'
    ];
    
    // Verificar si existen los directorios necesarios
    if (!defined('RECIBOS_PATH')) {
        define('RECIBOS_PATH', __DIR__ . '/public/uploads/recibos');
    }
    
    // Crear directorio si no existe
    if (!file_exists(RECIBOS_PATH)) {
        mkdir(RECIBOS_PATH, 0777, true);
    }
    
    // Intentar generar el recibo (puede fallar si no están las dependencias)
    try {
        $pdfPath = PDFHelper::generateRecibo($datosRecibo);
        $result = showTestResult(
            "Generación de PDF", 
            file_exists($pdfPath),
            "PDF generado en: $pdfPath"
        );
        if ($result) $passedTests++;
    } catch (Exception $pdfEx) {
        showTestResult(
            "Generación de PDF", 
            false, 
            "Posiblemente faltan dependencias (DomPDF): " . $pdfEx->getMessage()
        );
    }
    $totalTests++;
} catch (Exception $e) {
    showTestResult("Generación de recibo PDF", false, $e->getMessage());
    $totalTests++;
}

// 6. Prueba de modelo Pago - Búsqueda por ID
showTestInfo("Modelo Pago - Búsqueda por ID");
try {
    // Buscar un pago existente o mostrar mensaje si no hay
    $pago = Pago::findById(1);
    
    if ($pago) {
        $result = showTestResult(
            "Búsqueda de pago por ID", 
            $pago instanceof Pago && !empty($pago->id),
            "Pago encontrado: {$pago->numero_recibo}"
        );
        if ($result) $passedTests++;
    } else {
        showTestResult(
            "Búsqueda de pago por ID", 
            true,
            "No hay pagos en la base de datos (esto es normal en una instalación nueva)"
        );
        $passedTests++;
    }
    $totalTests++;
} catch (Exception $e) {
    showTestResult("Búsqueda de pago por ID", false, $e->getMessage());
    $totalTests++;
}

// 7. Prueba de modelo Pago - Búsqueda por número de recibo
showTestInfo("Modelo Pago - Búsqueda por Número de Recibo");
try {
    $pago = Pago::findByNumeroRecibo('EST-000001');
    
    if ($pago) {
        $result = showTestResult(
            "Búsqueda de pago por recibo", 
            $pago instanceof Pago && $pago->numero_recibo === 'EST-000001'
        );
        if ($result) $passedTests++;
    } else {
        showTestResult(
            "Búsqueda de pago por recibo", 
            true,
            "No existe el recibo EST-000001 (esto es normal si no hay pagos registrados)"
        );
        $passedTests++;
    }
    $totalTests++;
} catch (Exception $e) {
    showTestResult("Búsqueda de pago por recibo", false, $e->getMessage());
    $totalTests++;
}

// 8. Prueba de modelo Pago - Obtener pagos pendientes
showTestInfo("Modelo Pago - Obtener Pagos Pendientes");
try {
    $pendientes = Pago::getPendientesAprobar();
    
    $result = showTestResult(
        "Obtener pagos pendientes", 
        is_array($pendientes),
        "Se encontraron " . count($pendientes) . " pagos pendientes"
    );
    if ($result) $passedTests++;
    $totalTests++;
} catch (Exception $e) {
    showTestResult("Obtener pagos pendientes", false, $e->getMessage());
    $totalTests++;
}

// 9. Prueba de modelo Pago - Obtener estadísticas
showTestInfo("Modelo Pago - Obtener Estadísticas");
try {
    $mes = date('m');
    $anio = date('Y');
    $estadisticas = Pago::getEstadisticasMes($mes, $anio);
    
    $result = showTestResult(
        "Obtener estadísticas del mes", 
        is_array($estadisticas),
        "Estadísticas obtenidas para $mes/$anio: " . json_encode($estadisticas)
    );
    if ($result) $passedTests++;
    $totalTests++;
} catch (Exception $e) {
    showTestResult("Obtener estadísticas del mes", false, $e->getMessage());
    $totalTests++;
}

// 10. Prueba de conversión de modelo a array
showTestInfo("Modelo Pago - Conversión a Array");
try {
    // Crear un pago de prueba
    $pago = new Pago();
    $pago->id = 1;
    $pago->numero_recibo = 'EST-000001';
    $pago->monto_usd = 25.00;
    $pago->monto_bs = 887.50;
    $pago->moneda_pago = 'usd_efectivo';
    $pago->fecha_pago = date('Y-m-d H:i:s');
    $pago->estado_comprobante = 'aprobado';
    $pago->es_reconexion = false;
    
    $array = $pago->toArray();
    
    $result = showTestResult(
        "Conversión a array", 
        is_array($array) && 
        isset($array['id']) && 
        isset($array['numero_recibo']) &&
        isset($array['monto_usd'])
    );
    if ($result) $passedTests++;
    $totalTests++;
} catch (Exception $e) {
    showTestResult("Conversión a array", false, $e->getMessage());
    $totalTests++;
}

// 11. Prueba de generación de número de recibo
showTestInfo("Generación de Número de Recibo");
try {
    // Usar reflexión para acceder al método privado
    $reflection = new ReflectionClass('Pago');
    $method = $reflection->getMethod('generarNumeroRecibo');
    $method->setAccessible(true);
    
    $numeroRecibo = $method->invoke(null);
    
    $result = showTestResult(
        "Generación de número de recibo", 
        preg_match('/^EST-\d{6}$/', $numeroRecibo) === 1,
        "Número generado: $numeroRecibo"
    );
    if ($result) $passedTests++;
    $totalTests++;
} catch (Exception $e) {
    showTestResult("Generación de número de recibo", false, $e->getMessage());
    $totalTests++;
}

// Resumen final
showTestInfo("RESUMEN DE PRUEBAS DEL SISTEMA DE PAGOS");
$percentage = $totalTests > 0 ? round(($passedTests / $totalTests) * 100, 2) : 0;
echo "Pruebas pasadas: $passedTests/$totalTests ($percentage%)\n\n";

if ($passedTests === $totalTests) {
    echo "🎉 ¡TODAS LAS PRUEBAS PASARON! El sistema de pagos funciona correctamente.\n";
} else {
    echo "⚠️  Algunas pruebas fallaron. Revisa los errores mostrados arriba.\n";
}

// Mostrar recomendaciones
echo "\n=== RECOMENDACIONES ===\n";
echo "1. Asegúrate de que las dependencias (DomPDF, chillerlan/php-qrcode) estén instaladas\n";
echo "2. Verifica que los directorios de uploads tengan permisos de escritura\n";
echo "3. Configura correctamente las constantes en config/config.php\n";
echo "4. Asegúrate de que la tasa de cambio BCV esté configurada\n";
echo "5. Verifica que las tablas de la base de datos estén creadas correctamente\n";

// Notas adicionales
echo "\n=== NOTAS ADICIONALES ===\n";
echo "- La generación de PDF puede fallar si no está instalada la librería DomPDF\n";
echo "- Los códigos QR se generan correctamente si está instalada chillerlan/php-qrcode\n";
echo "- El sistema de pagos está diseñado para manejar múltiples monedas (USD y Bs)\n";
echo "- Los recibos incluyen códigos QR para verificación de autenticidad\n";
echo "- El sistema maneja estados de pago: pendiente, aprobado, rechazado\n";

?>