<?php
/**
 * Script para corregir datos con encoding incorrecto
 *
 * Este script detecta y corrige datos que fueron guardados con latin1
 * pero se están leyendo como utf8mb4
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

set_time_limit(300); // 5 minutos

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Corrección de Charset en Datos</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; max-width: 1200px; margin: 0 auto; }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
        table { border-collapse: collapse; width: 100%; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .button {
            background: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            cursor: pointer;
            font-size: 16px;
            margin: 10px 5px;
        }
        .button:hover { background: #45a049; }
        .button-danger { background: #f44336; }
        .button-danger:hover { background: #da190b; }
    </style>
</head>
<body>
    <h1>🔧 Corrección de Charset en Datos</h1>

<?php

try {
    $pdo = Database::getInstance();

    // Función para detectar si un texto tiene problemas de encoding
    function hasEncodingIssue($text) {
        // Detectar patrones de doble encoding UTF-8
        // Buscar bytes que indican problemas comunes
        $hasIssue = (
            strpos($text, "\xC3\x83") !== false ||  // Ã seguido de otro byte UTF-8
            strpos($text, "\xC3\x82") !== false ||  // Â seguido de otro byte UTF-8
            strpos($text, "\xE2\x80") !== false ||  // Guiones y comillas mal codificados
            strpos($text, "\xE2\x82") !== false ||  // Símbolos especiales mal codificados
            strpos($text, "\xC2\xA0") !== false     // Espacios no rompibles mal codificados
        );

        return $hasIssue;
    }

    // Función para corregir el encoding
    function fixEncoding($text) {
        // Si el texto no tiene problemas, devolverlo tal cual
        if (!hasEncodingIssue($text)) {
            return $text;
        }

        // Convertir de UTF-8 mal interpretado a latin1, luego a UTF-8 correcto
        $fixed = mb_convert_encoding($text, 'UTF-8', 'UTF-8');

        // Si eso no funciona, intentar otra estrategia
        if (hasEncodingIssue($fixed)) {
            // Intentar decodificar como si fuera latin1 guardado como UTF-8
            $fixed = utf8_decode($text);
            $fixed = utf8_encode($fixed);
        }

        return $fixed;
    }

    // Verificar si se debe ejecutar la corrección
    $execute = isset($_POST['execute']) && $_POST['execute'] === 'yes';

    if (!$execute) {
        echo "<h2>📋 Análisis de Datos con Problemas de Encoding</h2>";
        echo "<p>Este script analizará todas las tablas y mostrará los registros con problemas de encoding.</p>";

        $tablesWithIssues = [];

        // Tablas a revisar con sus columnas de texto
        $tablesToCheck = [
            'usuarios' => ['nombre_completo', 'motivo_exoneracion'],
            'apartamentos' => ['bloque', 'numero'],
            'configuracion_tarifas' => [],
            'logs_actividad' => ['modulo', 'accion', 'datos_anteriores', 'datos_nuevos'],
            'notificaciones' => ['titulo', 'mensaje'],
            'solicitudes_cambios' => ['tipo_solicitud', 'descripcion', 'respuesta_admin']
        ];

        foreach ($tablesToCheck as $table => $columns) {
            if (empty($columns)) continue;

            $issuesFound = [];

            foreach ($columns as $column) {
                try {
                    $sql = "SELECT id, `$column` FROM `$table` WHERE `$column` IS NOT NULL";
                    $stmt = $pdo->query($sql);

                    while ($row = $stmt->fetch()) {
                        $value = $row[$column];
                        if ($value && hasEncodingIssue($value)) {
                            $issuesFound[] = [
                                'id' => $row['id'],
                                'column' => $column,
                                'original' => $value,
                                'fixed' => fixEncoding($value)
                            ];
                        }
                    }
                } catch (Exception $e) {
                    echo "<p class='warning'>⚠️ Error al revisar $table.$column: {$e->getMessage()}</p>";
                }
            }

            if (!empty($issuesFound)) {
                $tablesWithIssues[$table] = $issuesFound;
            }
        }

        if (empty($tablesWithIssues)) {
            echo "<p class='success'>✅ No se encontraron problemas de encoding en los datos.</p>";
        } else {
            echo "<p class='warning'>⚠️ Se encontraron problemas en las siguientes tablas:</p>";

            $totalIssues = 0;
            foreach ($tablesWithIssues as $table => $issues) {
                $count = count($issues);
                $totalIssues += $count;

                echo "<h3>Tabla: $table ($count registros con problemas)</h3>";
                echo "<table>";
                echo "<tr><th>ID</th><th>Columna</th><th>Valor Actual</th><th>Valor Corregido</th></tr>";

                foreach ($issues as $issue) {
                    echo "<tr>";
                    echo "<td>{$issue['id']}</td>";
                    echo "<td>{$issue['column']}</td>";
                    echo "<td>" . htmlspecialchars($issue['original']) . "</td>";
                    echo "<td class='success'><strong>" . htmlspecialchars($issue['fixed']) . "</strong></td>";
                    echo "</tr>";
                }

                echo "</table>";
            }

            echo "<hr>";
            echo "<h2>🚀 ¿Desea corregir estos datos?</h2>";
            echo "<p>Se corregirán <strong>$totalIssues registros</strong> en total.</p>";

            echo "<form method='POST' onsubmit='return confirm(\"¿Está seguro de querer corregir los datos? Esta acción modificará la base de datos.\")'>";
            echo "<input type='hidden' name='execute' value='yes'>";
            echo "<button type='submit' class='button'>✅ Sí, Corregir Datos</button>";
            echo "</form>";

            echo "<p class='warning'><strong>Nota:</strong> Se recomienda hacer un backup de la base de datos antes de ejecutar la corrección.</p>";
        }

    } else {
        // EJECUTAR CORRECCIÓN
        echo "<h2>⚙️ Ejecutando Corrección...</h2>";

        $tablesWithIssues = [];

        $tablesToCheck = [
            'usuarios' => ['nombre_completo', 'motivo_exoneracion'],
            'apartamentos' => ['bloque', 'numero'],
            'logs_actividad' => ['modulo', 'accion', 'datos_anteriores', 'datos_nuevos'],
            'notificaciones' => ['titulo', 'mensaje'],
            'solicitudes_cambios' => ['tipo_solicitud', 'descripcion', 'respuesta_admin']
        ];

        $totalFixed = 0;

        foreach ($tablesToCheck as $table => $columns) {
            if (empty($columns)) continue;

            foreach ($columns as $column) {
                try {
                    $sql = "SELECT id, `$column` FROM `$table` WHERE `$column` IS NOT NULL";
                    $stmt = $pdo->query($sql);

                    while ($row = $stmt->fetch()) {
                        $value = $row[$column];
                        if ($value && hasEncodingIssue($value)) {
                            $fixed = fixEncoding($value);

                            // Actualizar el registro
                            $updateSql = "UPDATE `$table` SET `$column` = ? WHERE id = ?";
                            $updateStmt = $pdo->prepare($updateSql);
                            $updateStmt->execute([$fixed, $row['id']]);

                            echo "<p class='success'>✅ Corregido $table.id={$row['id']}: ";
                            echo htmlspecialchars($value) . " → <strong>" . htmlspecialchars($fixed) . "</strong></p>";

                            $totalFixed++;
                        }
                    }
                } catch (Exception $e) {
                    echo "<p class='error'>❌ Error al corregir $table.$column: {$e->getMessage()}</p>";
                }
            }
        }

        echo "<hr>";
        echo "<h2 class='success'>✅ Corrección Completada</h2>";
        echo "<p>Se corrigieron <strong>$totalFixed registros</strong> en total.</p>";

        echo "<a href='test_charset.php' class='button'>🧪 Verificar Resultados</a>";
        echo "<a href='admin/dashboard' class='button'>📊 Ir al Dashboard</a>";
    }

} catch (Exception $e) {
    echo "<p class='error'>❌ Error: " . $e->getMessage() . "</p>";
}

?>

</body>
</html>
