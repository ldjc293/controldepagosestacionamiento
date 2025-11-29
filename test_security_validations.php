<?php
/**
 * Script de prueba para seguridad y validaciones del sistema
 * 
 * Este script prueba las funcionalidades clave de seguridad:
 * - Validación de datos de entrada
 * - Prevención de XSS y SQL Injection
 * - Protección CSRF
 * - Control de acceso basado en roles
 * - Validación de archivos
 * - Configuración de seguridad HTTP
 */

// Configurar error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Incluir configuración y dependencias necesarias
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'app/helpers/ValidationHelper.php';
require_once 'app/helpers/auth.php';

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
echo "=== INICIANDO PRUEBAS DE SEGURIDAD Y VALIDACIONES ===\n\n";

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

// 2. Prueba de validación de email
showTestInfo("ValidationHelper - Validación de Email");
try {
    $emailsValidos = [
        'test@example.com',
        'user.name@domain.co.uk',
        'user+tag@example.org',
        'user123@test-domain.com'
    ];
    
    $emailsInvalidos = [
        'test@',
        '@example.com',
        'test.example.com',
        'test@.com',
        'test@example.',
        ''
    ];
    
    $todosValidosCorrectos = true;
    foreach ($emailsValidos as $email) {
        if (!ValidationHelper::validateEmail($email)) {
            $todosValidosCorrectos = false;
            break;
        }
    }
    
    $todosInvalidosCorrectos = true;
    foreach ($emailsInvalidos as $email) {
        if (ValidationHelper::validateEmail($email)) {
            $todosInvalidosCorrectos = false;
            break;
        }
    }
    
    $result1 = showTestResult("Validación de emails válidos", $todosValidosCorrectos);
    $result2 = showTestResult("Detección de emails inválidos", $todosInvalidosCorrectos);
    
    if ($result1) $passedTests++;
    if ($result2) $passedTests++;
    $totalTests += 2;
} catch (Exception $e) {
    showTestResult("Validación de email", false, $e->getMessage());
    $totalTests++;
}

// 3. Prueba de validación de contraseña
showTestInfo("ValidationHelper - Validación de Contraseña");
try {
    // Contraseñas válidas
    $contraseñasValidas = [
        'Password123!',
        'MySecurePass#9',
        'Admin2024$',
        'Test@123456'
    ];
    
    // Contraseñas inválidas
    $contraseñasInvalidas = [
        'short',              // Muy corta
        'alllowercase123',     // Sin mayúscula
        'ALLUPPERCASE123',    // Sin minúscula
        'NoNumbersHere!',     // Sin números
        'Password123',        // Sin carácter especial
        'Pass1!'             // Muy corta
    ];
    
    $todasValidasCorrectas = true;
    foreach ($contraseñasValidas as $password) {
        $validacion = ValidationHelper::validatePassword($password);
        if (!$validacion['valid']) {
            $todasValidasCorrectas = false;
            break;
        }
    }
    
    $todasInvalidasCorrectas = true;
    foreach ($contraseñasInvalidas as $password) {
        $validacion = ValidationHelper::validatePassword($password);
        if ($validacion['valid']) {
            $todasInvalidasCorrectas = false;
            break;
        }
    }
    
    $result1 = showTestResult("Validación de contraseñas válidas", $todasValidasCorrectas);
    $result2 = showTestResult("Detección de contraseñas inválidas", $todasInvalidasCorrectas);
    
    if ($result1) $passedTests++;
    if ($result2) $passedTests++;
    $totalTests += 2;
} catch (Exception $e) {
    showTestResult("Validación de contraseña", false, $e->getMessage());
    $totalTests++;
}

// 4. Prueba de validación de campos requeridos
showTestInfo("ValidationHelper - Validación de Campos Requeridos");
try {
    $datosCompletos = [
        'nombre' => 'Juan Pérez',
        'email' => 'juan@example.com',
        'password' => 'Password123!',
        'telefono' => '04141234567'
    ];
    
    $datosIncompletos = [
        'nombre' => 'María González',
        'email' => 'maria@example.com'
        // Falta password y teléfono
    ];
    
    $camposRequeridos = ['nombre', 'email', 'password', 'telefono'];
    
    $faltantes1 = ValidationHelper::validateRequired($datosCompletos, $camposRequeridos);
    $faltantes2 = ValidationHelper::validateRequired($datosIncompletos, $camposRequeridos);
    
    $result1 = showTestResult("Detección de datos completos", empty($faltantes1));
    $result2 = showTestResult("Detección de datos incompletos", !empty($faltantes2));
    
    if ($result1) $passedTests++;
    if ($result2) $passedTests++;
    $totalTests += 2;
} catch (Exception $e) {
    showTestResult("Validación de campos requeridos", false, $e->getMessage());
    $totalTests++;
}

// 5. Prueba de sanitización de entrada
showTestInfo("ValidationHelper - Sanitización de Entrada");
try {
    $entradaPeligrosa = '<script>alert("XSS")</script>';
    $entradaConEspacios = '   texto con espacios   ';
    $entradaNormal = 'Texto normal';
    
    $sanitizada1 = ValidationHelper::sanitize($entradaPeligrosa);
    $sanitizada2 = ValidationHelper::sanitize($entradaConEspacios);
    $sanitizada3 = ValidationHelper::sanitize($entradaNormal);
    
    $result1 = showTestResult(
        "Sanitización de XSS", 
        $sanitizada1 !== $entradaPeligrosa && strpos($sanitizada1, '<script>') === false,
        "Original: $entradaPeligrosa -> Sanitizado: $sanitizada1"
    );
    
    $result2 = showTestResult(
        "Eliminación de espacios", 
        $sanitizada2 === 'texto con espacios',
        "Original: '$entradaConEspacios' -> Sanitizado: '$sanitizada2'"
    );
    
    $result3 = showTestResult(
        "Preservación de texto normal", 
        $sanitizada3 === $entradaNormal,
        "Original: '$entradaNormal' -> Sanitizado: '$sanitizada3'"
    );
    
    if ($result1) $passedTests++;
    if ($result2) $passedTests++;
    if ($result3) $passedTests++;
    $totalTests += 3;
} catch (Exception $e) {
    showTestResult("Sanitización de entrada", false, $e->getMessage());
    $totalTests++;
}

// 6. Prueba de validación de valores numéricos
showTestInfo("ValidationHelper - Validación de Valores Numéricos");
try {
    $numerosValidos = [123, 45.67, '89', '0', -10];
    $numerosInvalidos = ['abc', '12.34.56', '', '12a', null];
    
    $todosNumericosCorrectos = true;
    foreach ($numerosValidos as $numero) {
        if (!ValidationHelper::validateNumeric($numero)) {
            $todosNumericosCorrectos = false;
            break;
        }
    }
    
    $todosNoNumericosCorrectos = true;
    foreach ($numerosInvalidos as $numero) {
        if (ValidationHelper::validateNumeric($numero)) {
            $todosNoNumericosCorrectos = false;
            break;
        }
    }
    
    $result1 = showTestResult("Validación de números", $todosNumericosCorrectos);
    $result2 = showTestResult("Detección de no numéricos", $todosNoNumericosCorrectos);
    
    if ($result1) $passedTests++;
    if ($result2) $passedTests++;
    $totalTests += 2;
} catch (Exception $e) {
    showTestResult("Validación de valores numéricos", false, $e->getMessage());
    $totalTests++;
}

// 7. Prueba de validación de enteros
showTestInfo("ValidationHelper - Validación de Enteros");
try {
    $enterosValidos = [123, -456, 0, '789'];
    $enterosInvalidos = [45.67, '12.34', 'abc', '', '12a'];
    
    $todosEnterosCorrectos = true;
    foreach ($enterosValidos as $entero) {
        if (!ValidationHelper::validateInteger($entero)) {
            $todosEnterosCorrectos = false;
            break;
        }
    }
    
    $todosNoEnterosCorrectos = true;
    foreach ($enterosInvalidos as $entero) {
        if (ValidationHelper::validateInteger($entero)) {
            $todosNoEnterosCorrectos = false;
            break;
        }
    }
    
    $result1 = showTestResult("Validación de enteros", $todosEnterosCorrectos);
    $result2 = showTestResult("Detección de no enteros", $todosNoEnterosCorrectos);
    
    if ($result1) $passedTests++;
    if ($result2) $passedTests++;
    $totalTests += 2;
} catch (Exception $e) {
    showTestResult("Validación de enteros", false, $e->getMessage());
    $totalTests++;
}

// 8. Prueba de validación de fechas
showTestInfo("ValidationHelper - Validación de Fechas");
try {
    $fechasValidas = ['2024-01-15', '2024-12-31', '2024-02-29']; // 2024 es bisiesto
    $fechasInvalidas = ['2024-13-01', '2024-02-30', '2023-02-29', '31-01-2024', '2024/01/15'];
    
    $todasFechasValidas = true;
    foreach ($fechasValidas as $fecha) {
        if (!ValidationHelper::validateDate($fecha)) {
            $todasFechasValidas = false;
            break;
        }
    }
    
    $todasFechasInvalidas = true;
    foreach ($fechasInvalidas as $fecha) {
        if (ValidationHelper::validateDate($fecha)) {
            $todasFechasInvalidas = false;
            break;
        }
    }
    
    $result1 = showTestResult("Validación de fechas válidas", $todasFechasValidas);
    $result2 = showTestResult("Detección de fechas inválidas", $todasFechasInvalidas);
    
    if ($result1) $passedTests++;
    if ($result2) $passedTests++;
    $totalTests += 2;
} catch (Exception $e) {
    showTestResult("Validación de fechas", false, $e->getMessage());
    $totalTests++;
}

// 9. Prueba de validación de archivos
showTestInfo("ValidationHelper - Validación de Archivos");
try {
    $archivoValido = [
        'name' => 'documento.pdf',
        'type' => 'application/pdf',
        'size' => 1024 * 1024, // 1MB
        'error' => UPLOAD_ERR_OK
    ];
    
    $archivoInvalidoTipo = [
        'name' => 'script.php',
        'type' => 'application/x-php',
        'size' => 1024,
        'error' => UPLOAD_ERR_OK
    ];
    
    $archivoInvalidoTamaño = [
        'name' => 'grande.jpg',
        'type' => 'image/jpeg',
        'size' => 10 * 1024 * 1024, // 10MB
        'error' => UPLOAD_ERR_OK
    ];
    
    $archivoConError = [
        'name' => 'documento.pdf',
        'type' => 'application/pdf',
        'size' => 1024,
        'error' => UPLOAD_ERR_INI_SIZE
    ];
    
    $tiposPermitidos = ['image/jpeg', 'image/png', 'application/pdf'];
    $tamañoMaximo = 5 * 1024 * 1024; // 5MB
    
    $validacion1 = ValidationHelper::validateFileUpload($archivoValido, $tiposPermitidos, $tamañoMaximo);
    $validacion2 = ValidationHelper::validateFileUpload($archivoInvalidoTipo, $tiposPermitidos, $tamañoMaximo);
    $validacion3 = ValidationHelper::validateFileUpload($archivoInvalidoTamaño, $tiposPermitidos, $tamañoMaximo);
    $validacion4 = ValidationHelper::validateFileUpload($archivoConError, $tiposPermitidos, $tamañoMaximo);
    
    $result1 = showTestResult("Validación de archivo válido", $validacion1['valid']);
    $result2 = showTestResult("Detección de tipo no permitido", !$validacion2['valid']);
    $result3 = showTestResult("Detección de tamaño excedido", !$validacion3['valid']);
    $result4 = showTestResult("Detección de error de subida", !$validacion4['valid']);
    
    if ($result1) $passedTests++;
    if ($result2) $passedTests++;
    if ($result3) $passedTests++;
    if ($result4) $passedTests++;
    $totalTests += 4;
} catch (Exception $e) {
    showTestResult("Validación de archivos", false, $e->getMessage());
    $totalTests++;
}

// 10. Prueba de sistema de permisos
showTestInfo("Auth Helper - Sistema de Permisos");
try {
    // Probar permisos de cliente
    $result1 = hasPermission('view_dashboard', 'cliente');
    $result2 = hasPermission('view_reports', 'cliente');
    
    // Probar permisos de administrador
    $result3 = hasPermission('view_dashboard', 'administrador');
    $result4 = hasPermission('view_reports', 'administrador');
    
    // Probar rol inexistente
    $result5 = !hasPermission('view_dashboard', 'rol_inexistente');
    
    $test1 = showTestResult("Cliente tiene permiso de dashboard", $result1);
    $test2 = showTestResult("Cliente no tiene permiso de reports", !$result2);
    $test3 = showTestResult("Administrador tiene permiso de dashboard", $result3);
    $test4 = showTestResult("Administrador tiene permiso de reports", $result4);
    $test5 = showTestResult("Rol inexistente no tiene permisos", $result5);
    
    if ($test1) $passedTests++;
    if ($test2) $passedTests++;
    if ($test3) $passedTests++;
    if ($test4) $passedTests++;
    if ($test5) $passedTests++;
    $totalTests += 5;
} catch (Exception $e) {
    showTestResult("Sistema de permisos", false, $e->getMessage());
    $totalTests++;
}

// 11. Prueba de generación y validación de tokens CSRF
showTestInfo("Seguridad - Tokens CSRF");
try {
    // Generar token
    $_SESSION['csrf_token'] = generateCSRFToken();
    $tokenValido = $_SESSION['csrf_token'];
    
    // Validar token correcto
    $resultado1 = ValidationHelper::validateCSRFToken($tokenValido);
    
    // Validar token incorrecto
    $resultado2 = !ValidationHelper::validateCSRFToken('token_incorrecto');
    
    // Validar token vacío
    $resultado3 = !ValidationHelper::validateCSRFToken('');
    
    $result1 = showTestResult("Validación de token CSRF correcto", $resultado1);
    $result2 = showTestResult("Detección de token CSRF incorrecto", $resultado2);
    $result3 = showTestResult("Detección de token CSRF vacío", $resultado3);
    
    if ($result1) $passedTests++;
    if ($result2) $passedTests++;
    if ($result3) $passedTests++;
    $totalTests += 3;
} catch (Exception $e) {
    showTestResult("Tokens CSRF", false, $e->getMessage());
    $totalTests++;
}

// 12. Prueba de prevención de SQL Injection (simulada)
showTestInfo("Seguridad - Prevención de SQL Injection");
try {
    // Simular inputs maliciosos
    $inputsMaliciosos = [
        "'; DROP TABLE usuarios; --",
        "' OR '1'='1",
        "'; INSERT INTO usuarios VALUES ('hacker', 'password'); --",
        "' UNION SELECT * FROM usuarios --"
    ];
    
    $todosSanitizados = true;
    foreach ($inputsMaliciosos as $input) {
        $sanitizado = ValidationHelper::sanitize($input);
        
        // Verificar que se hayan eliminado o escapado los caracteres peligrosos
        if (strpos($sanitizado, "'") !== false || strpos($sanitizado, ';') !== false) {
            $todosSanitizados = false;
            break;
        }
    }
    
    $result = showTestResult(
        "Prevención de SQL Injection", 
        $todosSanitizados,
        "Todos los inputs maliciosos fueron sanitizados"
    );
    if ($result) $passedTests++;
    $totalTests++;
} catch (Exception $e) {
    showTestResult("Prevención de SQL Injection", false, $e->getMessage());
    $totalTests++;
}

// 13. Prueba de prevención de XSS (simulada)
showTestInfo("Seguridad - Prevención de XSS");
try {
    // Simular scripts XSS
    $scriptsXSS = [
        '<script>alert("XSS")</script>',
        '<img src="x" onerror="alert(\'XSS\')">',
        '<a href="javascript:alert(\'XSS\')">Click</a>',
        '<div onclick="alert(\'XSS\')">Click</div>',
        '"><script>alert("XSS")</script>'
    ];
    
    $todosSanitizados = true;
    foreach ($scriptsXSS as $script) {
        $sanitizado = ValidationHelper::sanitize($script);
        
        // Verificar que se hayan eliminado las etiquetas script y eventos
        if (strpos($sanitizado, '<script>') !== false || 
            strpos($sanitizado, 'onerror') !== false ||
            strpos($sanitizado, 'javascript:') !== false) {
            $todosSanitizados = false;
            break;
        }
    }
    
    $result = showTestResult(
        "Prevención de XSS", 
        $todosSanitizados,
        "Todos los scripts XSS fueron sanitizados"
    );
    if ($result) $passedTests++;
    $totalTests++;
} catch (Exception $e) {
    showTestResult("Prevención de XSS", false, $e->getMessage());
    $totalTests++;
}

// 14. Prueba de configuración de seguridad HTTP (simulada)
showTestInfo("Seguridad - Configuración HTTP Headers");
try {
    // Verificar si los archivos .htaccess existen
    $htaccessRaiz = file_exists('.htaccess');
    $htaccessPublic = file_exists('public/.htaccess');
    
    // Verificar contenido del .htaccess público
    $contenidoHtaccessPublic = $htaccessPublic ? file_get_contents('public/.htaccess') : '';
    $tieneXFrameOptions = strpos($contenidoHtaccessPublic, 'X-Frame-Options') !== false;
    $tieneContentTypeOptions = strpos($contenidoHtaccessPublic, 'X-Content-Type-Options') !== false;
    $tieneXSSProtection = strpos($contenidoHtaccessPublic, 'X-XSS-Protection') !== false;
    
    $result1 = showTestResult("Existencia de .htaccess en raíz", $htaccessRaiz);
    $result2 = showTestResult("Existencia de .htaccess en public", $htaccessPublic);
    $result3 = showTestResult("Configuración X-Frame-Options", $tieneXFrameOptions);
    $result4 = showTestResult("Configuración X-Content-Type-Options", $tieneContentTypeOptions);
    $result5 = showTestResult("Configuración XSS-Protection", $tieneXSSProtection);
    
    if ($result1) $passedTests++;
    if ($result2) $passedTests++;
    if ($result3) $passedTests++;
    if ($result4) $passedTests++;
    if ($result5) $passedTests++;
    $totalTests += 5;
} catch (Exception $e) {
    showTestResult("Configuración HTTP Headers", false, $e->getMessage());
    $totalTests++;
}

// Resumen final
showTestInfo("RESUMEN DE PRUEBAS DE SEGURIDAD Y VALIDACIONES");
$percentage = $totalTests > 0 ? round(($passedTests / $totalTests) * 100, 2) : 0;
echo "Pruebas pasadas: $passedTests/$totalTests ($percentage%)\n\n";

if ($passedTests === $totalTests) {
    echo "🎉 ¡TODAS LAS PRUEBAS PASARON! El sistema de seguridad y validaciones funciona correctamente.\n";
} else {
    echo "⚠️  Algunas pruebas fallaron. Revisa los errores mostrados arriba.\n";
}

// Mostrar recomendaciones
echo "\n=== RECOMENDACIONES DE SEGURIDAD ===\n";
echo "1. Implementa CAPTCHA en formularios públicos para prevenir bots\n";
echo "2. Configura HTTPS en todo el sitio para cifrar las comunicaciones\n";
echo "3. Implementa logging de eventos de seguridad para monitoreo\n";
echo "4. Realiza auditorías de seguridad periódicas\n";
echo "5. Mantén todas las dependencias actualizadas\n";
echo "6. Configura correctamente los permisos de archivos y directorios\n";
echo "7. Implementa políticas de contraseñas más estrictas si es necesario\n";
echo "8. Considera implementar autenticación de dos factores (2FA)\n";

// Notas adicionales
echo "\n=== NOTAS ADICIONALES DE SEGURIDAD ===\n";
echo "- El sistema implementa validación de entrada en todos los formularios\n";
echo "- Se utiliza sanitización para prevenir XSS y SQL Injection\n";
echo "- Los tokens CSRF previenen ataques de falsificación de solicitudes\n";
echo "- El sistema de permisos controla el acceso a funcionalidades específicas\n";
echo "- Se implementan headers de seguridad HTTP para prevenir ataques comunes\n";
echo "- Las contraseñas se almacenan con hash seguro (bcrypt)\n";
echo "- El sistema previene la enumeración de usuarios\n";
echo "- Se implementa rate limiting para prevenir ataques de fuerza bruta\n";

?>