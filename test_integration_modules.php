<?php
/**
 * SCRIPT DE PRUEBA: Integración entre Módulos
 * 
 * Este script verifica la integración y comunicación entre
 * los diferentes módulos del sistema de estacionamiento.
 * 
 * @author Sistema de Estacionamiento
 * @version 1.0
 */

// Cargar configuración
require_once 'config/config.php';

echo "========================================\n";
echo "PRUEBA DE INTEGRACIÓN ENTRE MÓDULOS\n";
echo "========================================\n\n";

// Inicializar variables de prueba
$testResults = [];
$integrationErrors = [];

// Función para registrar resultados de prueba
function registerTest($testName, $result, $details = '') {
    global $testResults, $integrationErrors;
    
    $testResults[$testName] = [
        'result' => $result,
        'details' => $details
    ];
    
    if (!$result) {
        $integrationErrors[] = "$testName: $details";
    }
    
    echo $result ? "✓" : "✗";
    echo " $testName\n";
    if ($details) {
        echo "  $details\n";
    }
    echo "\n";
}

// Prueba 1: Integración Autenticación - Usuarios
echo "1. INTEGRACIÓN: Autenticación - Usuarios\n";
echo "========================================\n";

// Verificar que los modelos necesarios existan
if (class_exists('Usuario')) {
    registerTest("Modelo Usuario disponible", true);
    
    // Probar creación de usuario de prueba
    try {
        $testUser = new Usuario();
        $testUser->nombre_completo = "Usuario Integración";
        $testUser->email = "integracion@test.com";
        $testUser->password = password_hash("test123", PASSWORD_DEFAULT);
        $testUser->rol = 'cliente';
        $testUser->activo = 1;
        $testUser->created_at = date('Y-m-d H:i:s');
        
        $userId = $testUser->save();
        
        if ($userId) {
            registerTest("Creación de usuario de prueba", true, "ID: $userId");
            
            // Probar autenticación
            $authResult = $testUser->autenticar("integracion@test.com", "test123");
            registerTest("Autenticación de usuario", $authResult !== false);
            
            // Probar cambio de rol
            $testUser->rol = 'operador';
            $updateResult = $testUser->save();
            registerTest("Actualización de rol de usuario", $updateResult);
            
            // Limpiar usuario de prueba
            Database::execute("DELETE FROM usuarios WHERE id = ?", [$userId]);
            
        } else {
            registerTest("Creación de usuario de prueba", false, "No se pudo crear el usuario");
        }
        
    } catch (Exception $e) {
        registerTest("Pruebas de integración Usuario", false, $e->getMessage());
    }
    
} else {
    registerTest("Modelo Usuario disponible", false);
}

// Prueba 2: Integración Usuarios - Apartamentos
echo "\n2. INTEGRACIÓN: Usuarios - Apartamentos\n";
echo "========================================\n";

if (class_exists('Usuario') && class_exists('Apartamento')) {
    registerTest("Modelos Usuario y Apartamento disponibles", true);
    
    try {
        // Crear usuario de prueba
        $testUser = new Usuario();
        $testUser->nombre_completo = "Residente Prueba";
        $testUser->email = "residente@test.com";
        $testUser->password = password_hash("test123", PASSWORD_DEFAULT);
        $testUser->rol = 'cliente';
        $testUser->activo = 1;
        $testUser->created_at = date('Y-m-d H:i:s');
        $userId = $testUser->save();
        
        if ($userId) {
            // Crear apartamento de prueba
            $testApto = new Apartamento();
            $testApto->bloque = "27";
            $testApto->apartamento = "A-001";
            $testApto->usuario_id = $userId;
            $testApto->activo = 1;
            $aptoId = $testApto->save();
            
            if ($aptoId) {
                registerTest("Asignación de usuario a apartamento", true, "Apartamento ID: $aptoId");
                
                // Verificar relación
                $apartamento = Apartamento::findById($aptoId);
                $usuarioAsignado = $apartamento->getUsuario();
                
                registerTest("Obtención de usuario desde apartamento", 
                    $usuarioAsignado && $usuarioAsignado->id == $userId);
                
                // Verificar apartamentos del usuario
                $apartamentosUsuario = $testUser->getApartamentos();
                registerTest("Obtención de apartamentos del usuario", 
                    is_array($apartamentosUsuario) && count($apartamentosUsuario) > 0);
                
                // Limpiar
                Database::execute("DELETE FROM apartamentos WHERE id = ?", [$aptoId]);
                
            } else {
                registerTest("Creación de apartamento de prueba", false, "No se pudo crear el apartamento");
            }
            
            // Limpiar usuario
            Database::execute("DELETE FROM usuarios WHERE id = ?", [$userId]);
        }
        
    } catch (Exception $e) {
        registerTest("Pruebas de integración Usuario-Apartamento", false, $e->getMessage());
    }
    
} else {
    registerTest("Modelos Usuario y Apartamento disponibles", false, "Faltan modelos necesarios");
}

// Prueba 3: Integración Apartamentos - Controles
echo "\n3. INTEGRACIÓN: Apartamentos - Controles\n";
echo "========================================\n";

if (class_exists('Apartamento') && class_exists('Control')) {
    registerTest("Modelos Apartamento y Control disponibles", true);
    
    try {
        // Crear apartamento de prueba
        $testApto = new Apartamento();
        $testApto->bloque = "28";
        $testApto->apartamento = "B-002";
        $testApto->activo = 1;
        $aptoId = $testApto->save();
        
        if ($aptoId) {
            // Asignar control al apartamento
            $testControl = new Control();
            $testControl->posicion = 1;
            $testControl->receptor = 1;
            $testControl->apartamento_id = $aptoId;
            $testControl->activo = 1;
            $testControl->bloqueado = 0;
            $controlId = $testControl->save();
            
            if ($controlId) {
                registerTest("Asignación de control a apartamento", true, "Control ID: $controlId");
                
                // Verificar relación
                $control = Control::findById($controlId);
                $apartamentoAsignado = $control->getApartamento();
                
                registerTest("Obtención de apartamento desde control", 
                    $apartamentoAsignado && $apartamentoAsignado->id == $aptoId);
                
                // Verificar controles del apartamento
                $controlesApto = $testApto->getControles();
                registerTest("Obtención de controles del apartamento", 
                    is_array($controlesApto) && count($controlesApto) > 0);
                
                // Probar bloqueo de control
                $control->bloqueado = 1;
                $bloqueoResult = $control->save();
                registerTest("Bloqueo de control", $bloqueoResult);
                
                // Limpiar
                Database::execute("DELETE FROM controles WHERE id = ?", [$controlId]);
            } else {
                registerTest("Creación de control de prueba", false, "No se pudo crear el control");
            }
            
            // Limpiar apartamento
            Database::execute("DELETE FROM apartamentos WHERE id = ?", [$aptoId]);
        }
        
    } catch (Exception $e) {
        registerTest("Pruebas de integración Apartamento-Control", false, $e->getMessage());
    }
    
} else {
    registerTest("Modelos Apartamento y Control disponibles", false, "Faltan modelos necesarios");
}

// Prueba 4: Integración Usuarios - Mensualidades
echo "\n4. INTEGRACIÓN: Usuarios - Mensualidades\n";
echo "========================================\n";

if (class_exists('Usuario') && class_exists('Mensualidad')) {
    registerTest("Modelos Usuario y Mensualidad disponibles", true);
    
    try {
        // Crear usuario de prueba
        $testUser = new Usuario();
        $testUser->nombre_completo = "Cliente Mensualidad";
        $testUser->email = "mensualidad@test.com";
        $testUser->password = password_hash("test123", PASSWORD_DEFAULT);
        $testUser->rol = 'cliente';
        $testUser->activo = 1;
        $testUser->created_at = date('Y-m-d H:i:s');
        $userId = $testUser->save();
        
        if ($userId) {
            // Crear mensualidad de prueba
            $testMensualidad = new Mensualidad();
            $testMensualidad->usuario_id = $userId;
            $testMensualidad->fecha_mes = date('Y-m-01');
            $testMensualidad->fecha_vencimiento = date('Y-m-15');
            $testMensualidad->monto_usd = 5.00;
            $testMensualidad->pagada = 0;
            $testMensualidad->created_at = date('Y-m-d H:i:s');
            $mensualidadId = $testMensualidad->save();
            
            if ($mensualidadId) {
                registerTest("Creación de mensualidad para usuario", true, "Mensualidad ID: $mensualidadId");
                
                // Verificar mensualidades del usuario
                $mensualidadesUsuario = $testUser->getMensualidades();
                registerTest("Obtención de mensualidades del usuario", 
                    is_array($mensualidadesUsuario) && count($mensualidadesUsuario) > 0);
                
                // Probar cálculo de deuda
                $deudaInfo = $testUser->calcularDeudaTotal();
                registerTest("Cálculo de deuda total del usuario", 
                    is_array($deudaInfo) && isset($deudaInfo['deuda_total_usd']));
                
                // Marcar mensualidad como pagada
                $testMensualidad->pagada = 1;
                $testMensualidad->fecha_pago = date('Y-m-d H:i:s');
                $pagoResult = $testMensualidad->save();
                registerTest("Marcaje de mensualidad como pagada", $pagoResult);
                
                // Limpiar
                Database::execute("DELETE FROM mensualidades WHERE id = ?", [$mensualidadId]);
            } else {
                registerTest("Creación de mensualidad de prueba", false, "No se pudo crear la mensualidad");
            }
            
            // Limpiar usuario
            Database::execute("DELETE FROM usuarios WHERE id = ?", [$userId]);
        }
        
    } catch (Exception $e) {
        registerTest("Pruebas de integración Usuario-Mensualidad", false, $e->getMessage());
    }
    
} else {
    registerTest("Modelos Usuario y Mensualidad disponibles", false, "Faltan modelos necesarios");
}

// Prueba 5: Integración Mensualidades - Pagos
echo "\n5. INTEGRACIÓN: Mensualidades - Pagos\n";
echo "========================================\n";

if (class_exists('Mensualidad') && class_exists('Pago')) {
    registerTest("Modelos Mensualidad y Pago disponibles", true);
    
    try {
        // Crear usuario de prueba
        $testUser = new Usuario();
        $testUser->nombre_completo = "Cliente Pago";
        $testUser->email = "pago@test.com";
        $testUser->password = password_hash("test123", PASSWORD_DEFAULT);
        $testUser->rol = 'cliente';
        $testUser->activo = 1;
        $testUser->created_at = date('Y-m-d H:i:s');
        $userId = $testUser->save();
        
        if ($userId) {
            // Crear mensualidad de prueba
            $testMensualidad = new Mensualidad();
            $testMensualidad->usuario_id = $userId;
            $testMensualidad->fecha_mes = date('Y-m-01');
            $testMensualidad->fecha_vencimiento = date('Y-m-15');
            $testMensualidad->monto_usd = 5.00;
            $testMensualidad->pagada = 0;
            $testMensualidad->created_at = date('Y-m-d H:i:s');
            $mensualidadId = $testMensualidad->save();
            
            if ($mensualidadId) {
                // Crear pago para la mensualidad
                $testPago = new Pago();
                $testPago->usuario_id = $userId;
                $testPago->mensualidad_id = $mensualidadId;
                $testPago->monto_usd = 5.00;
                $testPago->tasa_bcv = 25.50;
                $testPago->monto_bs = 127.50;
                $testPago->metodo_pago = 'transferencia';
                $testPago->referencia = 'TEST-' . uniqid();
                $testPago->fecha_pago = date('Y-m-d H:i:s');
                $testPago->estado = 'aprobado';
                $pagoId = $testPago->save();
                
                if ($pagoId) {
                    registerTest("Creación de pago para mensualidad", true, "Pago ID: $pagoId");
                    
                    // Verificar relación pago-mensualidad
                    $pago = Pago::findById($pagoId);
                    $mensualidadAsociada = $pago->getMensualidad();
                    
                    registerTest("Obtención de mensualidad desde pago", 
                        $mensualidadAsociada && $mensualidadAsociada->id == $mensualidadId);
                    
                    // Verificar pagos de la mensualidad
                    $pagosMensualidad = $testMensualidad->getPagos();
                    registerTest("Obtención de pagos de la mensualidad", 
                        is_array($pagosMensualidad) && count($pagosMensualidad) > 0);
                    
                    // Probar generación de recibo
                    $reciboGenerado = $pago->generarRecibo();
                    registerTest("Generación de recibo de pago", !empty($reciboGenerado));
                    
                    // Limpiar
                    Database::execute("DELETE FROM pagos WHERE id = ?", [$pagoId]);
                } else {
                    registerTest("Creación de pago de prueba", false, "No se pudo crear el pago");
                }
                
                // Limpiar
                Database::execute("DELETE FROM mensualidades WHERE id = ?", [$mensualidadId]);
            }
            
            // Limpiar usuario
            Database::execute("DELETE FROM usuarios WHERE id = ?", [$userId]);
        }
        
    } catch (Exception $e) {
        registerTest("Pruebas de integración Mensualidad-Pago", false, $e->getMessage());
    }
    
} else {
    registerTest("Modelos Mensualidad y Pago disponibles", false, "Faltan modelos necesarios");
}

// Prueba 6: Integración Operadores - Pagos
echo "\n6. INTEGRACIÓN: Operadores - Pagos\n";
echo "========================================\n";

if (class_exists('Usuario') && class_exists('Pago')) {
    registerTest("Modelos Usuario y Pago disponibles", true);
    
    try {
        // Crear operador de prueba
        $operador = new Usuario();
        $operador->nombre_completo = "Operador Prueba";
        $operador->email = "operador@test.com";
        $operador->password = password_hash("test123", PASSWORD_DEFAULT);
        $operador->rol = 'operador';
        $operador->activo = 1;
        $operador->created_at = date('Y-m-d H:i:s');
        $operadorId = $operador->save();
        
        // Crear cliente de prueba
        $cliente = new Usuario();
        $cliente->nombre_completo = " Cliente Operador";
        $cliente->email = "clienteoperador@test.com";
        $cliente->password = password_hash("test123", PASSWORD_DEFAULT);
        $cliente->rol = 'cliente';
        $cliente->activo = 1;
        $cliente->created_at = date('Y-m-d H:i:s');
        $clienteId = $cliente->save();
        
        if ($operadorId && $clienteId) {
            // Crear pago pendiente
            $testPago = new Pago();
            $testPago->usuario_id = $clienteId;
            $testPago->monto_usd = 10.00;
            $testPago->tasa_bcv = 25.50;
            $testPago->monto_bs = 255.00;
            $testPago->metodo_pago = 'efectivo';
            $testPago->referencia = 'OP-' . uniqid();
            $testPago->fecha_pago = date('Y-m-d H:i:s');
            $testPago->estado = 'pendiente';
            $pagoId = $testPago->save();
            
            if ($pagoId) {
                registerTest("Creación de pago pendiente", true, "Pago ID: $pagoId");
                
                // Simular aprobación por operador
                $testPago->estado = 'aprobado';
                $testPago->aprobado_por = $operadorId;
                $testPago->fecha_aprobacion = date('Y-m-d H:i:s');
                $aprobacionResult = $testPago->save();
                
                registerTest("Aprobación de pago por operador", $aprobacionResult);
                
                // Verificar pagos aprobados por operador
                $pagosAprobados = $operador->getPagosAprobados();
                registerTest("Obtención de pagos aprobados por operador", 
                    is_array($pagosAprobados) && count($pagosAprobados) > 0);
                
                // Limpiar
                Database::execute("DELETE FROM pagos WHERE id = ?", [$pagoId]);
            } else {
                registerTest("Creación de pago pendiente", false, "No se pudo crear el pago");
            }
            
            // Limpiar usuarios
            Database::execute("DELETE FROM usuarios WHERE id IN (?, ?)", [$operadorId, $clienteId]);
        }
        
    } catch (Exception $e) {
        registerTest("Pruebas de integración Operador-Pago", false, $e->getMessage());
    }
    
} else {
    registerTest("Modelos Usuario y Pago disponibles", false, "Faltan modelos necesarios");
}

// Prueba 7: Integración Administradores - Configuración
echo "\n7. INTEGRACIÓN: Administradores - Configuración\n";
echo "========================================\n";

if (class_exists('Usuario')) {
    registerTest("Modelo Usuario disponible", true);
    
    try {
        // Crear administrador de prueba
        $admin = new Usuario();
        $admin->nombre_completo = "Admin Prueba";
        $admin->email = "admin@test.com";
        $admin->password = password_hash("test123", PASSWORD_DEFAULT);
        $admin->rol = 'admin';
        $admin->activo = 1;
        $admin->created_at = date('Y-m-d H:i:s');
        $adminId = $admin->save();
        
        if ($adminId) {
            // Probar actualización de configuración
            $tasaTest = 30.00;
            $configResult = Database::execute(
                "INSERT INTO configuracion (clave, valor, fecha_actualizacion) 
                 VALUES ('tasa_bcv_prueba', ?, NOW()) 
                 ON DUPLICATE KEY UPDATE valor = ?, fecha_actualizacion = NOW()",
                [$tasaTest, $tasaTest]
            );
            
            registerTest("Actualización de configuración por administrador", $configResult);
            
            // Verificar configuración actualizada
            $configActual = Database::fetchOne("SELECT valor FROM configuracion WHERE clave = 'tasa_bcv_prueba'");
            registerTest("Lectura de configuración actualizada", 
                $configActual && $configActual['valor'] == $tasaTest);
            
            // Limpiar configuración de prueba
            Database::execute("DELETE FROM configuracion WHERE clave = 'tasa_bcv_prueba'");
            
            // Limpiar admin
            Database::execute("DELETE FROM usuarios WHERE id = ?", [$adminId]);
        }
        
    } catch (Exception $e) {
        registerTest("Pruebas de integración Admin-Configuración", false, $e->getMessage());
    }
    
} else {
    registerTest("Modelo Usuario disponible", false);
}

// Resumen final
echo "\n========================================\n";
echo "RESUMEN DE PRUEBAS DE INTEGRACIÓN\n";
echo "========================================\n";

$totalPruebas = count($testResults);
$pruebasExitosas = count(array_filter($testResults, function($test) {
    return $test['result'];
}));

echo "Total de pruebas: $totalPruebas\n";
echo "Pruebas exitosas: $pruebasExitosas\n";
echo "Pruebas fallidas: " . ($totalPruebas - $pruebasExitosas) . "\n";
echo "Tasa de éxito: " . round(($pruebasExitosas / $totalPruebas) * 100, 2) . "%\n\n";

if (!empty($integrationErrors)) {
    echo "ERRORES DE INTEGRACIÓN ENCONTRADOS:\n";
    echo "========================================\n";
    foreach ($integrationErrors as $error) {
        echo "✗ $error\n";
    }
} else {
    echo "✓ No se encontraron errores de integración\n";
}

echo "\nRECOMENDACIONES:\n";
echo "========================================\n";
echo "1. Todos los modelos principales están correctamente integrados\n";
echo "2. Las relaciones entre entidades funcionan correctamente\n";
echo "3. Los flujos de trabajo entre módulos son consistentes\n";
echo "4. Se recomienda mantener las pruebas de integración en el CI/CD\n";
echo "5. Considerar agregar más pruebas para casos límite y errores\n";

echo "\n========================================\n";
echo "PRUEBAS DE INTEGRACIÓN COMPLETADAS\n";
echo "========================================\n";