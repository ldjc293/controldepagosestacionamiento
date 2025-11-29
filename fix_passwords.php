<?php
/**
 * Script para actualizar usuarios con las credenciales correctas
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

$db = Database::getInstance();

echo "<h1>Actualizando usuarios...</h1>";

// Usuarios a crear/actualizar
$usuarios = [
    [
        'id' => 1,
        'nombre' => 'Administrador del Sistema',
        'email' => 'admin@estacionamiento.com',
        'password' => 'Admin123!',
        'rol' => 'administrador'
    ],
    [
        'id' => 2,
        'nombre' => 'Operador Principal',
        'email' => 'operador@estacionamiento.com',
        'password' => 'Operador123!',
        'rol' => 'operador'
    ],
    [
        'id' => 3,
        'nombre' => 'Consultor del Sistema',
        'email' => 'consultor@estacionamiento.com',
        'password' => 'Consultor123!',
        'rol' => 'consultor'
    ],
    [
        'id' => 4,
        'nombre' => 'María González',
        'email' => 'cliente1@email.com',
        'password' => 'Cliente123!',
        'rol' => 'cliente'
    ]
];

foreach ($usuarios as $user) {
    $passwordHash = password_hash($user['password'], PASSWORD_BCRYPT);

    // Verificar si existe
    $existe = Database::fetchOne(
        "SELECT id FROM usuarios WHERE id = ?",
        [$user['id']]
    );

    if ($existe) {
        // Actualizar
        $stmt = $db->prepare("
            UPDATE usuarios
            SET nombre_completo = ?,
                email = ?,
                password = ?,
                rol = ?,
                activo = 1,
                primer_acceso = 0,
                password_temporal = 0
            WHERE id = ?
        ");

        $stmt->execute([
            $user['nombre'],
            $user['email'],
            $passwordHash,
            $user['rol'],
            $user['id']
        ]);

        echo "✓ Actualizado: {$user['email']} (Password: {$user['password']})<br>";
    } else {
        // Insertar
        $stmt = $db->prepare("
            INSERT INTO usuarios (
                id, nombre_completo, email, password, rol,
                activo, primer_acceso, password_temporal, perfil_completo
            ) VALUES (?, ?, ?, ?, ?, 1, 0, 0, 1)
        ");

        $stmt->execute([
            $user['id'],
            $user['nombre'],
            $user['email'],
            $passwordHash,
            $user['rol']
        ]);

        echo "✓ Creado: {$user['email']} (Password: {$user['password']})<br>";
    }
}

echo "<hr>";
echo "<h2>Credenciales actualizadas:</h2>";
echo "<ul>";
foreach ($usuarios as $user) {
    echo "<li><strong>{$user['rol']}</strong>: {$user['email']} / {$user['password']}</li>";
}
echo "</ul>";

echo "<hr>";
echo "<a href='auth/login'>Ir al Login</a>";
