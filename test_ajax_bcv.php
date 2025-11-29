<?php
/**
 * Test de actualización BCV via AJAX
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

// Simular sesión de administrador (la sesión ya fue iniciada en config.php)
$_SESSION['user_id'] = 1;
$_SESSION['user_rol'] = 'administrador';

// Simular petición AJAX
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';

// Generar token CSRF
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

// Simular el body JSON
$json_data = json_encode([
    'csrf_token' => $_SESSION['csrf_token']
]);

// Crear stream simulado para php://input
stream_wrapper_unregister("php");
stream_wrapper_register("php", "MockPhpStream");

class MockPhpStream {
    public $position = 0;
    public $data;

    public function stream_open($path, $mode, $options, &$opened_path) {
        global $json_data;
        $this->data = $json_data;
        $this->position = 0;
        return true;
    }

    public function stream_read($count) {
        $ret = substr($this->data, $this->position, $count);
        $this->position += strlen($ret);
        return $ret;
    }

    public function stream_eof() {
        return $this->position >= strlen($this->data);
    }

    public function stream_stat() {
        return [];
    }
}

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'></head><body>";
echo "<h1>Test AJAX - Actualización BCV</h1>";

try {
    require_once __DIR__ . '/app/controllers/AdminController.php';

    echo "<h2>1. Instanciando controlador...</h2>";
    $controller = new AdminController();

    echo "<p style='color: green;'>✓ Controlador instanciado</p>";

    echo "<h2>2. Ejecutando actualizarTasaBCV()...</h2>";

    // Capturar la salida
    ob_start();
    $controller->actualizarTasaBCV();
    $output = ob_get_clean();

    echo "<h2>3. Respuesta del servidor:</h2>";
    echo "<div style='background: #f0f0f0; padding: 15px; border: 1px solid #ccc; border-radius: 5px;'>";
    echo "<pre>" . htmlspecialchars($output) . "</pre>";
    echo "</div>";

    // Intentar decodificar JSON
    $json = json_decode($output, true);

    if ($json) {
        echo "<h2>4. JSON Decodificado:</h2>";
        echo "<div style='background: " . ($json['success'] ? '#d4edda' : '#f8d7da') . "; padding: 15px; border-radius: 5px;'>";
        echo "<strong>Success:</strong> " . ($json['success'] ? 'true' : 'false') . "<br>";
        echo "<strong>Message:</strong> " . htmlspecialchars($json['message']) . "<br>";

        if (isset($json['tasa'])) {
            echo "<strong>Tasa:</strong> " . $json['tasa'] . " Bs/USD<br>";
        }
        if (isset($json['fecha'])) {
            echo "<strong>Fecha:</strong> " . $json['fecha'] . "<br>";
        }
        if (isset($json['fuente'])) {
            echo "<strong>Fuente:</strong> " . $json['fuente'] . "<br>";
        }
        echo "</div>";

        if ($json['success']) {
            echo "<h2 style='color: green;'>✓✓✓ TEST EXITOSO!</h2>";
        } else {
            echo "<h2 style='color: orange;'>⚠ Test completado con errores</h2>";
        }
    } else {
        echo "<h2 style='color: red;'>✗ Error: La respuesta no es JSON válido</h2>";
    }

} catch (Exception $e) {
    echo "<p style='color: red;'><strong>Error:</strong> " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<hr>";
echo "<p><a href='admin/configuracion'>Ir a Configuración</a></p>";
echo "</body></html>";
