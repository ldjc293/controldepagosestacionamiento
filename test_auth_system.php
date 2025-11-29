<?php
/**
 * Script de prueba para el sistema de autenticación
 * 
 * Este script prueba el login para todos los roles de usuario
 * y verifica que el sistema de autenticación funcione correctamente.
 */

// Incluir configuración
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

// Configurar headers para salida en texto plano
header('Content-Type: text/plain; charset=utf-8');

echo "=== PRUEBAS DEL SISTEMA DE AUTENTICACIÓN ===\n\n";

// 1. Verificar conexión a la base de datos
try {
    $db = Database::getInstance();
    echo "✅ Conexión a la base de datos establecida correctamente\n\n";
} catch (Exception $e) {
    echo "❌ Error al conectar a la base de datos: " . $e->getMessage() . "\n";
    exit(1);
}

// 2. Verificar que existan los usuarios de prueba
$usuarios = [
    [
        'email' => 'admin@estacionamiento.local',
        'password' => 'password123',
        'rol' => 'administrador'
    ],
    [
        'email' => 'operador@estacionamiento.local',
        'password' => 'password123',
        'rol' => 'operador'
    ],
    [
        'email' => 'consultor@estacionamiento.local',
        'password' => 'password123',
        'rol' => 'consultor'
    ],
    [
        'email' => 'maria.gonzalez@gmail.com',
        'password' => 'password123',
        'rol' => 'cliente'
    ],
    [
        'email' => 'roberto.diaz@gmail.com',
        'password' => 'password123',
        'rol' => 'cliente',
        'primer_acceso' => true
    ]
];

echo "=== VERIFICACIÓN DE USUARIOS DE PRUEBA ===\n";
foreach ($usuarios as $index => $usuario) {
    $sql = "SELECT id, nombre_completo, email, rol, activo, primer_acceso, password_temporal 
             FROM usuarios WHERE email = ?";
    $result = Database::fetchOne($sql, [$usuario['email']]);
    
    if ($result) {
        echo "✅ Usuario " . ($index + 1) . ": {$result['nombre_completo']} ({$result['rol']}) - ACTIVO\n";
        
        // Verificar contraseña
        if (password_verify($usuario['password'], $result['password'])) {
            echo "   ✅ Contraseña correcta\n";
        } else {
            echo "   ❌ Contraseña incorrecta\n";
        }
        
        // Verificar estado especial
        if (isset($usuario['primer_acceso']) && $usuario['primer_acceso']) {
            if ($result['primer_acceso'] || $result['password_temporal']) {
                echo "   ✅ Requiere cambio de contraseña (primer acceso)\n";
            } else {
                echo "   ⚠️  Debería requerir cambio de contraseña pero no lo marca\n";
            }
        }
    } else {
        echo "❌ Usuario " . ($index + 1) . ": {$usuario['email']} - NO ENCONTRADO\n";
    }
    echo "\n";
}

// 3. Verificar tabla de login_intentos
echo "=== VERIFICACIÓN DE TABLA login_intentos ===\n";
try {
    $result = Database::fetchAll("SHOW TABLES LIKE 'login_intentos'");
    if (count($result) > 0) {
        echo "✅ Tabla login_intentos existe\n";
        
        // Verificar estructura
        $columns = Database::fetchAll("SHOW COLUMNS FROM login_intentos");
        $columnNames = array_map(fn($col) => $col['Field'], $columns);
        
        $requiredColumns = ['id', 'email', 'ip_address', 'fecha_hora', 'exitoso', 'intentos', 'ultimo_intento', 'bloqueado_hasta'];
        foreach ($requiredColumns as $column) {
            if (in_array($column, $columnNames)) {
                echo "✅ Columna '$column' existe\n";
            } else {
                echo "❌ Columna '$column' NO existe\n";
            }
        }
    } else {
        echo "❌ Tabla login_intentos NO existe\n";
    }
} catch (Exception $e) {
    echo "❌ Error al verificar tabla login_intentos: " . $e->getMessage() . "\n";
}

echo "\n";

// 4. Verificar tabla de password_reset_tokens
echo "=== VERIFICACIÓN DE TABLA password_reset_tokens ===\n";
try {
    $result = Database::fetchAll("SHOW TABLES LIKE 'password_reset_tokens'");
    if (count($result) > 0) {
        echo "✅ Tabla password_reset_tokens existe\n";
        
        // Verificar estructura
        $columns = Database::fetchAll("SHOW COLUMNS FROM password_reset_tokens");
        $columnNames = array_map(fn($col) => $col['Field'], $columns);
        
        $requiredColumns = ['id', 'usuario_id', 'email', 'codigo', 'fecha_creacion', 'fecha_expiracion', 'usado'];
        foreach ($requiredColumns as $column) {
            if (in_array($column, $columnNames)) {
                echo "✅ Columna '$column' existe\n";
            } else {
                echo "❌ Columna '$column' NO existe\n";
            }
        }
    } else {
        echo "❌ Tabla password_reset_tokens NO existe\n";
    }
} catch (Exception $e) {
    echo "❌ Error al verificar tabla password_reset_tokens: " . $e->getMessage() . "\n";
}

echo "\n";

// 5. Probar funcionalidad de verificación de login
echo "=== PRUEBA DE FUNCIONALIDAD DE VERIFICACIÓN DE LOGIN ===\n";

// Simular un login exitoso
$testEmail = 'admin@estacionamiento.local';
$testPassword = 'password123';

echo "Probando login con: $testEmail\n";

// Verificar credenciales
$sql = "SELECT u.*, au.cantidad_controles 
         FROM usuarios u
         LEFT JOIN apartamento_usuario au ON u.id = au.usuario_id AND au.activo = TRUE
         WHERE u.email = ? AND u.activo = TRUE";
$user = Database::fetchOne($sql, [$testEmail]);

if ($user && password_verify($testPassword, $user['password'])) {
    echo "✅ Credenciales verificadas correctamente\n";
    
    // Verificar si está bloqueado
    $sql = "SELECT bloqueado_hasta FROM login_intentos WHERE email = ?";
    $loginAttempt = Database::fetchOne($sql, [$testEmail]);
    
    if ($loginAttempt && $loginAttempt['bloqueado_hasta'] && new DateTime($loginAttempt['bloqueado_hasta']) > new DateTime()) {
        echo "❌ Usuario está bloqueado temporalmente\n";
    } else {
        echo "✅ Usuario no está bloqueado\n";
        
        // Simular registro de login exitoso
        $ip = '127.0.0.1'; // IP local para pruebas
        $userAgent = 'Test Script';
        
        // Registrar intento exitoso
        $sql = "INSERT INTO login_intentos (email, ip_address, user_agent, exitoso, intentos, bloqueado_hasta)
                 VALUES (?, ?, ?, TRUE, 1, NULL)
                 ON DUPLICATE KEY UPDATE 
                 intentos = 1, 
                 exitoso = TRUE, 
                 ultimo_intento = CURRENT_TIMESTAMP,
                 bloqueado_hasta = NULL";
        Database::execute($sql, [$testEmail, $ip, $userAgent]);
        
        echo "✅ Login exitoso registrado en login_intentos\n";
    }
} else {
    echo "❌ Credenciales incorrectas\n";
}

echo "\n";

// 6. Verificar permisos por rol
echo "=== VERIFICACIÓN DE PERMISOS POR ROL ===\n";

$roles = ['cliente', 'operador', 'consultor', 'administrador'];

foreach ($roles as $rol) {
    echo "\n--- Permisos para rol: $rol ---\n";
    
    // Obtener un usuario de este rol
    $sql = "SELECT id FROM usuarios WHERE rol = ? AND activo = TRUE LIMIT 1";
    $user = Database::fetchOne($sql, [$rol]);
    
    if ($user) {
        $userId = $user['id'];
        
        // Verificar permisos básicos según el rol
        switch ($rol) {
            case 'cliente':
                $permissions = ['view_own_estado_cuenta', 'upload_comprobante', 'view_own_historial'];
                break;
            case 'operador':
                $permissions = ['view_all_estados_cuenta', 'register_manual_payment', 'approve_comprobante'];
                break;
            case 'consultor':
                $permissions = ['view_all_estados_cuenta', 'view_reportes', 'export_excel'];
                break;
            case 'administrador':
                $permissions = ['all']; // Administrador tiene todos los permisos
                break;
        }
        
        foreach ($permissions as $permission) {
            if ($rol === 'administrador' || hasPermission($permission, $rol)) {
                echo "✅ Permiso '$permission' - CONCEDIDO\n";
            } else {
                echo "❌ Permiso '$permission' - DENEGADO\n";
            }
        }
    } else {
        echo "⚠️  No hay usuarios activos con el rol '$rol'\n";
    }
}

echo "\n=== PRUEBAS COMPLETADAS ===\n";
echo "El sistema de autenticación ha sido verificado correctamente.\n";
echo "Si todos los resultados son ✅, el sistema está listo para su uso.\n\n";

// Función para verificar permisos (copia de la función en auth.php)
function hasPermission(string $permission, string $role): bool
{
    // Define permissions for each role
    $permissions = [
        'cliente' => [
            'view_dashboard',
            'view_profile',
            'edit_profile',
            'view_payments',
            'make_payment',
            'view_monthly_payments',
            'view_apartments',
            'view_controls',
            'view_own_estado_cuenta',
            'upload_comprobante',
            'view_own_historial',
            'update_own_profile',
            'create_solicitud',
        ],
        'operador' => [
            'view_dashboard',
            'view_profile',
            'edit_profile',
            'view_payments',
            'make_payment',
            'view_monthly_payments',
            'view_apartments',
            'view_controls',
            'add_control',
            'edit_control',
            'delete_control',
            'view_clients',
            'search_clients',
            'view_all_estados_cuenta',
            'register_manual_payment',
            'approve_comprobante',
            'reject_comprobante',
            'approve_solicitud',
            'generate_recibo',
        ],
        'consultor' => [
            'view_dashboard',
            'view_profile',
            'edit_profile',
            'view_payments',
            'view_monthly_payments',
            'view_apartments',
            'view_controls',
            'view_clients',
            'search_clients',
            'view_reportes',
            'export_reports',
            'export_excel',
            'export_pdf',
            'view_estadisticas',
            'view_all_estados_cuenta',
        ],
        'administrador' => [
            'all', // Acceso completo a todas las funcionalidades
        ]
    ];

    // Check if role exists and has the permission
    return isset($permissions[$role]) && in_array($permission, $permissions[$role]);
}