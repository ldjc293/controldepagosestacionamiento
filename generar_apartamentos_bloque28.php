<?php
/**
 * Script para generar apartamentos del Bloque 28
 *
 * Estructura corregida:
 * - Escalera 1: 5 apartamentos/piso × 5 pisos (PB + 4) = 25 apartamentos
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/app/models/Apartamento.php';

// Configuración del Bloque 28
$configuracion = [
    '28' => [
        'escaleras' => [
            1 => ['apartamentos_por_piso' => 5, 'pisos' => 5] // PB (0) + 4 pisos = 5 pisos
        ]
    ]
];

$apartamentosCreados = 0;
$apartamentosExistentes = 0;

echo "=== Generación de Apartamentos del Bloque 28 (Corregido) ===\n\n";

try {
    Database::beginTransaction();

    foreach ($configuracion as $bloque => $configBloque) {
        echo "Procesando Bloque $bloque...\n";

        foreach ($configBloque['escaleras'] as $escalera => $config) {
            $apartamentosPorPiso = $config['apartamentos_por_piso'];
            $totalPisos = $config['pisos'];

            echo "  Escalera $escalera: $apartamentosPorPiso apartamentos/piso × $totalPisos pisos\n";

            // Generar apartamentos para cada piso (0=PB, 1-4=pisos)
            for ($piso = 0; $piso < $totalPisos; $piso++) {
                for ($numApto = 1; $numApto <= $apartamentosPorPiso; $numApto++) {
                    // Formato del número de apartamento: PISO + NÚMERO (001, 002, 101, 102, etc.)
                    // PB: 001, 002, 003...
                    // Piso 1: 101, 102, 103...

                    if ($piso == 0) {
                        // Planta Baja: 001, 002, 003...
                        $numeroApartamento = str_pad($numApto, 3, '0', STR_PAD_LEFT);
                    } else {
                        // Piso X: X01, X02, X03...
                        $numeroApartamento = $piso . str_pad($numApto, 2, '0', STR_PAD_LEFT);
                    }

                    // Verificar si ya existe
                    $existente = Apartamento::findByDatos($bloque, (string)$escalera, $piso, $numeroApartamento);

                    if ($existente) {
                        echo "    ✓ Ya existe: Bloque $bloque, Escalera $escalera, Piso $piso, Apto $numeroApartamento\n";
                        $apartamentosExistentes++;
                    } else {
                        // Crear apartamento
                        $data = [
                            'bloque' => $bloque,
                            'escalera' => (string)$escalera,
                            'piso' => $piso,
                            'numero_apartamento' => $numeroApartamento,
                            'activo' => true
                        ];

                        $id = Apartamento::create($data);
                        echo "    + Creado: Bloque $bloque, Escalera $escalera, Piso $piso, Apto $numeroApartamento (ID: $id)\n";
                        $apartamentosCreados++;
                    }
                }
            }

            echo "\n";
        }

        echo "Bloque $bloque completado.\n\n";
    }

    Database::commit();

    echo "=== Resumen ===\n";
    echo "Apartamentos creados: $apartamentosCreados\n";
    echo "Apartamentos ya existentes: $apartamentosExistentes\n";
    echo "Total procesados: " . ($apartamentosCreados + $apartamentosExistentes) . "\n";
    echo "\n✓ Proceso completado exitosamente\n";

} catch (Exception $e) {
    Database::rollback();
    echo "\n✗ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}