<?php
/**
 * Script para verificar el estado actual de los usuarios en la base de datos
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
    <title>Verificar Estado de Usuarios</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
    <style>
        body { 
            padding: 20px; 
            background-color: #f8f9fa;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .user-card {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
        }
        .status-active { color: #198754; font-weight: bold; }
        .status-inactive { color: #dc3545; font-weight: bold; }
        .status-blocked { color: #fd7e14; font-weight: bold; }
        .status-pending { color: #0d6efd; font-weight: bold; }
    </style>
</head>
<body>
    <div class='container'>
        <h1 class='mb-4'>Verificar Estado de Usuarios</h1>";

try {
    // Obtener todos los usuarios
    $usuarios = Database::fetchAll("SELECT * FROM usuarios ORDER BY id");
    
    if (empty($usuarios)) {
        echo "<div class='alert alert-warning'>
            <h4>Advertencia:</h4>
            <p>No se encontraron usuarios en la base de datos.</p>
            <p><a href='solucionar_acceso.php' class='btn btn-primary'>Crear Usuarios Predeterminados</a></p>
        </div>";
    } else {
        echo "<div class='alert alert-info'>
            <h4>Información:</h4>
            <p>Se encontraron " . count($usuarios) . " usuarios en la base de datos.</p>
        </div>";
        
        // Mostrar cada usuario
        foreach ($usuarios as $usuario) {
            $statusClass = 'status-active';
            $statusText = 'Activo';
            
            if (!$usuario['activo']) {
                $statusClass = 'status-inactive';
                $statusText = 'Inactivo';
            } elseif ($usuario['bloqueado_hasta'] && strtotime($usuario['bloqueado_hasta']) > time()) {
                $statusClass = 'status-blocked';
                $statusText = 'Bloqueado hasta ' . date('d/m/Y H:i', strtotime($usuario['bloqueado_hasta']));
            } elseif ($usuario['primer_acceso'] || $usuario['password_temporal']) {
                $statusClass = 'status-pending';
                $statusText = 'Primer Acceso / Contraseña Temporal';
            }
            
            echo "<div class='user-card'>
                <h5>{$usuario['nombre_completo']} (ID: {$usuario['id']})</h5>
                <div class='row'>
                    <div class='col-md-6'>
                        <p><strong>Email:</strong> {$usuario['email']}</p>
                        <p><strong>Rol:</strong> {$usuario['rol']}</p>
                        <p><strong>Estado:</strong> <span class='{$statusClass}'>{$statusText}</span></p>
                    </div>
                    <div class='col-md-6'>
                        <p><strong>Intentos Fallidos:</strong> {$usuario['intentos_fallidos']}</p>
                        <p><strong>Primer Acceso:</strong> " . ($usuario['primer_acceso'] ? 'Sí' : 'No') . "</p>
                        <p><strong>Contraseña Temporal:</strong> " . ($usuario['password_temporal'] ? 'Sí' : 'No') . "</p>
                        <p><strong>Perfil Completo:</strong> " . ($usuario['perfil_completo'] ? 'Sí' : 'No') . "</p>
                    </div>
                </div>";
            
            // Verificar si hay intentos de login fallidos para este email
            $intentosLogin = Database::fetchOne(
                "SELECT * FROM login_intentos WHERE email = ?",
                [$usuario['email']]
            );
            
            if ($intentosLogin) {
                echo "<div class='alert alert-warning mt-2'>
                    <strong>Advertencia de seguridad:</strong> Hay registros de intentos fallidos para este email.<br>
                    <strong>Intentos:</strong> {$intentosLogin['intentos']}<br>
                    <strong>Último intento:</strong> " . date('d/m/Y H:i:s', strtotime($intentosLogin['ultimo_intento'])) . "<br>";
                
                if ($intentosLogin['bloqueado_hasta'] && strtotime($intentosLogin['bloqueado_hasta']) > time()) {
                    echo "<strong>Bloqueado hasta:</strong> " . date('d/m/Y H:i:s', strtotime($intentosLogin['bloqueado_hasta']));
                }
                
                echo "</div>";
            }
            
            echo "</div>";
        }
    }
    
    // Verificar si hay usuarios bloqueados por rate limiting
    $bloqueadosRateLimit = Database::fetchAll(
        "SELECT * FROM login_intentos WHERE bloqueado_hasta > NOW() ORDER BY bloqueado_hasta"
    );
    
    if (!empty($bloqueadosRateLimit)) {
        echo "<div class='alert alert-danger mt-4'>
            <h4>Usuarios Bloqueados por Rate Limiting:</h4>
            <table class='table table-striped'>
                <thead>
                    <tr>
                        <th>Email</th>
                        <th>Intentos</th>
                        <th>Bloqueado Hasta</th>
                    </tr>
                </thead>
                <tbody>";
                
        foreach ($bloqueadosRateLimit as $bloqueado) {
            echo "<tr>
                    <td>{$bloqueado['email']}</td>
                    <td>{$bloqueado['intentos']}</td>
                    <td>" . date('d/m/Y H:i:s', strtotime($bloqueado['bloqueado_hasta'])) . "</td>
                </tr>";
        }
        
        echo "</tbody>
            </table>
        </div>";
    }
    
    // Botones de acción
    echo "<div class='mt-4'>
        <a href='solucionar_acceso.php' class='btn btn-primary me-2'>Restablecer Credenciales</a>
        <a href='auth/login' class='btn btn-success'>Ir al Login</a>
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