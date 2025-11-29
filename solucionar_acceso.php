<?php
/**
 * Script para solucionar problemas de acceso de usuarios
 * 
 * Este script:
 * 1. Verifica si los usuarios existen en la base de datos
 * 2. Si no existen, los crea con las credenciales predeterminadas
 * 3. Si existen, actualiza sus contraseñas a valores conocidos
 * 4. Resetea intentos fallidos y desbloquea cuentas
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
    <title>Solucionar Acceso de Usuarios</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
    <style>
        body { 
            padding: 20px; 
            background-color: #f8f9fa;
        }
        .container {
            max-width: 800px;
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
        .success { color: #198754; font-weight: bold; }
        .error { color: #dc3545; font-weight: bold; }
        .info { color: #0d6efd; font-weight: bold; }
    </style>
</head>
<body>
    <div class='container'>
        <h1 class='mb-4'>Solucionar Acceso de Usuarios</h1>";

try {
    // Usuarios a crear/actualizar con sus credenciales
    $usuarios = [
        [
            'id' => 1,
            'nombre_completo' => 'Ing. Miguel Sánchez',
            'email' => 'admin@estacionamiento.local',
            'password' => 'admin123',
            'rol' => 'administrador'
        ],
        [
            'id' => 2,
            'nombre_completo' => 'Carmen Méndez',
            'email' => 'operador@estacionamiento.local',
            'password' => 'operador123',
            'rol' => 'operador'
        ],
        [
            'id' => 3,
            'nombre_completo' => 'Sr. Alberto Rivas',
            'email' => 'consultor@estacionamiento.local',
            'password' => 'consultor123',
            'rol' => 'consultor'
        ],
        [
            'id' => 4,
            'nombre_completo' => 'María González',
            'email' => 'maria.gonzalez@gmail.com',
            'password' => 'cliente123',
            'rol' => 'cliente'
        ]
    ];

    echo "<div class='alert alert-info'>
        <h4>Información:</h4>
        <p>Este script va a verificar y crear/actualizar los siguientes usuarios:</p>
        <ul>";
        
    foreach ($usuarios as $usuario) {
        echo "<li><strong>{$usuario['nombre_completo']}</strong> ({$usuario['rol']})<br>
              Email: {$usuario['email']}<br>
              Contraseña: {$usuario['password']}</li>";
    }
        
    echo "</ul>
        <p>Además, se resetearán los intentos fallidos y se desbloquearán las cuentas.</p>
    </div>";

    // Procesar cada usuario
    foreach ($usuarios as $usuario) {
        echo "<div class='user-card'>
            <h5>Procesando: {$usuario['nombre_completo']} ({$usuario['rol']})</h5>";
        
        // Verificar si el usuario existe
        $existe = Database::fetchOne(
            "SELECT * FROM usuarios WHERE id = ? OR email = ?",
            [$usuario['id'], $usuario['email']]
        );
        
        $passwordHash = password_hash($usuario['password'], PASSWORD_BCRYPT);
        
        if ($existe) {
            // Actualizar usuario existente
            $sql = "UPDATE usuarios SET
                    nombre_completo = ?,
                    email = ?,
                    password = ?,
                    rol = ?,
                    activo = 1,
                    intentos_fallidos = 0,
                    bloqueado_hasta = NULL,
                    primer_acceso = 0,
                    password_temporal = 0,
                    perfil_completo = 1
                    WHERE id = ? OR email = ?";
            
            $params = [
                $usuario['nombre_completo'],
                $usuario['email'],
                $passwordHash,
                $usuario['rol'],
                $usuario['id'],
                $usuario['email']
            ];
            
            $result = Database::execute($sql, $params);
            
            if ($result > 0) {
                echo "<p class='success'>✓ Usuario actualizado correctamente</p>";
            } else {
                echo "<p class='info'>ℹ El usuario ya tenía los datos actualizados</p>";
            }
        } else {
            // Crear nuevo usuario
            $sql = "INSERT INTO usuarios (
                    id, nombre_completo, email, password, rol,
                    activo, intentos_fallidos, bloqueado_hasta,
                    primer_acceso, password_temporal, perfil_completo
                    ) VALUES (?, ?, ?, ?, ?, 1, 0, NULL, 0, 0, 1)";
            
            $params = [
                $usuario['id'],
                $usuario['nombre_completo'],
                $usuario['email'],
                $passwordHash,
                $usuario['rol']
            ];
            
            try {
                $userId = Database::insert($sql, $params);
                echo "<p class='success'>✓ Usuario creado correctamente con ID: {$userId}</p>";
            } catch (Exception $e) {
                // Si hay un error de clave duplicada, intentar actualizar
                if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                    $sql = "UPDATE usuarios SET
                            nombre_completo = ?,
                            email = ?,
                            password = ?,
                            rol = ?,
                            activo = 1,
                            intentos_fallidos = 0,
                            bloqueado_hasta = NULL,
                            primer_acceso = 0,
                            password_temporal = 0,
                            perfil_completo = 1
                            WHERE id = ?";
                    
                    $params = [
                        $usuario['nombre_completo'],
                        $usuario['email'],
                        $passwordHash,
                        $usuario['rol'],
                        $usuario['id']
                    ];
                    
                    $result = Database::execute($sql, $params);
                    
                    if ($result > 0) {
                        echo "<p class='success'>✓ Usuario actualizado correctamente</p>";
                    } else {
                        echo "<p class='error'>✗ No se pudo actualizar el usuario</p>";
                    }
                } else {
                    echo "<p class='error'>✗ Error al crear usuario: " . $e->getMessage() . "</p>";
                }
            }
        }
        
        // Limpiar intentos fallidos de login por email
        $sql = "DELETE FROM login_intentos WHERE email = ?";
        Database::execute($sql, [$usuario['email']]);
        
        echo "</div>";
    }
    
    // Mostrar resumen
    echo "<div class='alert alert-success mt-4'>
        <h4>Resumen:</h4>
        <p>Los usuarios han sido procesados correctamente. Ahora puedes intentar iniciar sesión con las siguientes credenciales:</p>
        <table class='table table-striped'>
            <thead>
                <tr>
                    <th>Rol</th>
                    <th>Email</th>
                    <th>Contraseña</th>
                </tr>
            </thead>
            <tbody>";
            
    foreach ($usuarios as $usuario) {
        echo "<tr>
                <td>{$usuario['rol']}</td>
                <td>{$usuario['email']}</td>
                <td>{$usuario['password']}</td>
            </tr>";
    }
    
    echo "</tbody>
        </table>
        <p class='mt-3'><a href='auth/login' class='btn btn-primary'>Ir al Login</a></p>
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