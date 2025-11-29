<?php
/**
 * Script para generar apartamentos del Bloque 27
 * 
 * Estructura:
 * - Escalera 1: 3 apartamentos/piso × 5 pisos (PB + 4) = 15 apartamentos
 * - Escalera 2: 4 apartamentos/piso × 5 pisos (PB + 4) = 20 apartamentos
 * - Escalera 3: 3 apartamentos/piso × 5 pisos (PB + 4) = 15 apartamentos
 * - Escalera 4: 5 apartamentos/piso × 5 pisos (PB + 4) = 25 apartamentos
 * Total: 75 apartamentos
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/app/models/Apartamento.php';

// Configuración de escaleras del Bloque 27
$configuracion = [
    1 => ['apartamentos_por_piso' => 3, 'pisos' => 5], // PB (0) + 4 pisos = 5 pisos
    2 => ['apartamentos_por_piso' => 4, 'pisos' => 5],
    3 => ['apartamentos_por_piso' => 3, 'pisos' => 5],
    4 => ['apartamentos_por_piso' => 5, 'pisos' => 5]
];

$bloque = '27';
$apartamentosCreados = 0;
$apartamentosExistentes = 0;

echo "=== Generación de Apartamentos del Bloque 27 ===\n\n";

try {
    Database::beginTransaction();
    
    foreach ($configuracion as $escalera => $config) {
        $apartamentosPorPiso = $config['apartamentos_por_piso'];
        $totalPisos = $config['pisos'];
        
        echo "Escalera $escalera: $apartamentosPorPiso apartamentos/piso × $totalPisos pisos\n";
        
        // Generar apartamentos para cada piso (0=PB, 1-4=pisos)
        for ($piso = 0; $piso < $totalPisos; $piso++) {
            for ($numApto = 1; $numApto <= $apartamentosPorPiso; $numApto++) {
                // Formato del número de apartamento: PISO + LETRA (A, B, C, D, E)
                // PB: A, B, C...
                // Piso 1: 1A, 1B, 1C...
                // Piso 2: 2A, 2B, 2C...
                
                $letra = chr(64 + $numApto); // A=65, B=66, etc.
                
                if ($piso == 0) {
                    // Planta Baja
                    $numeroApartamento = "PB-$letra";
                } else {
                    $numeroApartamento = "$piso$letra";
                }
                
                // Verificar si ya existe
                $existente = Apartamento::findByDatos($bloque, (string)$escalera, $piso, $numeroApartamento);
                
                if ($existente) {
                    echo "  ✓ Ya existe: Bloque $bloque, Escalera $escalera, Piso $piso, Apto $numeroApartamento\n";
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
                    echo "  + Creado: Bloque $bloque, Escalera $escalera, Piso $piso, Apto $numeroApartamento (ID: $id)\n";
                    $apartamentosCreados++;
                }
            }
        }
        
        echo "\n";
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
