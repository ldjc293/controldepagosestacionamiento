<?php
require_once 'config/database.php';

// Conectar a la BD
$db = Database::getInstance();

echo "<h1>Diagnóstico de Pagos Recientes</h1>";

// 1. Consultar últimos 20 pagos sin filtros complejos
$sql = "SELECT p.id, p.fecha_pago, p.estado_comprobante, p.moneda_pago, p.monto_usd, p.monto_bs,
               p.apartamento_usuario_id, au.usuario_id, au.apartamento_id,
               u.nombre_completo, u.email
        FROM pagos p
        LEFT JOIN apartamento_usuario au ON au.id = p.apartamento_usuario_id
        LEFT JOIN usuarios u ON u.id = au.usuario_id
        ORDER BY p.id DESC
        LIMIT 20";

try {
    $stmt = $db->query($sql);
    $pagos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<table border='1' cellpadding='5' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background-color: #f0f0f0;'>
            <th>ID</th>
            <th>Fecha Pago</th>
            <th>Estado</th>
            <th>Método</th>
            <th>Monto USD</th>
            <th>Usuario</th>
            <th>Email</th>
            <th>Apt. Usuario ID</th>
            <th>Usuario ID</th>
            <th>Apt. ID</th>
          </tr>";

    foreach ($pagos as $pago) {
        $style = "";
        if ($pago['estado_comprobante'] == 'no_aplica') $style = "background-color: #fff3cd;"; // Amarillo claro
        if ($pago['estado_comprobante'] == 'pendiente') $style = "background-color: #d1e7dd;"; // Verde claro

        echo "<tr style='$style'>";
        echo "<td>{$pago['id']}</td>";
        echo "<td>{$pago['fecha_pago']}</td>";
        echo "<td><strong>{$pago['estado_comprobante']}</strong></td>";
        echo "<td>{$pago['moneda_pago']}</td>";
        echo "<td>{$pago['monto_usd']}</td>";
        echo "<td>{$pago['nombre_completo']}</td>";
        echo "<td>{$pago['email']}</td>";
        echo "<td>{$pago['apartamento_usuario_id']}</td>";
        echo "<td>{$pago['usuario_id']}</td>";
        echo "<td>{$pago['apartamento_id']}</td>";
        echo "</tr>";
    }
    echo "</table>";

    echo "<h2>Análisis de Visibilidad para Operador</h2>";
    echo "<ul>";
    echo "<li><strong>Dashboard (Pendientes):</strong> Solo muestra estado 'pendiente'.</li>";
    echo "<li><strong>Historial (Todos):</strong> Muestra todos, pero requiere que el JOIN con usuarios y apartamentos funcione.</li>";
    echo "</ul>";

    echo "<h3>Verificación de JOINs para Historial</h3>";
    foreach ($pagos as $pago) {
        if (empty($pago['nombre_completo'])) {
            echo "<p style='color: red;'>El pago ID {$pago['id']} tiene problemas con el usuario (Usuario ID: {$pago['usuario_id']}). No saldrá en el historial del operador.</p>";
        }
        
        // Verificar apartamento
        if (!empty($pago['apartamento_id'])) {
            $sqlApt = "SELECT id, bloque, numero_apartamento FROM apartamentos WHERE id = ?";
            $stmtApt = $db->prepare($sqlApt);
            $stmtApt->execute([$pago['apartamento_id']]);
            $apt = $stmtApt->fetch();
            
            if (!$apt) {
                echo "<p style='color: red;'>El pago ID {$pago['id']} tiene un Apartamento ID ({$pago['apartamento_id']}) que NO existe en la tabla apartamentos. No saldrá en el historial.</p>";
            }
        } else {
             echo "<p style='color: red;'>El pago ID {$pago['id']} no tiene Apartamento ID válido.</p>";
        }
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
