<?php
// Script de debug para login
error_reporting(E_ALL);
ini_set('display_errors', 1);
echo "<h1>Debug Login</h1>";

// Iniciar sesión
session_start();

echo "<h2>Session Status:</h2>";
echo "Session ID: " . session_id() . "<br>";
echo "Session status: " . session_status() . "<br>";

// Cargar configuración
try {
    echo "<h2>Loading config...</h2>";
    require_once __DIR__ . '/config/config.php';
    echo "✓ Config loaded<br>";
} catch (Exception $e) {
    echo "✗ Config error: " . $e->getMessage() . "<br>";
    exit;
}

// Cargar base de datos
try {
    echo "<h2>Loading database...</h2>";
    require_once __DIR__ . '/config/database.php';
    echo "✓ Database loaded<br>";
} catch (Exception $e) {
    echo "✗ Database error: " . $e->getMessage() . "<br>";
    exit;
}

// Cargar modelos
try {
    echo "<h2>Loading models...</h2>";
    require_once __DIR__ . '/app/models/Usuario.php';
    echo "✓ Usuario model loaded<br>";
} catch (Exception $e) {
    echo "✗ Model error: " . $e->getMessage() . "<br>";
    exit;
}

// Probar login con contraseña por defecto
echo "<h2>Testing login with default password...</h2>";

$testEmail = 'admin@estacionamiento.com';
$testPasswords = ['Admin123!', 'admin123', '123456', 'admin', 'password'];

foreach ($testPasswords as $testPassword) {
    echo "<h3>Trying password: '$testPassword'</h3>";

    try {
        $result = Usuario::verifyLogin($testEmail, $testPassword);

        if ($result['success']) {
            echo "<strong style='color: green;'>✓ LOGIN SUCCESSFUL!</strong><br>";
            echo "User: " . $result['user']->nombre_completo . "<br>";
            echo "Rol: " . $result['user']->rol . "<br>";

            // Crear sesión
            $_SESSION['user_id'] = $result['user']->id;
            $_SESSION['user_rol'] = $result['user']->rol;
            $_SESSION['user_name'] = $result['user']->nombre_completo;

            echo "✓ Session created<br>";

            break;
        } else {
            echo "<span style='color: red;'>✗ Failed: " . $result['message'] . "</span><br>";
        }
    } catch (Exception $e) {
        echo "<span style='color: red;'>✗ Exception: " . $e->getMessage() . "</span><br>";
    }
}

// Si el login fue exitoso, probar redirección
if (isset($_SESSION['user_id'])) {
    echo "<h2>Testing redirect...</h2>";
    $rol = $_SESSION['user_rol'];
    $dashboardRol = $rol === 'administrador' ? 'admin' : $rol;
    $redirectUrl = url("{$dashboardRol}/dashboard");

    echo "Rol: $rol<br>";
    echo "Dashboard: $dashboardRol<br>";
    echo "Redirect URL: <a href='$redirectUrl' target='_blank'>$redirectUrl</a><br>";

    echo "<h3>Manual redirect test:</h3>";
    echo "<a href='direct_dashboard.php?rol=$dashboardRol' class='btn btn-success'>Go to Direct Dashboard</a><br>";
    echo "<a href='$redirectUrl' class='btn btn-primary'>Go to Real Dashboard</a><br>";
} else {
    echo "<h2>No successful login found</h2>";
    echo "<p>Trying to create a new admin user with password 'Admin123!'</p>";

    try {
        $userData = [
            'nombre_completo' => 'Administrador del Sistema',
            'email' => 'admin@estacionamiento.com',
            'password' => 'Admin123!',
            'rol' => 'administrador',
            'activo' => true
        ];

        $newUserId = Usuario::create($userData);
        echo "<span style='color: green;'>✓ Admin user created/updated with ID: $newUserId</span><br>";
        echo "<p>Try logging in with: admin@estacionamiento.com / Admin123!</p>";
    } catch (Exception $e) {
        echo "<span style='color: red;'>✗ Error creating user: " . $e->getMessage() . "</span><br>";
    }
}

echo "<hr>";
echo "<h2>Test Login Form:</h2>";
echo "<form method='POST'>";
echo "<input type='hidden' name='csrf_token' value='" . generateCSRFToken() . "'>";
echo "Email: <input type='email' name='email' value='admin@estacionamiento.com' style='width: 200px;'><br><br>";
echo "Password: <input type='password' name='password' value='Admin123!' style='width: 200px;'><br><br>";
echo "<button type='submit'>Test Login</button>";
echo "</form>";

if ($_POST) {
    echo "<h2>POST Result:</h2>";
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    try {
        $result = Usuario::verifyLogin($email, $password);

        if ($result['success']) {
            echo "<span style='color: green;'>✓ Login successful!</span><br>";
            $_SESSION['user_id'] = $result['user']->id;
            $_SESSION['user_rol'] = $result['user']->rol;
            echo "<a href=''>Refresh to see session</a>";
        } else {
            echo "<span style='color: red;'>✗ Login failed: " . $result['message'] . "</span><br>";
        }
    } catch (Exception $e) {
        echo "<span style='color: red;'>✗ Exception: " . $e->getMessage() . "</span><br>";
    }
}
?>