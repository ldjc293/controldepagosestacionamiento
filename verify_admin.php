<?php
/**
 * Verificar estado del usuario admin
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

echo "<h1>Verificación de Usuario Admin</h1>";

try {
    // Buscar admin por email
    $admin = Database::fetchOne(
        "SELECT * FROM usuarios WHERE email = ?",
        ['admin@estacionamiento.com']
    );

    if ($admin) {
        echo "<h2 style='color: green;'>✓ Usuario encontrado</h2>";
        echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
        echo "<tr><th>Campo</th><th>Valor</th></tr>";
        echo "<tr><td>ID</td><td>{$admin['id']}</td></tr>";
        echo "<tr><td>Nombre</td><td>{$admin['nombre_completo']}</td></tr>";
        echo "<tr><td>Email</td><td>{$admin['email']}</td></tr>";
        echo "<tr><td>Rol</td><td>{$admin['rol']}</td></tr>";
        echo "<tr><td>Activo</td><td>" . ($admin['activo'] ? 'Sí' : 'No') . "</td></tr>";
        echo "<tr><td>Primer Acceso</td><td>" . ($admin['primer_acceso'] ? 'Sí' : 'No') . "</td></tr>";
        echo "<tr><td>Password Temporal</td><td>" . ($admin['password_temporal'] ? 'Sí' : 'No') . "</td></tr>";
        echo "<tr><td>Hash Password</td><td>" . substr($admin['password'], 0, 30) . "...</td></tr>";
        echo "</table>";

        // Probar passwords
        echo "<h2>Prueba de Passwords</h2>";
        $testPasswords = ['Admin123!', 'password123', 'admin123'];

        foreach ($testPasswords as $pwd) {
            $match = password_verify($pwd, $admin['password']);
            $color = $match ? 'green' : 'red';
            $icon = $match ? '✓' : '✗';
            echo "<p style='color: $color;'><strong>$icon Password '$pwd':</strong> " . ($match ? 'CORRECTO' : 'Incorrecto') . "</p>";
        }

        // Generar hash nuevo si es necesario
        echo "<h2>Generar Nuevo Hash</h2>";
        $newHash = password_hash('Admin123!', PASSWORD_BCRYPT);
        echo "<p>Nuevo hash para 'Admin123!': <code>$newHash</code></p>";

        echo "<h3>Actualizar con este hash:</h3>";
        echo "<pre>";
        echo "UPDATE usuarios SET password = '$newHash' WHERE id = 1;";
        echo "</pre>";

    } else {
        echo "<h2 style='color: red;'>✗ Usuario NO encontrado</h2>";
        echo "<p>No existe usuario con email: admin@estacionamiento.com</p>";

        // Mostrar todos los usuarios
        echo "<h3>Usuarios existentes:</h3>";
        $allUsers = Database::fetchAll("SELECT id, nombre_completo, email, rol FROM usuarios ORDER BY id");

        if ($allUsers) {
            echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
            echo "<tr><th>ID</th><th>Nombre</th><th>Email</th><th>Rol</th></tr>";
            foreach ($allUsers as $user) {
                echo "<tr>";
                echo "<td>{$user['id']}</td>";
                echo "<td>{$user['nombre_completo']}</td>";
                echo "<td>{$user['email']}</td>";
                echo "<td>{$user['rol']}</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    }

} catch (Exception $e) {
    echo "<h2 style='color: red;'>Error</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='fix_passwords.php'>Ejecutar fix_passwords.php</a></p>";
echo "<p><a href='auth/login'>Ir al Login</a></p>";
