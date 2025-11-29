<?php
// Activar todos los errores
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('html_errors', 1);

echo "<!DOCTYPE html>";
echo "<html><head><title>Debug Directo</title>";
echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>";
echo "</head><body>";

echo "<div class='container mt-4'>";
echo "<h1>Debug Directo - Estado de Cuenta</h1>";

// Simular el proceso exacto del controlador
try {
    echo "<h3>Paso 1: Cargar configuración</h3>";
    require_once __DIR__ . '/config/config.php';
    require_once __DIR__ . '/config/database.php';
    echo "<p class='text-success'>✓ Config y Database cargadas</p>";

    echo "<h3>Paso 2: Iniciar sesión</h3>";
    session_start();
    echo "<p class='text-success'>✓ Sesión iniciada</p>";

    echo "<h3>Paso 3: Buscar cliente y establecer sesión</h3>";
    $usuarios = Database::fetchAll("SELECT id, nombre_completo, email, rol FROM usuarios WHERE rol = 'cliente' AND activo = 1 LIMIT 1");

    if ($usuarios) {
        $usuario = (object)$usuarios[0];
        $_SESSION['user_id'] = $usuario->id;
        $_SESSION['user_rol'] = $usuario->rol;
        $_SESSION['user_name'] = $usuario->nombre_completo;

        echo "<p class='text-success'>✓ Sesión de cliente establecida: {$usuario->nombre_completo}</p>";

        echo "<h3>Paso 4: Verificar autenticación</h3>";
        if (isset($_SESSION['user_id']) && $_SESSION['user_rol'] === 'cliente') {
            echo "<p class='text-success'>✓ Autenticación válida</p>";

            echo "<h3>Paso 5: Cargar modelos</h3>";
            require_once __DIR__ . '/app/models/Usuario.php';
            require_once __DIR__ . '/app/models/Mensualidad.php';
            require_once __DIR__ . '/app/models/Pago.php';
            echo "<p class='text-success'>✓ Modelos cargados</p>";

            echo "<h3>Paso 6: Obtener datos del usuario</h3>";
            $usuarioObj = Usuario::findById($_SESSION['user_id']);
            if ($usuarioObj && $usuarioObj->activo) {
                echo "<p class='text-success'>✓ Usuario válido y activo</p>";

                echo "<h3>Paso 7: Obtener mensualidades</h3>";
                $mensualidades = Mensualidad::getAllByUsuario($usuarioObj->id);
                echo "<p class='text-success'>✓ Mensualidades: " . count($mensualidades) . "</p>";

                echo "<h3>Paso 8: Obtener pagos</h3>";
                $pagos = Pago::getByUsuario($usuarioObj->id);
                echo "<p class='text-success'>✓ Pagos: " . count($pagos) . "</p>";

                echo "<h3>Paso 9: Calcular deuda</h3>";
                $deudaInfo = Mensualidad::calcularDeudaTotal($usuarioObj->id);
                echo "<p class='text-success'>✓ Deuda calculada</p>";

                echo "<h3>Paso 10: Mostrar vista</h3>";
                echo "<div class='alert alert-info'>";
                echo "<h4>Información de Deuda:</h4>";
                echo "<pre>" . print_r($deudaInfo, true) . "</pre>";
                echo "</div>";

                echo "<p><a href='cliente/estado-cuenta' class='btn btn-primary'>Ir a Estado de Cuenta Real</a></p>";

            } else {
                echo "<p class='text-danger'>✗ Usuario inválido o inactivo</p>";
            }
        } else {
            echo "<p class='text-danger'>✗ Autenticación fallida</p>";
        }
    } else {
        echo "<p class='text-warning'>No hay clientes en la BD</p>";
    }

} catch (Error $e) {
    echo "<div class='alert alert-danger'>";
    echo "<h4>Error PHP:</h4>";
    echo "<p><strong>" . get_class($e) . "</strong>: " . $e->getMessage() . "</p>";
    echo "<p><strong>Archivo:</strong> " . $e->getFile() . ":" . $e->getLine() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    echo "</div>";
} catch (Exception $e) {
    echo "<div class='alert alert-warning'>";
    echo "<h4>Excepción:</h4>";
    echo "<p><strong>" . get_class($e) . "</strong>: " . $e->getMessage() . "</p>";
    echo "<p><strong>Archivo:</strong> " . $e->getFile() . ":" . $e->getLine() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    echo "</div>";
}

echo "</div>";
echo "</body></html>";
?>