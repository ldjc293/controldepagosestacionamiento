<?php
// Script para probar login con usuarios específicos
error_reporting(E_ALL);
ini_set('display_errors', 1);
echo "<h1>Test Login Usuarios Específicos</h1>";

// Iniciar sesión
session_start();

// Cargar configuración
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/app/models/Usuario.php';

// Usuarios para probar
$usuarios = [
    [
        'email' => 'operador@estacionamiento.com',
        'password' => 'operador123',
        'rol' => 'operador'
    ],
    [
        'email' => 'consultor@estacionamiento.com',
        'password' => 'consultor123',
        'rol' => 'consultor'
    ],
    [
        'email' => 'juan.perez@gmail.com',
        'password' => 'cliente123',
        'rol' => 'cliente'
    ],
    [
        'email' => 'admin@estacionamiento.com',
        'password' => 'Admin123!',
        'rol' => 'administrador'
    ]
];

echo "<table border='1' style='border-collapse: collapse; margin: 20px 0;'>";
echo "<tr><th>Email</th><th>Rol</th><th>Password</th><th>Resultado</th><th>Usuario</th></tr>";

foreach ($usuarios as $usuarioTest) {
    echo "<tr>";
    echo "<td>{$usuarioTest['email']}</td>";
    echo "<td>{$usuarioTest['rol']}</td>";
    echo "<td>{$usuarioTest['password']}</td>";

    try {
        $result = Usuario::verifyLogin($usuarioTest['email'], $usuarioTest['password']);

        if ($result['success']) {
            echo "<td style='color: green; font-weight: bold;'>✓ ÉXITO</td>";
            echo "<td>{$result['user']->nombre_completo}</td>";

            // Guardar sesión si es exitoso
            $_SESSION['user_id'] = $result['user']->id;
            $_SESSION['user_rol'] = $result['user']->rol;
            $_SESSION['user_name'] = $result['user']->nombre_completo;
        } else {
            echo "<td style='color: red;'>✗ " . htmlspecialchars($result['message']) . "</td>";
            echo "<td>-</td>";
        }
    } catch (Exception $e) {
        echo "<td style='color: red;'>✗ ERROR: " . htmlspecialchars($e->getMessage()) . "</td>";
        echo "<td>-</td>";
    }

    echo "</tr>";
}

echo "</table>";

// Si hay sesión activa, mostrar opciones de dashboard
if (isset($_SESSION['user_id'])) {
    echo "<h2>Dashboard Disponibles:</h2>";

    $rolesDashboards = [
        'administrador' => 'admin',
        'operador' => 'operador',
        'consultor' => 'consultor',
        'cliente' => 'cliente'
    ];

    foreach ($rolesDashboards as $rol => $dashboard) {
        if ($_SESSION['user_rol'] === $rol) {
            $url = url("{$dashboard}/dashboard");
            echo "<a href='$url' target='_blank' class='btn btn-primary' style='margin: 5px;'>Ir a Dashboard $dashboard</a><br>";
        }
    }

    echo "<h3>Información de Sesión:</h3>";
    echo "User ID: " . $_SESSION['user_id'] . "<br>";
    echo "User Rol: " . $_SESSION['user_rol'] . "<br>";
    echo "User Name: " . $_SESSION['user_name'] . "<br>";
    echo "Session ID: " . session_id() . "<br>";
}

// Formulario de login manual
echo "<h2>Formulario de Login Manual:</h2>";
echo "<form method='POST' style='margin: 20px 0;'>";
echo "<input type='hidden' name='csrf_token' value='" . generateCSRFToken() . "'>";
echo "Email: <input type='email' name='email' style='width: 250px; margin: 5px;'><br>";
echo "Password: <input type='password' name='password' style='width: 250px; margin: 5px;'><br>";
echo "<button type='submit' style='margin: 5px;'>Probar Login</button>";
echo "</form>";

if ($_POST) {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    echo "<h3>Resultado del Login Manual:</h3>";

    try {
        $result = Usuario::verifyLogin($email, $password);

        if ($result['success']) {
            echo "<span style='color: green; font-weight: bold;'>✓ Login exitoso!</span><br>";
            echo "Usuario: {$result['user']->nombre_completo}<br>";
            echo "Rol: {$result['user']->rol}<br>";

            $_SESSION['user_id'] = $result['user']->id;
            $_SESSION['user_rol'] = $result['user']->rol;
            $_SESSION['user_name'] = $result['user']->nombre_completo;

            echo "<br><a href=''>Recargar para ver sesión</a>";
        } else {
            echo "<span style='color: red; font-weight: bold;'>✗ Login fallido: {$result['message']}</span><br>";
        }
    } catch (Exception $e) {
        echo "<span style='color: red; font-weight: bold;'>✗ Error: {$e->getMessage()}</span><br>";
    }
}

echo "<hr>";
echo "<p><a href='" . url('auth/login') . "' target='_blank'>Ir a Login Normal</a></p>";
echo "<p><a href='test_login_debug.php' target='_blank'>Ir a Debug de Login Admin</a></p>";
?>