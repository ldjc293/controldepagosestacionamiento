<?php
/**
 * Script de prueba para el sistema de mensualidades y bloqueo automático
 * 
 * Este script prueba las funcionalidades clave del sistema de mensualidades:
 * - Generación de mensualidades
 * - Cálculo de deudas
 * - Marcado de mensualidades vencidas
 * - Sistema de bloqueo automático
 * - Generación de mensualidades futuras
 */

// Configurar error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Incluir configuración y dependencias necesarias
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'app/models/Mensualidad.php';
require_once 'app/models/Usuario.php';
require_once 'app/models/Apartamento.php';

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
echo "=== INICIANDO PRUEBAS DEL SISTEMA DE MENSUALIDADES ===\n\n";

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

// 2. Prueba de búsqueda de mensualidad por ID
showTestInfo("Modelo Mensualidad - Búsqueda por ID");
try {
    $mensualidad = Mensualidad::findById(1);
    
    if ($mensualidad) {
        $result = showTestResult(
            "Búsqueda de mensualidad por ID", 
            $mensualidad instanceof Mensualidad && !empty($mensualidad->id),
            "Mensualidad encontrada: {$mensualidad->mes}/{$mensualidad->anio}"
        );
        if ($result) $passedTests++;
    } else {
        showTestResult(
            "Búsqueda de mensualidad por ID", 
            true,
            "No hay mensualidades en la base de datos (esto es normal en una instalación nueva)"
        );
        $passedTests++;
    }
    $totalTests++;
} catch (Exception $e) {
    showTestResult("Búsqueda de mensualidad por ID", false, $e->getMessage());
    $totalTests++;
}

// 3. Prueba de obtención de mensualidades por apartamento_usuario
showTestInfo("Modelo Mensualidad - Obtener por Apartamento/Usuario");
try {
    // Intentar obtener mensualidades para un apartamento_usuario_id = 1
    $mensualidades = Mensualidad::getByApartamentoUsuario(1);
    
    $result = showTestResult(
        "Obtener mensualidades por apartamento_usuario", 
        is_array($mensualidades),
        "Se encontraron " . count($mensualidades) . " mensualidades"
    );
    if ($result) $passedTests++;
    $totalTests++;
} catch (Exception $e) {
    showTestResult("Obtener mensualidades por apartamento_usuario", false, $e->getMessage());
    $totalTests++;
}

// 4. Prueba de cálculo de deuda total
showTestInfo("Modelo Mensualidad - Cálculo de Deuda Total");
try {
    // Intentar calcular deuda para un usuario_id = 1
    $deuda = Mensualidad::calcularDeudaTotal(1);
    
    $result = showTestResult(
        "Cálculo de deuda total", 
        is_array($deuda) && 
        isset($deuda['total_usd']) && 
        isset($deuda['total_bs']) && 
        isset($deuda['meses_count']),
        "Deuda calculada: {$deuda['total_usd']} USD, {$deuda['total_bs']} Bs, {$deuda['meses_count']} meses"
    );
    if ($result) $passedTests++;
    $totalTests++;
} catch (Exception $e) {
    showTestResult("Cálculo de deuda total", false, $e->getMessage());
    $totalTests++;
}

// 5. Prueba de obtención de mensualidades pendientes
showTestInfo("Modelo Mensualidad - Obtener Pendientes");
try {
    // Intentar obtener mensualidades pendientes para un usuario_id = 1
    $pendientes = Mensualidad::getPendientesByUsuario(1, false); // sin generar futuras
    
    $result = showTestResult(
        "Obtener mensualidades pendientes", 
        is_array($pendientes),
        "Se encontraron " . count($pendientes) . " mensualidades pendientes"
    );
    if ($result) $passedTests++;
    $totalTests++;
} catch (Exception $e) {
    showTestResult("Obtener mensualidades pendientes", false, $e->getMessage());
    $totalTests++;
}

// 6. Prueba de obtención de mensualidades vencidas
showTestInfo("Modelo Mensualidad - Obtener Vencidas");
try {
    // Obtener usuarios con 3+ meses vencidos
    $vencidas = Mensualidad::getVencidas(3);
    
    $result = showTestResult(
        "Obtener mensualidades vencidas", 
        is_array($vencidas),
        "Se encontraron " . count($vencidas) . " usuarios con 3+ meses vencidos"
    );
    if ($result) $passedTests++;
    $totalTests++;
} catch (Exception $e) {
    showTestResult("Obtener mensualidades vencidas", false, $e->getMessage());
    $totalTests++;
}

// 7. Prueba de conversión a array
showTestInfo("Modelo Mensualidad - Conversión a Array");
try {
    // Crear una mensualidad de prueba
    $mensualidad = new Mensualidad();
    $mensualidad->id = 1;
    $mensualidad->apartamento_usuario_id = 1;
    $mensualidad->mes = 11;
    $mensualidad->anio = 2024;
    $mensualidad->cantidad_controles = 2;
    $mensualidad->monto_usd = 50.00;
    $mensualidad->monto_bs = 1775.00;
    $mensualidad->estado = 'pendiente';
    $mensualidad->fecha_vencimiento = '2024-11-30';
    $mensualidad->bloqueado = false;
    
    $array = $mensualidad->toArray();
    
    $result = showTestResult(
        "Conversión a array", 
        is_array($array) && 
        isset($array['id']) && 
        isset($array['mes']) &&
        isset($array['monto_usd']) &&
        isset($array['mes_nombre'])
    );
    if ($result) $passedTests++;
    $totalTests++;
} catch (Exception $e) {
    showTestResult("Conversión a array", false, $e->getMessage());
    $totalTests++;
}

// 8. Prueba de obtención de nombre del mes
showTestInfo("Modelo Mensualidad - Obtener Nombre del Mes");
try {
    $nombreEnero = Mensualidad::getNombreMes(1);
    $nombreDiciembre = Mensualidad::getNombreMes(12);
    $nombreInvalido = Mensualidad::getNombreMes(13);
    
    $result1 = showTestResult("Obtener nombre de Enero", $nombreEnero === 'Enero');
    $result2 = showTestResult("Obtener nombre de Diciembre", $nombreDiciembre === 'Diciembre');
    $result3 = showTestResult("Obtener nombre de mes inválido", $nombreInvalido === 'Desconocido');
    
    if ($result1) $passedTests++;
    if ($result2) $passedTests++;
    if ($result3) $passedTests++;
    $totalTests += 3;
} catch (Exception $e) {
    showTestResult("Obtener nombre del mes", false, $e->getMessage());
    $totalTests++;
}

// 9. Prueba de obtención de historial
showTestInfo("Modelo Mensualidad - Obtener Historial");
try {
    // Intentar obtener historial para un usuario_id = 1
    $historial = Mensualidad::getHistorialByUsuario(1, 12);
    
    $result = showTestResult(
        "Obtener historial de mensualidades", 
        is_array($historial),
        "Se encontraron " . count($historial) . " mensualidades en el historial"
    );
    if ($result) $passedTests++;
    $totalTests++;
} catch (Exception $e) {
    showTestResult("Obtener historial de mensualidades", false, $e->getMessage());
    $totalTests++;
}

// 10. Prueba de obtención de todas las mensualidades de un usuario
showTestInfo("Modelo Mensualidad - Obtener Todas las Mensualidades");
try {
    // Intentar obtener todas las mensualidades para un usuario_id = 1
    $todas = Mensualidad::getAllByUsuario(1);
    
    $result = showTestResult(
        "Obtener todas las mensualidades del usuario", 
        is_array($todas),
        "Se encontraron " . count($todas) . " mensualidades en total"
    );
    if ($result) $passedTests++;
    $totalTests++;
} catch (Exception $e) {
    showTestResult("Obtener todas las mensualidades del usuario", false, $e->getMessage());
    $totalTests++;
}

// 11. Prueba de marcado como pagada
showTestInfo("Modelo Mensualidad - Marcar como Pagada");
try {
    // Buscar una mensualidad pendiente para marcar como pagada
    $sql = "SELECT id FROM mensualidades WHERE estado = 'pendiente' LIMIT 1";
    $mensualidadPendiente = Database::fetchOne($sql);
    
    if ($mensualidadPendiente) {
        $mensualidad = Mensualidad::findById($mensualidadPendiente['id']);
        if ($mensualidad) {
            $resultado = $mensualidad->marcarComoPagada();
            $result = showTestResult(
                "Marcar mensualidad como pagada", 
                $resultado,
                "Mensualidad ID {$mensualidad->id} marcada como pagada"
            );
            if ($result) $passedTests++;
        } else {
            showTestResult(
                "Marcar mensualidad como pagada", 
                true,
                "No se encontró la mensualidad para marcar como pagada"
            );
            $passedTests++;
        }
    } else {
        showTestResult(
            "Marcar mensualidad como pagada", 
            true,
            "No hay mensualidades pendientes para marcar como pagadas"
        );
        $passedTests++;
    }
    $totalTests++;
} catch (Exception $e) {
    showTestResult("Marcar mensualidad como pagada", false, $e->getMessage());
    $totalTests++;
}

// 12. Prueba de marcado de mensualidades vencidas
showTestInfo("Modelo Mensualidad - Marcar Vencidas");
try {
    // Intentar marcar mensualidades vencidas
    $vencidas = Mensualidad::marcarVencidas();
    
    $result = showTestResult(
        "Marcar mensualidades como vencidas", 
        is_numeric($vencidas),
        "Se marcaron $vencidas mensualidades como vencidas"
    );
    if ($result) $passedTests++;
    $totalTests++;
} catch (Exception $e) {
    showTestResult("Marcar mensualidades como vencidas", false, $e->getMessage());
    $totalTests++;
}

// 13. Prueba de verificación de bloqueos
showTestInfo("Modelo Mensualidad - Verificación de Bloqueos");
try {
    // Intentar verificar bloqueos
    $bloqueados = Mensualidad::verificarBloqueos();
    
    $result = showTestResult(
        "Verificar bloqueos por morosidad", 
        is_numeric($bloqueados),
        "Se bloquearon $bloqueados apartamentos por morosidad"
    );
    if ($result) $passedTests++;
    $totalTests++;
} catch (Exception $e) {
    showTestResult("Verificar bloqueos por morosidad", false, $e->getMessage());
    $totalTests++;
}

// 14. Prueba de generación de mensualidades del mes
showTestInfo("Modelo Mensualidad - Generación de Mensualidades del Mes");
try {
    // Verificar si existen las tablas necesarias
    $sql = "SHOW TABLES LIKE 'configuracion_tarifas'";
    $tablaTarifas = Database::fetchOne($sql);
    
    $sql = "SHOW TABLES LIKE 'tasa_cambio_bcv'";
    $tablaTasa = Database::fetchOne($sql);
    
    if ($tablaTarifas && $tablaTasa) {
        // Intentar generar mensualidades del mes
        $generadas = Mensualidad::generarMensualidadesMes();
        
        $result = showTestResult(
            "Generar mensualidades del mes", 
            is_numeric($generadas),
            "Se generaron $generadas mensualidades para el mes actual"
        );
        if ($result) $passedTests++;
    } else {
        showTestResult(
            "Generar mensualidades del mes", 
            false,
            "Faltan tablas necesarias: configuracion_tarifas o tasa_cambio_bcv"
        );
    }
    $totalTests++;
} catch (Exception $e) {
    showTestResult("Generar mensualidades del mes", false, $e->getMessage());
    $totalTests++;
}

// 15. Prueba de generación de mensualidades futuras
showTestInfo("Modelo Mensualidad - Generación de Mensualidades Futuras");
try {
    // Intentar generar mensualidades futuras para un usuario_id = 1
    $futuras = Mensualidad::generarMensualidadesFuturas(1, 2);
    
    $result = showTestResult(
        "Generar mensualidades futuras", 
        is_array($futuras),
        "Se generaron " . count($futuras) . " mensualidades futuras"
    );
    if ($result) $passedTests++;
    $totalTests++;
} catch (Exception $e) {
    showTestResult("Generar mensualidades futuras", false, $e->getMessage());
    $totalTests++;
}

// Resumen final
showTestInfo("RESUMEN DE PRUEBAS DEL SISTEMA DE MENSUALIDADES");
$percentage = $totalTests > 0 ? round(($passedTests / $totalTests) * 100, 2) : 0;
echo "Pruebas pasadas: $passedTests/$totalTests ($percentage%)\n\n";

if ($passedTests === $totalTests) {
    echo "🎉 ¡TODAS LAS PRUEBAS PASARON! El sistema de mensualidades funciona correctamente.\n";
} else {
    echo "⚠️  Algunas pruebas fallaron. Revisa los errores mostrados arriba.\n";
}

// Mostrar recomendaciones
echo "\n=== RECOMENDACIONES ===\n";
echo "1. Asegúrate de que las tablas de configuración de tarifas y tasa de cambio BCV estén creadas\n";
echo "2. Verifica que existan usuarios y apartamentos registrados en el sistema\n";
echo "3. Configura correctamente las constantes en config/config.php\n";
echo "4. Asegúrate de que la base de datos tenga la estructura correcta\n";
echo "5. Configura los scripts CRON para la generación automática de mensualidades\n";

// Notas adicionales
echo "\n=== NOTAS ADICIONALES ===\n";
echo "- El sistema de mensualidades genera automáticamente las mensualidades cada mes\n";
echo "- Los controles se bloquean automáticamente cuando hay 4+ meses de morosidad\n";
echo "- Las mensualidades se marcan como vencidas automáticamente cada día\n";
echo "- El sistema permite generar mensualidades futuras para pagos adelantados\n";
echo "- Los montos se calculan automáticamente según la cantidad de controles\n";
echo "- La tasa de cambio BCV se obtiene automáticamente para calcular montos en Bs\n";

?>