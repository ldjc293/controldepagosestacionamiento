<?php
/**
 * Script de Diagnóstico y Solución para el Sistema
 * 
 * Este script verifica el estado del sistema y proporciona soluciones
 * para los problemas más comunes.
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

header('Content-Type: text/html; charset=utf-8');

/**
 * Función para ejecutar la corrección de la tabla login_intentos.
 */
function fixLoginIntentosTable() {
    $output = "<h4>Ejecutando corrección de 'login_intentos'...</h4>";
    try {
        $pdo = Database::getInstance();
        
        // 1. Verificar si la tabla existe
        $result = $pdo->query("SHOW TABLES LIKE 'login_intentos'")->fetchAll();
        
        if (count($result) === 0) {
            $output .= '<p class="text-warning">La tabla login_intentos no existe. Creándola...</p>';
            $sql = "
            CREATE TABLE login_intentos (
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
            $pdo->exec($sql);
            $output .= '<p class="text-success">✅ Tabla login_intentos creada correctamente.</p>';
        } else {
            $output .= '<p class="text-info">La tabla login_intentos ya existe. Verificando estructura...</p>';
            
            $columns = $pdo->query("SHOW COLUMNS FROM login_intentos")->fetchAll(PDO::FETCH_ASSOC);
            $columnNames = array_column($columns, 'Field');
            
            // 2. Definir todas las columnas necesarias y sus definiciones
            $requiredColumns = [
                'intentos' => 'INT DEFAULT 1',
                'ultimo_intento' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
                'bloqueado_hasta' => 'DATETIME NULL',
                'ip_address' => 'VARCHAR(45) NOT NULL',
                'user_agent' => 'TEXT NULL',
                'exitoso' => 'BOOLEAN DEFAULT FALSE'
            ];

            // 3. Verificar y añadir cada columna faltante
            foreach ($requiredColumns as $colName => $colDef) {
                if (!in_array($colName, $columnNames)) {
                    $output .= "<p class='text-warning'>La columna '{$colName}' no existe. Añadiéndola...</p>";
                    try {
                        // Usamos una cláusula AFTER simple si es posible, si no, la añadimos al final.
                        $afterClause = in_array('fecha_hora', $columnNames) ? "AFTER fecha_hora" : "";
                        $pdo->exec("ALTER TABLE login_intentos ADD COLUMN {$colName} {$colDef} {$afterClause}");
                        $output .= "<p class='text-success'>✅ Columna '{$colName}' añadida.</p>";
                        // Añadimos la columna recién creada a la lista para las siguientes iteraciones
                        $columnNames[] = $colName;
                    } catch (Exception $e) {
                        $output .= '<p class="text-danger">❌ Error al añadir la columna ' . $colName . ': ' . htmlspecialchars($e->getMessage()) . '</p>';
                    }
                }
            }

            // 4. Verificar y añadir índice único
            $indexes = $pdo->query("SHOW INDEX FROM login_intentos WHERE Key_name = 'unique_email'")->fetchAll();
            if (count($indexes) === 0) {
                $output .= "<p class='text-warning'>El índice 'unique_email' no existe. Añadiéndolo...</p>";
                $pdo->exec("ALTER TABLE login_intentos ADD UNIQUE KEY unique_email (email)");
                $output .= "<p class='text-success'>✅ Índice 'unique_email' añadido.</p>";
            }
        }
        $output .= '<p class="text-success"><strong>Corrección finalizada. La tabla ahora debería tener la estructura correcta.</strong></p>';
    } catch (Exception $e) {
        $output .= '<p class="text-danger">❌ Error durante la corrección: ' . htmlspecialchars($e->getMessage()) . '</p>';
    }
    return $output;
}

// Verificar si se está solicitando una acción
$action = $_GET['action'] ?? null;
if ($action === 'fix_login_intentos') {
    echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><title>Corrigiendo...</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head><body><div class="container mt-4">';
    echo fixLoginIntentosTable();
    echo '<a href="diagnosticar_y_solucionar.php" class="btn btn-primary mt-3">Volver al Diagnóstico</a>';
    echo '</div></body></html>';
    exit;
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico y Solución del Sistema</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
<?php

echo "========================================\n";
echo "DIAGNÓSTICO DEL SISTEMA\n";
echo "========================================\n\n";

// Función para verificar estado
function checkStatus($testName, $condition, $details = '', $solution = '') {
    $statusIcon = $condition ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-x-circle-fill text-danger"></i>';
    echo "<li class='list-group-item d-flex justify-content-between align-items-center'>";
    echo "<div>$statusIcon $testName " . ($details ? "<small class='text-muted'>($details)</small>" : "") . "</div>";
    if (!$condition && $solution) {
        echo $solution;
    }
    echo "</li>";
    return $condition;
}

// 1. Verificar conexión a base de datos
echo "1. CONEXIÓN A BASE DE DATOS\n";
echo "========================================\n";
try {
    echo "<ul class='list-group mb-4'>";
    $db = Database::getInstance();
    checkStatus("Conexión a base de datos", true, 'Conexión PDO exitosa');
    
    // Verificar tablas principales
    $tables = ['usuarios', 'apartamentos', 'controles', 'mensualidades', 'pagos', 'login_intentos'];
    foreach ($tables as $table) {
        $result = Database::fetchAll("SHOW TABLES LIKE '$table'");
        $exists = count($result) > 0;
        $solution = !$exists ? '<a href="database/init.php" class="btn btn-sm btn-info">Ejecutar Init DB</a>' : '';
        checkStatus("Tabla '$table'", $exists, '', $solution);
    }
    echo "</ul>";
    
} catch (Exception $e) {
    checkStatus("Conexión a base de datos", false, $e->getMessage());
}

// 2. Verificar tabla login_intentos
echo "\n2. TABLA LOGIN_INTENTOS\n";
echo "========================================\n";
$loginIntentosOk = true;
try {
    echo "<ul class='list-group mb-4'>";
    $db = Database::getInstance();
    
    // Verificar si la tabla existe
    $result = Database::fetchAll("SHOW TABLES LIKE 'login_intentos'");
    $tableExists = count($result) > 0;
    
    if (!checkStatus("Tabla 'login_intentos' existe", $tableExists, '', '<a href="?action=fix_login_intentos" class="btn btn-sm btn-warning">Corregir Ahora</a>')) {
        $loginIntentosOk = false;
    } else {
        $columns = Database::fetchAll("SHOW COLUMNS FROM login_intentos");
        $columnNames = array_column($columns, 'Field');
        
        $requiredColumns = ['id', 'email', 'ip_address', 'user_agent', 'fecha_hora', 'exitoso', 'intentos', 'ultimo_intento', 'bloqueado_hasta'];
        foreach ($requiredColumns as $col) {
            if (!in_array($col, $columnNames)) {
                checkStatus("Columna '$col'", false, 'Faltante');
                $loginIntentosOk = false;
            }
        }

        $indexes = Database::fetchAll("SHOW INDEX FROM login_intentos WHERE Key_name = 'unique_email'");
        if (count($indexes) === 0) {
            checkStatus("Índice 'unique_email'", false, 'Faltante');
            $loginIntentosOk = false;
        }

        if (!$loginIntentosOk) {
            echo '<li class="list-group-item list-group-item-warning"><a href="?action=fix_login_intentos" class="btn btn-warning">Corregir Estructura de login_intentos</a></li>';
        } else {
            checkStatus("Estructura de 'login_intentos'", true, 'Todas las columnas e índices requeridos existen.');
        }
    }
    echo "</ul>";
    
} catch (Exception $e) {
    checkStatus("Verificación de login_intentos", false, $e->getMessage());
    $loginIntentosOk = false;
}

// 3. Verificar configuración del servidor web
echo "\n3. CONFIGURACIÓN DEL SERVIDOR WEB\n";
echo "========================================\n";
echo "<ul class='list-group mb-4'>";
checkStatus("Software del servidor", isset($_SERVER['SERVER_SOFTWARE']), $_SERVER['SERVER_SOFTWARE'] ?? 'N/A');
checkStatus("Document Root", isset($_SERVER['DOCUMENT_ROOT']), $_SERVER['DOCUMENT_ROOT'] ?? 'N/A');
checkStatus("Mod Rewrite", function_exists('apache_get_modules') && in_array('mod_rewrite', apache_get_modules()), 'Necesario para URLs amigables');
echo "</ul>";

// 4. Verificar archivos críticos
echo "\n4. ARCHIVOS CRÍTICOS\n";
echo "========================================\n";
echo "<ul class='list-group mb-4'>";
$criticalFiles = [
    'public/index.php' => 'Punto de entrada principal',
    'config/config.php' => 'Configuración principal',
    'config/database.php' => 'Conexión a base de datos',
    '.htaccess' => 'Configuración de Apache',
];

foreach ($criticalFiles as $file => $description) {
    $exists = file_exists($file);
    checkStatus($description, $exists, "Ruta: $file");
}
echo "</ul>";

// 5. Verificar permisos de directorios
echo "\n5. PERMISOS DE DIRECTORIOS\n";
echo "========================================\n";
echo "<ul class='list-group mb-4'>";
$directories = [
    'logs/' => 'Directorio de logs',
    'public/uploads/comprobantes/' => 'Directorio para comprobantes',
    'public/uploads/recibos/' => 'Directorio para recibos'
];

foreach ($directories as $dir => $description) {
    $isWritable = is_writable($dir);
    checkStatus($description, $isWritable, "Permisos de escritura");
}
echo "</ul>";

?>
</div>
</body>
</html>