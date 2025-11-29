<?php
/**
 * Script para análisis detallado y corrección de apartamentos faltantes
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/app/models/Apartamento.php';

echo "=== Análisis Detallado y Corrección ===\n\n";

try {
    // Configuración esperada
    $configuracion = [
        1 => ['apartamentos_por_piso' => 3, 'pisos' => 5],
        2 => ['apartamentos_por_piso' => 4, 'pisos' => 5],
        3 => ['apartamentos_por_piso' => 3, 'pisos' => 5],
        4 => ['apartamentos_por_piso' => 5, 'pisos' => 5]
    ];
    
    $bloque = '27';
    $faltantes = [];
    $creados = 0;
    
    Database::beginTransaction();
    
    foreach ($configuracion as $escalera => $config) {
        echo "Escalera $escalera:\n";
        
        for ($piso = 0; $piso < $config['pisos']; $piso++) {
            $esperados = [];
            $existentes = [];
            
            // Generar lista de apartamentos esperados
            for ($numApto = 1; $numApto <= $config['apartamentos_por_piso']; $numApto++) {
                if ($piso == 0) {
                    $numeroApartamento = str_pad($numApto, 3, '0', STR_PAD_LEFT);
                } else {
                    $numeroApartamento = str_pad(($piso * 100) + $numApto, 3, '0', STR_PAD_LEFT);
                }
                $esperados[] = $numeroApartamento;
            }
            
            // Obtener apartamentos existentes
            $sql = "SELECT numero_apartamento FROM apartamentos 
                    WHERE bloque = ? AND escalera = ? AND piso = ?
                    ORDER BY numero_apartamento";
            $results = Database::fetchAll($sql, [$bloque, (string)$escalera, $piso]);
            
            foreach ($results as $row) {
                $existentes[] = $row['numero_apartamento'];
            }
            
            // Encontrar faltantes
            $faltantesEnPiso = array_diff($esperados, $existentes);
            
            if (count($faltantesEnPiso) > 0) {
                $pisoNombre = $piso == 0 ? 'PB' : "Piso $piso";
                echo "  $pisoNombre - Faltantes: " . implode(', ', $faltantesEnPiso) . "\n";
                
                // Crear apartamentos faltantes
                foreach ($faltantesEnPiso as $numeroApartamento) {
                    $data = [
                        'bloque' => $bloque,
                        'escalera' => (string)$escalera,
                        'piso' => $piso,
                        'numero_apartamento' => $numeroApartamento,
                        'activo' => true
                    ];
                    
                    $id = Apartamento::create($data);
                    echo "    + Creado: Apto $numeroApartamento (ID: $id)\n";
                    $creados++;
                }
            } else {
                $pisoNombre = $piso == 0 ? 'PB' : "Piso $piso";
                echo "  $pisoNombre - ✓ Completo (" . count($existentes) . " apartamentos)\n";
            }
        }
        echo "\n";
    }
    
    Database::commit();
    
    echo "=== Resumen ===\n";
    echo "Apartamentos creados: $creados\n";
    
    // Verificar total final
    $sql = "SELECT COUNT(*) as total FROM apartamentos WHERE bloque = '27'";
    $total = Database::fetchOne($sql);
    echo "Total de apartamentos en Bloque 27: {$total['total']}\n";
    
    // Mostrar resumen por escalera
    $sql = "SELECT escalera, COUNT(*) as total 
            FROM apartamentos 
            WHERE bloque = '27' 
            GROUP BY escalera 
            ORDER BY escalera";
    $porEscalera = Database::fetchAll($sql);
    
    echo "\nPor escalera:\n";
    foreach ($porEscalera as $row) {
        echo "  Escalera {$row['escalera']}: {$row['total']} apartamentos\n";
    }
    
    echo "\n✓ Análisis y corrección completados\n";
    
} catch (Exception $e) {
    Database::rollback();
    echo "\n✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
