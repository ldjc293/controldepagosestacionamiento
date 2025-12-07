<?php
/**
 * Test de Integración: Asignación de Controles en Aprobación de Solicitudes
 *
 * Este archivo prueba la funcionalidad completa de asignación de controles
 * durante la aprobación de solicitudes de registro de nuevos usuarios.
 */

require_once 'config/database.php';
require_once 'config/config.php';
require_once 'app/models/SolicitudCambio.php';
require_once 'app/models/Control.php';
require_once 'app/models/Usuario.php';

echo "=== TEST DE INTEGRACIÓN: ASIGNACIÓN DE CONTROLES ===\n\n";

// Test 1: Verificar que hay controles disponibles
echo "Test 1: Verificando controles disponibles...\n";
$controlesDisponibles = Control::getVacios();
echo "Controles disponibles: " . count($controlesDisponibles) . "\n";

if (count($controlesDisponibles) < 2) {
    echo "❌ ERROR: No hay suficientes controles disponibles para las pruebas\n";
    exit(1);
}
echo "✅ OK\n\n";

// Test 2: Crear una solicitud de registro de nuevo usuario
echo "Test 2: Creando solicitud de registro de nuevo usuario...\n";

$datosUsuario = [
    'nombre_completo' => 'Usuario de Prueba',
    'email' => 'test_' . time() . '@example.com',
    'password' => password_hash('Test123456', PASSWORD_BCRYPT),
    'telefono' => '04141234567',
    'bloque' => 'A',
    'escalera' => '1',
    'piso' => '1',
    'apartamento' => '101',
    'cantidad_controles' => 2
];

$solicitudId = SolicitudCambio::create([
    'tipo_solicitud' => 'registro_nuevo_usuario',
    'datos_nuevo_usuario' => $datosUsuario,
    'estado' => 'pendiente'
]);

if (!$solicitudId) {
    echo "❌ ERROR: No se pudo crear la solicitud\n";
    exit(1);
}

echo "✅ Solicitud creada con ID: $solicitudId\n\n";

// Test 3: Obtener la solicitud y verificar datos
echo "Test 3: Verificando datos de la solicitud...\n";
$solicitud = SolicitudCambio::findById($solicitudId);

if (!$solicitud) {
    echo "❌ ERROR: No se pudo obtener la solicitud\n";
    exit(1);
}

$datosObtenidos = $solicitud->getDatosNuevoUsuario();
if ($datosObtenidos['cantidad_controles'] !== 2) {
    echo "❌ ERROR: Los datos de la solicitud no coinciden\n";
    exit(1);
}

echo "✅ Datos de solicitud correctos\n\n";

// Test 4: Simular asignación manual de controles
echo "Test 4: Probando asignación manual de controles...\n";

$datosAsignacion = [
    'cantidad_controles' => 2,
    'controles' => [
        $controlesDisponibles[0]['id'],
        $controlesDisponibles[1]['id']
    ],
    'bloque' => 'A',
    'escalera' => '1',
    'apartamento' => '101',
    'piso' => '1'
];

try {
    $usuarioId = $solicitud->crearUsuarioConAsignacionManual(1, $datosAsignacion); // ID de admin = 1

    if (!$usuarioId) {
        echo "❌ ERROR: No se pudo crear el usuario con asignación manual\n";
        exit(1);
    }

    echo "✅ Usuario creado con ID: $usuarioId\n\n";

    // Test 5: Verificar que los controles fueron asignados
    echo "Test 5: Verificando asignación de controles...\n";

    $usuario = Usuario::findById($usuarioId);
    if (!$usuario) {
        echo "❌ ERROR: Usuario no encontrado\n";
        exit(1);
    }

    // Obtener apartamento_usuario_id
    $sql = "SELECT id FROM apartamento_usuario WHERE usuario_id = ? AND activo = 1";
    $apartamentoUsuario = Database::fetchOne($sql, [$usuarioId]);

    if (!$apartamentoUsuario) {
        echo "❌ ERROR: Apartamento usuario no encontrado\n";
        exit(1);
    }

    $controlesAsignados = Control::getByApartamentoUsuario($apartamentoUsuario['id']);

    if (count($controlesAsignados) !== 2) {
        echo "❌ ERROR: No se asignaron los controles correctamente. Esperados: 2, Obtenidos: " . count($controlesAsignados) . "\n";
        exit(1);
    }

    echo "✅ Controles asignados correctamente:\n";
    foreach ($controlesAsignados as $control) {
        echo "   - {$control->numero_control_completo} (Estado: {$control->estado})\n";
    }

    // Test 6: Verificar que la solicitud fue aprobada
    echo "\nTest 6: Verificando estado de la solicitud...\n";
    $solicitudActualizada = SolicitudCambio::findById($solicitudId);

    if ($solicitudActualizada->estado !== 'aprobada') {
        echo "❌ ERROR: La solicitud no fue aprobada correctamente\n";
        exit(1);
    }

    echo "✅ Solicitud aprobada correctamente\n\n";

    // Test 7: Verificar que los controles ya no están disponibles
    echo "Test 7: Verificando que los controles ya no están disponibles...\n";
    $controlesDisponiblesFinal = Control::getVacios();

    $controlesUsados = array_filter($controlesDisponiblesFinal, function($c) use ($datosAsignacion) {
        return !in_array($c['id'], $datosAsignacion['controles']);
    });

    if (count($controlesDisponiblesFinal) !== count($controlesDisponibles) - 2) {
        echo "❌ ERROR: Los controles no fueron marcados como no disponibles\n";
        exit(1);
    }

    echo "✅ Controles correctamente marcados como asignados\n\n";

    echo "🎉 TODOS LOS TESTS PASARON EXITOSAMENTE 🎉\n\n";

    // Limpieza: Desactivar usuario de prueba (soft delete)
    echo "Realizando limpieza...\n";
    $usuario->desactivar();

    // Desasignar controles
    foreach ($controlesAsignados as $control) {
        $control->desasignar('Limpieza de test', 1);
    }

    echo "✅ Limpieza completada\n";

} catch (Exception $e) {
    echo "❌ ERROR durante la prueba: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n=== FIN DEL TEST ===\n";
?>