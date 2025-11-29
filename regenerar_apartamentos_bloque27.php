<?php
/**
 * Script para regenerar apartamentos del Bloque 27 con numeración correcta
 * Formato: 001, 002, 003 (PB), 101, 102, 103 (Piso 1), etc.
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/config.php';

echo "=== Regeneración de Apartamentos del Bloque 27 ===\n\n";

try {
    Database::beginTransaction();
    
    // PASO 1: Eliminar apartamentos con formato incorrecto (PB-A, 1A, etc.)
    echo "PASO 1: Eliminando apartamentos con formato incorrecto...\n";
    
    $sql = "DELETE FROM apartamentos 
            WHERE bloque = '27' 
            AND (numero_apartamento LIKE 'PB-%' OR numero_apartamento REGEXP '^[0-9][A-Z]$')";
    
    $eliminados = Database::execute($sql);
    echo "✓ Eliminados: $eliminados apartamentos con formato incorrecto\n\n";
    
    // PASO 2: Generar apartamentos con numeración correcta
    echo "PASO 2: Generando apartamentos con numeración correcta...\n";
    
    $configuracion = [
        1 => ['apartamentos_por_piso' => 3, 'pisos' => 5],
        2 => ['apartamentos_por_piso' => 4, 'pisos' => 5],
        3 => ['apartamentos_por_piso' => 3, 'pisos' => 5],
        4 => ['apartamentos_por_piso' => 5, 'pisos' => 5]
    ];
    
    $bloque = '27';
    $apartamentosCreados = 0;
    
    foreach ($configuracion as $escalera => $config) {
        $apartamentosPorPiso = $config['apartamentos_por_piso'];
        $totalPisos = $config['pisos'];
        
        echo "Escalera $escalera: $apartamentosPorPiso apartamentos/piso × $totalPisos pisos\n";
        
        for ($piso = 0; $piso < $totalPisos; $piso++) {
            for ($numApto = 1; $numApto <= $apartamentosPorPiso; $numApto++) {
                // Formato: PB = 001, 002, 003
                //          Piso 1 = 101, 102, 103
                //          Piso 2 = 201, 202, 203, etc.
                
                if ($piso == 0) {
                    // Planta Baja: 001, 002, 003...
                    $numeroApartamento = str_pad($numApto, 3, '0', STR_PAD_LEFT);
                } else {
                    // Pisos superiores: 101, 102, 201, 202, etc.
                    $numeroApartamento = ($piso * 100) + $numApto;
                    $numeroApartamento = str_pad($numeroApartamento, 3, '0', STR_PAD_LEFT);
                }
                
                // Verificar si ya existe
                $existente = Apartamento::findByDatos($bloque, (string)$escalera, $piso, $numeroApartamento);
                
                if (!$existente) {
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
    echo "Apartamentos eliminados (formato incorrecto): $eliminados\n";
    echo "Apartamentos creados (formato correcto): $apartamentosCreados\n";
    
    // Verificar resultado final
    $sql = "SELECT COUNT(*) as total FROM apartamentos WHERE bloque = '27'";
    $total = Database::fetchOne($sql);
    echo "Total de apartamentos en Bloque 27: {$total['total']}\n";
    
    echo "\n✓ Regeneración completada exitosamente\n";
    
} catch (Exception $e) {
    Database::rollback();
    echo "\n✗ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
