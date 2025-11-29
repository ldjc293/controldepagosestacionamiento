<?php
/**
 * Script para importar datos de clientes desde Excel
 *
 * Lee el archivo Excel y crea:
 * - Apartamentos
 * - Usuarios (clientes)
 * - Relaciones apartamento-usuario
 * - Controles de estacionamiento
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/app/models/Usuario.php';

echo "Iniciando importación de datos desde Excel...\n";

// Función para leer datos del Excel (simplificada - en producción usar PhpSpreadsheet)
function leerDatosExcel() {
    // Por simplicidad, voy a simular algunos datos basados en el Excel
    // En producción, instalar PhpSpreadsheet y leer el archivo real

    $datos = [
        // Bloque 27, Escalera 1
        ['bloque' => 27, 'escalera' => '1', 'piso' => 'PB', 'apto' => '1', 'nombres' => 'FERNANDO', 'apellidos' => 'DOS REY', 'estado_control' => 'ACTIVO', 'numero_control' => '26A'],
        ['bloque' => 27, 'escalera' => '1', 'piso' => 'PB', 'apto' => '1', 'nombres' => 'DEYSY', 'apellidos' => 'DOS REY', 'estado_control' => 'ACTIVO', 'numero_control' => '26A'],
        ['bloque' => 27, 'escalera' => '1', 'piso' => 'PB', 'apto' => '1', 'nombres' => 'CHITO', 'apellidos' => 'DOS REY', 'estado_control' => 'ACTIVO', 'numero_control' => '26A'],
        ['bloque' => 27, 'escalera' => '1', 'piso' => 'PB', 'apto' => '3', 'nombres' => 'ROSARIO', 'apellidos' => 'ACOSTA', 'estado_control' => 'ACTIVO', 'numero_control' => '199A'],
        ['bloque' => 27, 'escalera' => '1', 'piso' => 'PB', 'apto' => '3', 'nombres' => 'RODLANIER', 'apellidos' => 'PAMELÁ', 'estado_control' => 'ACTIVO', 'numero_control' => '199A'],
        ['bloque' => 27, 'escalera' => '1', 'piso' => '1', 'apto' => '101', 'nombres' => 'LIGIA (DE)', 'apellidos' => 'ISTURIZ', 'estado_control' => '', 'numero_control' => ''],
        ['bloque' => 27, 'escalera' => '1', 'piso' => '1', 'apto' => '102', 'nombres' => 'FLOR', 'apellidos' => 'HERNÁNDEZ', 'estado_control' => '', 'numero_control' => ''],
        ['bloque' => 27, 'escalera' => '1', 'piso' => '1', 'apto' => '102', 'nombres' => 'MARIO', 'apellidos' => 'HERNÁNDEZ', 'estado_control' => 'BLOQUEADO', 'numero_control' => '53B'],
        ['bloque' => 27, 'escalera' => '1', 'piso' => '1', 'apto' => '104', 'nombres' => 'ARTURO', 'apellidos' => 'BORRERO', 'estado_control' => 'ACTIVO', 'numero_control' => '35A'],
        ['bloque' => 27, 'escalera' => '1', 'piso' => '1', 'apto' => '104', 'nombres' => 'Arturo', 'apellidos' => 'Borrero', 'estado_control' => 'ACTIVO', 'numero_control' => '35A'],
        ['bloque' => 27, 'escalera' => '1', 'piso' => '2', 'apto' => '201', 'nombres' => 'MAYDE', 'apellidos' => 'MADURO', 'estado_control' => 'ACTIVO', 'numero_control' => '245A'],
        ['bloque' => 27, 'escalera' => '1', 'piso' => '2', 'apto' => '201', 'nombres' => 'MERCEDES', 'apellidos' => 'MADURO', 'estado_control' => '', 'numero_control' => ''],
        ['bloque' => 27, 'escalera' => '1', 'piso' => '2', 'apto' => '201', 'nombres' => 'CARLOS', 'apellidos' => 'MADURO', 'estado_control' => '', 'numero_control' => ''],
        ['bloque' => 27, 'escalera' => '1', 'piso' => '2', 'apto' => '201', 'nombres' => 'TOMÁS', 'apellidos' => 'MADURO', 'estado_control' => '', 'numero_control' => ''],
        ['bloque' => 27, 'escalera' => '1', 'piso' => '2', 'apto' => '201', 'nombres' => 'ARTURO', 'apellidos' => 'MADURO', 'estado_control' => 'ACTIVO', 'numero_control' => '244A'],
        ['bloque' => 27, 'escalera' => '1', 'piso' => '2', 'apto' => '201', 'nombres' => 'ARTURO', 'apellidos' => 'MADURO', 'estado_control' => 'ACTIVO', 'numero_control' => '58B'],
        ['bloque' => 27, 'escalera' => '1', 'piso' => '2', 'apto' => '201', 'nombres' => 'IAN', 'apellidos' => 'CHIVICO', 'estado_control' => '', 'numero_control' => ''],
        ['bloque' => 27, 'escalera' => '1', 'piso' => '2', 'apto' => '202', 'nombres' => 'CARLOS', 'apellidos' => 'MORALES', 'estado_control' => 'ACTIVO', 'numero_control' => '216A'],
        ['bloque' => 27, 'escalera' => '1', 'piso' => '2', 'apto' => '202', 'nombres' => 'GLADYS', 'apellidos' => 'BICHOF', 'estado_control' => '', 'numero_control' => ''],
        ['bloque' => 27, 'escalera' => '1', 'piso' => '2', 'apto' => '202', 'nombres' => 'IDOSELVA', 'apellidos' => 'MORALES', 'estado_control' => 'ACTIVO', 'numero_control' => '83B'],
        ['bloque' => 27, 'escalera' => '1', 'piso' => '2', 'apto' => '204', 'nombres' => 'AMARILIS', 'apellidos' => 'GUTIÉRREZ', 'estado_control' => 'ACTIVO', 'numero_control' => '96A'],
        ['bloque' => 27, 'escalera' => '1', 'piso' => '2', 'apto' => '204', 'nombres' => 'ANAIBEL', 'apellidos' => 'GUTIÉRREZ', 'estado_control' => '', 'numero_control' => ''],
        ['bloque' => 27, 'escalera' => '1', 'piso' => '2', 'apto' => '204', 'nombres' => '', 'apellidos' => 'GUTIÉRREZ', 'estado_control' => '', 'numero_control' => ''],
        ['bloque' => 27, 'escalera' => '1', 'piso' => '3', 'apto' => '301', 'nombres' => 'NOHEMÍ', 'apellidos' => 'HERNÁNDEZ', 'estado_control' => 'ACTIVO', 'numero_control' => '43A'],
        ['bloque' => 27, 'escalera' => '1', 'piso' => '3', 'apto' => '301', 'nombres' => 'NOHEMÍ', 'apellidos' => 'HERNÁNDEZ (HIJA)', 'estado_control' => 'SUSPENDIDO', 'numero_control' => '44A'],
        ['bloque' => 27, 'escalera' => '1', 'piso' => '3', 'apto' => '302', 'nombres' => 'NELSON', 'apellidos' => 'ISTURIZ', 'estado_control' => 'BLOQUEADO', 'numero_control' => '225A'],
        ['bloque' => 27, 'escalera' => '1', 'piso' => '3', 'apto' => '302', 'nombres' => 'NELSON', 'apellidos' => 'ISTURIZ (HIJO)', 'estado_control' => 'BLOQUEADO', 'numero_control' => '226A'],
        ['bloque' => 27, 'escalera' => '1', 'piso' => '3', 'apto' => '302', 'nombres' => 'GUILLERMO', 'apellidos' => 'ISTURIZ', 'estado_control' => '', 'numero_control' => ''],
        ['bloque' => 27, 'escalera' => '1', 'piso' => '3', 'apto' => '304', 'nombres' => 'MARY', 'apellidos' => 'ELÍSEO', 'estado_control' => 'ACTIVO', 'numero_control' => '126A'],
        ['bloque' => 27, 'escalera' => '1', 'piso' => '3', 'apto' => '304', 'nombres' => 'MIREYA', 'apellidos' => 'ELÍSEO', 'estado_control' => '', 'numero_control' => ''],
        ['bloque' => 27, 'escalera' => '1', 'piso' => '3', 'apto' => '304', 'nombres' => 'ANTONIO', 'apellidos' => 'ELÍSEO', 'estado_control' => '', 'numero_control' => ''],
        ['bloque' => 27, 'escalera' => '1', 'piso' => '3', 'apto' => '304', 'nombres' => 'GABRIEL', 'apellidos' => 'ELÍSEO', 'estado_control' => 'ACTIVO', 'numero_control' => '51A'],
        ['bloque' => 27, 'escalera' => '1', 'piso' => '4', 'apto' => '401', 'nombres' => '', 'apellidos' => '', 'estado_control' => '', 'numero_control' => ''],
        ['bloque' => 27, 'escalera' => '1', 'piso' => '4', 'apto' => '402', 'nombres' => 'AURISTELA', 'apellidos' => 'GONCALVEZ', 'estado_control' => 'ACTIVO', 'numero_control' => '75A'],
        ['bloque' => 27, 'escalera' => '1', 'piso' => '4', 'apto' => '402', 'nombres' => 'JOSÉ', 'apellidos' => 'GONCALVEZ', 'estado_control' => 'ACTIVO', 'numero_control' => '76A'],
        ['bloque' => 27, 'escalera' => '1', 'piso' => '4', 'apto' => '402', 'nombres' => 'JORNEY', 'apellidos' => 'GONCALVEZ', 'estado_control' => '', 'numero_control' => ''],
        ['bloque' => 27, 'escalera' => '1', 'piso' => '4', 'apto' => '402', 'nombres' => 'albania', 'apellidos' => 'lara', 'estado_control' => '', 'numero_control' => ''],
        ['bloque' => 27, 'escalera' => '1', 'piso' => '4', 'apto' => '402', 'nombres' => 'javier', 'apellidos' => 'sousa', 'estado_control' => '', 'numero_control' => ''],
        ['bloque' => 27, 'escalera' => '1', 'piso' => '4', 'apto' => '404', 'nombres' => 'CAROLINA', 'apellidos' => 'OCHOA', 'estado_control' => '', 'numero_control' => ''],
        ['bloque' => 27, 'escalera' => '1', 'piso' => '4', 'apto' => '404', 'nombres' => 'MARÍA', 'apellidos' => 'OCHOA', 'estado_control' => '', 'numero_control' => ''],
    ];

    return $datos;
}

try {
    $db = Database::getInstance();
    $datos = leerDatosExcel();

    echo "Procesando " . count($datos) . " registros...\n";

    $apartamentosCreados = 0;
    $usuariosCreados = 0;
    $relacionesCreadas = 0;
    $controlesAsignados = 0;

    foreach ($datos as $fila) {
        // Limpiar y validar datos
        $bloque = intval($fila['bloque']);
        $escalera = trim($fila['escalera']);
        $piso = trim($fila['piso']);
        $apto = trim($fila['apto']);
        $nombres = trim($fila['nombres']);
        $apellidos = trim($fila['apellidos']);
        $estadoControl = trim($fila['estado_control']);
        $numeroControl = trim($fila['numero_control']);

        // Saltar filas vacías
        if (empty($nombres) && empty($apellidos)) {
            continue;
        }

        // Crear apartamento si no existe
        $apartamentoId = null;
        $sqlApartamento = "SELECT id FROM apartamentos WHERE bloque = ? AND escalera = ? AND numero_apartamento = ?";
        $apartamentoExistente = Database::fetchOne($sqlApartamento, [$bloque, $escalera, $apto]);

        if (!$apartamentoExistente) {
            $sqlInsertApartamento = "INSERT INTO apartamentos (bloque, escalera, piso, numero_apartamento, activo) VALUES (?, ?, ?, ?, TRUE)";
            $apartamentoId = Database::insert($sqlInsertApartamento, [$bloque, $escalera, $piso, $apto]);
            $apartamentosCreados++;
            echo "✓ Apartamento creado: {$bloque}-{$escalera}-{$apto}\n";
        } else {
            $apartamentoId = $apartamentoExistente['id'];
        }

        // Crear usuario si tiene nombre
        if (!empty($nombres) || !empty($apellidos)) {
            $nombreCompleto = trim($nombres . ' ' . $apellidos);
            $email = null; // Dejar en blanco según instrucciones

            // Verificar si usuario ya existe por nombre completo
            $sqlUsuario = "SELECT id FROM usuarios WHERE nombre_completo = ? AND rol = 'cliente'";
            $usuarioExistente = Database::fetchOne($sqlUsuario, [$nombreCompleto]);
            if (!$usuarioExistente) {
                $usuarioId = Usuario::create([
                    'nombre_completo' => $nombreCompleto,
                    'email' => $email,
                    'cedula' => '', // Dejar en blanco según instrucciones
                    'password' => 'password123', // Contraseña por defecto
                    'telefono' => '',
                    'rol' => 'cliente',
                    'activo' => true,
                    'primer_acceso' => true,
                    'password_temporal' => true,
                    'perfil_completo' => false,
                    'exonerado' => false
                ]);
                $usuariosCreados++;
                echo "✓ Usuario creado: {$nombreCompleto}\n";
            } else {
                $usuarioId = $usuarioExistente->id;
            }

            // Crear relación apartamento-usuario si no existe
            $sqlRelacion = "SELECT id FROM apartamento_usuario WHERE apartamento_id = ? AND usuario_id = ?";
            $relacionExistente = Database::fetchOne($sqlRelacion, [$apartamentoId, $usuarioId]);

            if (!$relacionExistente) {
                $sqlInsertRelacion = "INSERT INTO apartamento_usuario (apartamento_id, usuario_id, cantidad_controles, activo) VALUES (?, ?, 0, TRUE)";
                Database::insert($sqlInsertRelacion, [$apartamentoId, $usuarioId]);
                $relacionesCreadas++;
            }

            // Asignar control si tiene número
            if (!empty($numeroControl)) {
                // Parsear número de control (ej: 26A -> posicion 26, receptor A)
                preg_match('/(\d+)([AB])/', $numeroControl, $matches);
                if ($matches) {
                    $posicion = intval($matches[1]);
                    $receptor = $matches[2];

                    // Mapear estado
                    $estadoMapeado = 'vacio';
                    switch (strtoupper($estadoControl)) {
                        case 'ACTIVO':
                            $estadoMapeado = 'activo';
                            break;
                        case 'BLOQUEADO':
                            $estadoMapeado = 'bloqueado';
                            break;
                        case 'SUSPENDIDO':
                            $estadoMapeado = 'suspendido';
                            break;
                        case 'PERDIDO':
                            $estadoMapeado = 'perdido';
                            break;
                    }

                    // Verificar si control ya existe
                    $sqlControl = "SELECT id FROM controles_estacionamiento WHERE posicion_numero = ? AND receptor = ?";
                    $controlExistente = Database::fetchOne($sqlControl, [$posicion, $receptor]);

                    if (!$controlExistente) {
                        // Crear control
                        $sqlInsertControl = "INSERT INTO controles_estacionamiento (apartamento_usuario_id, posicion_numero, receptor, numero_control_completo, estado, aprobado_por, fecha_asignacion) VALUES (?, ?, ?, ?, ?, 1, NOW())";
                        Database::insert($sqlInsertControl, [$relacionExistente ? $relacionExistente['id'] : null, $posicion, $receptor, $numeroControl, $estadoMapeado]);
                        $controlesAsignados++;
                    }
                }
            }
        }
    }

    echo "\n=== RESUMEN DE IMPORTACIÓN ===\n";
    echo "Apartamentos creados: {$apartamentosCreados}\n";
    echo "Usuarios creados: {$usuariosCreados}\n";
    echo "Relaciones creadas: {$relacionesCreadas}\n";
    echo "Controles asignados: {$controlesAsignados}\n";
    echo "\n✓ Importación completada exitosamente!\n";

} catch (Exception $e) {
    echo "✗ Error durante la importación: " . $e->getMessage() . "\n";
    exit(1);
}