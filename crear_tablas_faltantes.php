<?php
/**
 * Script para crear las tablas faltantes en la base de datos
 * sin afectar los datos existentes
 */

// Include necessary files
require_once __DIR__ . '/config/database.php';

// Set headers for proper output
header('Content-Type: text/html; charset=utf-8');

echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Crear Tablas Faltantes</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
    <style>
        body { 
            padding: 20px; 
            background-color: #f8f9fa;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .success { color: #198754; font-weight: bold; }
        .error { color: #dc3545; font-weight: bold; }
        .info { color: #0d6efd; font-weight: bold; }
        .warning { color: #fd7e14; font-weight: bold; }
    </style>
</head>
<body>
    <div class='container'>
        <h1 class='mb-4'>Crear Tablas Faltantes</h1>";

try {
    // Verificar si la tabla configuracion_cron existe
    $tableExists = Database::fetchOne(
        "SELECT TABLE_NAME 
         FROM INFORMATION_SCHEMA.TABLES 
         WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'configuracion_cron'",
        [$_ENV['DB_NAME'] ?? 'estacionamiento_db']
    );
    
    if (!$tableExists) {
        echo "<div class='alert alert-warning'>
            <h4>Información:</h4>
            <p>La tabla 'configuracion_cron' no existe. Creándola ahora...</p>
        </div>";
        
        // Crear la tabla configuracion_cron
        $sql = "CREATE TABLE configuracion_cron (
            id INT PRIMARY KEY AUTO_INCREMENT,
            nombre_tarea VARCHAR(100) NOT NULL UNIQUE COMMENT 'Nombre único de la tarea (ej: generar_mensualidades)',
            descripcion TEXT NULL,
            script_path VARCHAR(255) NOT NULL COMMENT 'Ruta relativa al script PHP',
            activo BOOLEAN DEFAULT TRUE,
            frecuencia VARCHAR(100) NOT NULL COMMENT 'Ej: \"Diario\", \"Mensual\"',
            hora_ejecucion TIME NULL COMMENT 'Hora preferida de ejecución (HH:MM:SS)',
            dia_mes INT NULL COMMENT 'Día del mes para tareas mensuales (1-31)',
            ultima_ejecucion DATETIME NULL,
            ultimo_resultado ENUM('exitoso', 'fallido', 'pendiente') DEFAULT 'pendiente',
            ultimo_mensaje TEXT NULL,
            
            INDEX idx_nombre_tarea (nombre_tarea),
            INDEX idx_activo (activo)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Configuración y estado de tareas CRON'";
        
        Database::execute($sql);
        echo "<p class='success'>✓ Tabla 'configuracion_cron' creada correctamente</p>";
        
        // Insertar las tareas CRON iniciales
        $sql = "INSERT INTO configuracion_cron (nombre_tarea, descripcion, script_path, activo, frecuencia, hora_ejecucion, dia_mes) VALUES
        ('generar_mensualidades', 'Genera las mensualidades para todos los clientes activos el día 5 de cada mes.', 'cron/generar_mensualidades.php', TRUE, 'Mensual', '00:05:00', 5),
        ('verificar_bloqueos', 'Verifica clientes con 4+ meses de mora y bloquea sus controles.', 'cron/verificar_bloqueos.php', TRUE, 'Diario', '01:00:00', NULL),
        ('enviar_notificaciones', 'Envía notificaciones pendientes por email (alertas de mora, etc.).', 'cron/enviar_notificaciones.php', TRUE, 'Diario', '09:00:00', NULL),
        ('actualizar_tasa_bcv', 'Intenta actualizar la tasa de cambio desde el BCV automáticamente.', 'cron/actualizar_tasa_bcv.php', TRUE, 'Diario', '10:00:00', NULL),
        ('backup_database', 'Realiza un backup completo de la base de datos.', 'cron/backup_database.php', TRUE, 'Diario', '02:00:00', NULL)";
        
        Database::execute($sql);
        echo "<p class='success'>✓ Tareas CRON iniciales insertadas correctamente</p>";
    } else {
        echo "<div class='alert alert-info'>
            <h4>Información:</h4>
            <p>La tabla 'configuracion_cron' ya existe en la base de datos.</p>
        </div>";
    }
    
    // Verificar si existe la tabla login_intentos
    $tableExists = Database::fetchOne(
        "SELECT TABLE_NAME 
         FROM INFORMATION_SCHEMA.TABLES 
         WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'login_intentos'",
        [$_ENV['DB_NAME'] ?? 'estacionamiento_db']
    );
    
    if (!$tableExists) {
        echo "<div class='alert alert-warning'>
            <h4>Información:</h4>
            <p>La tabla 'login_intentos' no existe. Creándola ahora...</p>
        </div>";
        
        // Crear la tabla login_intentos
        $sql = "CREATE TABLE login_intentos (
            id INT PRIMARY KEY AUTO_INCREMENT,
            email VARCHAR(255) NOT NULL,
            ip_address VARCHAR(45) NOT NULL,
            user_agent TEXT NULL,
            fecha_hora TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            exitoso BOOLEAN DEFAULT FALSE,
            intentos INT DEFAULT 1,
            ultimo_intento TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            bloqueado_hasta DATETIME NULL,
            
            UNIQUE KEY unique_email (email),
            INDEX idx_email (email),
            INDEX idx_ip_address (ip_address),
            INDEX idx_fecha_hora (fecha_hora),
            INDEX idx_exitoso (exitoso)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Registro de intentos de login para seguridad'";
        
        Database::execute($sql);
        echo "<p class='success'>✓ Tabla 'login_intentos' creada correctamente</p>";
    } else {
        echo "<div class='alert alert-info'>
            <h4>Información:</h4>
            <p>La tabla 'login_intentos' ya existe en la base de datos.</p>
        </div>";
    }
    
    // Verificar si existe la tabla password_reset_tokens
    $tableExists = Database::fetchOne(
        "SELECT TABLE_NAME 
         FROM INFORMATION_SCHEMA.TABLES 
         WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'password_reset_tokens'",
        [$_ENV['DB_NAME'] ?? 'estacionamiento_db']
    );
    
    if (!$tableExists) {
        echo "<div class='alert alert-warning'>
            <h4>Información:</h4>
            <p>La tabla 'password_reset_tokens' no existe. Creándola ahora...</p>
        </div>";
        
        // Crear la tabla password_reset_tokens
        $sql = "CREATE TABLE password_reset_tokens (
            id INT PRIMARY KEY AUTO_INCREMENT,
            usuario_id INT NOT NULL,
            email VARCHAR(255) NOT NULL,
            
            -- Código de verificación
            codigo VARCHAR(6) NOT NULL COMMENT 'Código de 6 dígitos',
            fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            fecha_expiracion TIMESTAMP NULL COMMENT 'Expira en 15 minutos',
            
            -- Control de uso
            usado BOOLEAN DEFAULT FALSE COMMENT 'TRUE después de usar el código',
            intentos_validacion INT DEFAULT 0 COMMENT 'Máximo 3 intentos',
            
            -- Seguridad
            ip_address VARCHAR(45) NULL,
            user_agent TEXT NULL,
            
            FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
            INDEX idx_codigo (codigo),
            INDEX idx_email (email),
            INDEX idx_fecha_expiracion (fecha_expiracion),
            INDEX idx_usado (usado)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Tokens para recuperación de contraseña'";
        
        Database::execute($sql);
        echo "<p class='success'>✓ Tabla 'password_reset_tokens' creada correctamente</p>";
    } else {
        echo "<div class='alert alert-info'>
            <h4>Información:</h4>
            <p>La tabla 'password_reset_tokens' ya existe en la base de datos.</p>
        </div>";
    }
    
    // Verificar si existen las vistas
    $viewExists = Database::fetchOne(
        "SELECT TABLE_NAME 
         FROM INFORMATION_SCHEMA.VIEWS 
         WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'vista_morosidad'",
        [$_ENV['DB_NAME'] ?? 'estacionamiento_db']
    );
    
    if (!$viewExists) {
        echo "<div class='alert alert-warning'>
            <h4>Información:</h4>
            <p>La vista 'vista_morosidad' no existe. Creándola ahora...</p>
        </div>";
        
        try {
            // Crear la vista vista_morosidad
            $sql = "CREATE VIEW vista_morosidad AS
            SELECT
                u.id AS usuario_id,
                u.nombre_completo,
                u.email,
                u.telefono,
                CONCAT(a.bloque, '-', a.numero_apartamento) AS apartamento,
                au.cantidad_controles,
                COUNT(m.id) AS meses_pendientes,
                SUM(m.monto_usd) AS total_deuda_usd,
                SUM(m.monto_bs) AS total_deuda_bs,
                MIN(m.fecha_vencimiento) AS primer_mes_pendiente,
                MAX(m.fecha_vencimiento) AS ultimo_mes_pendiente,
                CASE
                    WHEN COUNT(m.id) >= 4 THEN 'Bloqueado'
                    WHEN COUNT(m.id) >= 3 THEN 'Alerta'
                    ELSE 'Normal'
                END AS estado_morosidad
            FROM usuarios u
            JOIN apartamento_usuario au ON au.usuario_id = u.id AND au.activo = TRUE
            JOIN apartamentos a ON a.id = au.apartamento_id
            JOIN mensualidades m ON m.apartamento_usuario_id = au.id AND m.estado IN ('pendiente', 'vencido')
            WHERE u.rol = 'cliente' AND u.activo = TRUE AND u.exonerado = FALSE
            GROUP BY u.id, u.nombre_completo, u.email, u.telefono, a.bloque, a.numero_apartamento, au.cantidad_controles";
            
            Database::execute($sql);
            echo "<p class='success'>✓ Vista 'vista_morosidad' creada correctamente</p>";
        } catch (Exception $e) {
            echo "<p class='error'>✗ Error al crear vista 'vista_morosidad': " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<div class='alert alert-info'>
            <h4>Información:</h4>
            <p>La vista 'vista_morosidad' ya existe en la base de datos.</p>
        </div>";
    }
    
    // Verificar si existe la vista vista_controles_vacios
    $viewExists = Database::fetchOne(
        "SELECT TABLE_NAME 
         FROM INFORMATION_SCHEMA.VIEWS 
         WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'vista_controles_vacios'",
        [$_ENV['DB_NAME'] ?? 'estacionamiento_db']
    );
    
    if (!$viewExists) {
        echo "<div class='alert alert-warning'>
            <h4>Información:</h4>
            <p>La vista 'vista_controles_vacios' no existe. Creándola ahora...</p>
        </div>";
        
        try {
            // Crear la vista vista_controles_vacios
            $sql = "CREATE VIEW vista_controles_vacios AS
            SELECT
                c.id,
                c.posicion_numero,
                c.receptor,
                c.numero_control_completo,
                c.estado
            FROM controles_estacionamiento c
            WHERE c.estado = 'vacio' AND c.apartamento_usuario_id IS NULL
            ORDER BY c.posicion_numero, c.receptor";
            
            Database::execute($sql);
            echo "<p class='success'>✓ Vista 'vista_controles_vacios' creada correctamente</p>";
        } catch (Exception $e) {
            echo "<p class='error'>✗ Error al crear vista 'vista_controles_vacios': " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<div class='alert alert-info'>
            <h4>Información:</h4>
            <p>La vista 'vista_controles_vacios' ya existe en la base de datos.</p>
        </div>";
    }
    
    echo "<div class='alert alert-success mt-4'>
        <h4>Proceso completado:</h4>
        <p>Se han creado todas las tablas y vistas faltantes. Ahora puedes intentar acceder al sistema.</p>
        <p><a href='verificar_usuarios.php' class='btn btn-primary me-2'>Verificar Usuarios</a>
        <a href='solucionar_acceso.php' class='btn btn-success'>Solucionar Acceso</a></p>
    </div>";
    
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>
        <h4>Error:</h4>
        <p>" . $e->getMessage() . "</p>
    </div>";
}

echo "</div>
</body>
</html>";