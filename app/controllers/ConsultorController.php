<?php
/**
 * ConsultorController - Funcionalidades para consultores
 *
 * Reportes, estadísticas, consultas (solo lectura)
 */

require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../models/Pago.php';
require_once __DIR__ . '/../models/Mensualidad.php';
require_once __DIR__ . '/../models/Control.php';
require_once __DIR__ . '/../models/Apartamento.php';

class ConsultorController
{
    /**
     * Verificar que el usuario esté autenticado como consultor
     */
    private function checkAuth(): ?Usuario
    {
        if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_rol'], ['consultor', 'administrador'])) {
            redirect('auth/login');
            return null;
        }

        $usuario = Usuario::findById($_SESSION['user_id']);

        if (!$usuario || !$usuario->activo) {
            session_destroy();
            redirect('auth/login');
            return null;
        }

        return $usuario;
    }

    /**
     * Dashboard del consultor
     */
    public function dashboard(): void
    {
        $usuario = $this->checkAuth();
        if (!$usuario) return;

        // Estadísticas generales
        $estadisticasGenerales = $this->getEstadisticasGenerales();

        // Estadísticas del mes actual
        $estadisticasMes = Pago::getEstadisticasMes(date('n'), date('Y'));

        // Morosidad
        $morosidad = $this->getEstadisticasMorosidad();

        // Controles
        $estadisticasControles = Control::getEstadisticas();

        require_once __DIR__ . '/../views/consultor/dashboard.php';
    }

    /**
     * Reporte de morosidad
     */
    public function reporteMorosidad(): void
    {
        $usuario = $this->checkAuth();
        if (!$usuario) return;

        // Filtros
        $mesesMinimos = intval($_GET['meses'] ?? 1);
        $bloque = sanitize($_GET['bloque'] ?? '');

        $sql = "SELECT
                    u.id,
                    u.nombre_completo,
                    u.email,
                    u.telefono,
                    u.cedula,
                    a.bloque,
                    a.numero_apartamento,
                    COUNT(m.id) as meses_vencidos,
                    SUM(m.monto_usd) as deuda_total_usd,
                    MIN(m.mes_correspondiente) as primera_mensualidad_vencida,
                    MAX(m.mes_correspondiente) as ultima_mensualidad_vencida
                FROM usuarios u
                JOIN apartamento_usuario au ON au.usuario_id = u.id AND au.activo = TRUE
                JOIN apartamentos a ON a.id = au.apartamento_id
                JOIN mensualidades m ON m.apartamento_usuario_id = au.id AND m.estado = 'vencida'
                WHERE 1=1";

        $params = [];

        if ($bloque) {
            $sql .= " AND a.bloque = ?";
            $params[] = $bloque;
        }

        $sql .= " GROUP BY u.id, a.id
                  HAVING COUNT(m.id) >= ?
                  ORDER BY meses_vencidos DESC, deuda_total_usd DESC";

        $params[] = $mesesMinimos;

        $morosos = Database::fetchAll($sql, $params);

        // Resumen
        $resumen = [
            'total_morosos' => count($morosos),
            'deuda_total' => array_sum(array_column($morosos, 'deuda_total_usd')),
            'promedio_meses' => count($morosos) > 0 ? array_sum(array_column($morosos, 'meses_vencidos')) / count($morosos) : 0
        ];

        require_once __DIR__ . '/../views/consultor/reporte_morosidad.php';
    }

    /**
     * Reporte de pagos
     */
    public function reportePagos(): void
    {
        $usuario = $this->checkAuth();
        if (!$usuario) return;

        // Filtros
        $mesInicio = $_GET['mes_inicio'] ?? date('Y-m-01');
        $mesFin = $_GET['mes_fin'] ?? date('Y-m-t');
        $estado = $_GET['estado'] ?? null;
        $moneda = $_GET['moneda'] ?? null;

        $sql = "SELECT
                    p.*,
                    u.nombre_completo as cliente_nombre,
                    u.cedula as cliente_cedula,
                    op.nombre_completo as operador_nombre
                FROM pagos p
                JOIN usuarios u ON u.id = p.usuario_id
                LEFT JOIN usuarios op ON op.id = p.aprobado_por
                WHERE DATE(p.fecha_pago) BETWEEN ? AND ?";

        $params = [$mesInicio, $mesFin];

        if ($estado) {
            $sql .= " AND p.estado = ?";
            $params[] = $estado;
        }

        if ($moneda) {
            $sql .= " AND p.moneda = ?";
            $params[] = $moneda;
        }

        $sql .= " ORDER BY p.fecha_pago DESC, p.id DESC";

        $pagos = Database::fetchAll($sql, $params);

        // Estadísticas del período
        $estadisticas = [
            'total_pagos' => count($pagos),
            'total_usd' => 0,
            'total_bs' => 0,
            'aprobados' => 0,
            'rechazados' => 0,
            'pendientes' => 0
        ];

        foreach ($pagos as $pago) {
            if ($pago['estado'] === 'aprobado') {
                if ($pago['moneda'] === 'USD') {
                    $estadisticas['total_usd'] += $pago['monto'];
                } else {
                    $estadisticas['total_bs'] += $pago['monto'];
                }
                $estadisticas['aprobados']++;
            } elseif ($pago['estado'] === 'rechazado') {
                $estadisticas['rechazados']++;
            } else {
                $estadisticas['pendientes']++;
            }
        }

        require_once __DIR__ . '/../views/consultor/reporte_pagos.php';
    }

    /**
     * Reporte de controles
     */
    public function reporteControles(): void
    {
        $usuario = $this->checkAuth();
        if (!$usuario) return;

        // Filtros
        $estado = $_GET['estado'] ?? null;
        $receptor = $_GET['receptor'] ?? null;

        $filtros = [];
        if ($estado) $filtros['estado'] = $estado;
        if ($receptor) $filtros['receptor'] = $receptor;

        $controles = Control::getAll($filtros);

        // Estadísticas
        $estadisticas = Control::getEstadisticas();

        // Agrupar por estado
        $controlsPorEstado = [];
        foreach ($controles as $control) {
            $estado = $control['estado'];
            if (!isset($controlsPorEstado[$estado])) {
                $controlsPorEstado[$estado] = [];
            }
            $controlsPorEstado[$estado][] = $control;
        }

        require_once __DIR__ . '/../views/consultor/reporte_controles.php';
    }

    /**
     * Reporte de apartamentos
     */
    public function reporteApartamentos(): void
    {
        $usuario = $this->checkAuth();
        if (!$usuario) return;

        // Obtener todos los apartamentos con información de residentes
        $apartamentos = Apartamento::getAllWithResidentes();

        // Estadísticas
        $estadisticas = Apartamento::getEstadisticas();

        require_once __DIR__ . '/../views/consultor/reporte_apartamentos.php';
    }

    /**
     * Reporte financiero mensual
     */
    public function reporteFinanciero(): void
    {
        $usuario = $this->checkAuth();
        if (!$usuario) return;

        $mes = intval($_GET['mes'] ?? date('n'));
        $anio = intval($_GET['anio'] ?? date('Y'));

        // Estadísticas del mes
        $estadisticas = Pago::getEstadisticasMes($mes, $anio);

        // Ingresos por método de pago
        $sql = "SELECT
                    moneda_pago as metodo_pago,
                    COUNT(*) as cantidad,
                    SUM(monto_usd) as total_usd,
                    SUM(monto_bs) as total_bs
                FROM pagos
                WHERE MONTH(fecha_pago) = ? AND YEAR(fecha_pago) = ?
                  AND estado_comprobante = 'aprobado'
                GROUP BY moneda_pago";

        $ingresosPorMetodo = Database::fetchAll($sql, [$mes, $anio]);

        // Ingresos diarios del mes
        $sql = "SELECT
                    DAY(fecha_pago) as dia,
                    SUM(monto_usd) as total_usd,
                    SUM(monto_bs) as total_bs,
                    COUNT(*) as cantidad
                FROM pagos
                WHERE MONTH(fecha_pago) = ? AND YEAR(fecha_pago) = ?
                  AND estado_comprobante = 'aprobado'
                GROUP BY DAY(fecha_pago)
                ORDER BY dia";

        $ingresosDiarios = Database::fetchAll($sql, [$mes, $anio]);

        require_once __DIR__ . '/../views/consultor/reporte_financiero.php';
    }

    /**
     * Exportar reporte a Excel
     */
    public function exportarExcel(): void
    {
        $usuario = $this->checkAuth();
        if (!$usuario) return;

        $tipo = $_GET['tipo'] ?? '';

        switch ($tipo) {
            case 'morosidad':
                $this->exportarMorosidadExcel();
                break;
            case 'pagos':
                $this->exportarPagosExcel();
                break;
            case 'controles':
                $this->exportarControlesExcel();
                break;
            default:
                $_SESSION['error'] = 'Tipo de reporte inválido';
                redirect('consultor/dashboard');
        }
    }

    /**
     * Buscar cliente/apartamento
     */
    public function buscar(): void
    {
        $usuario = $this->checkAuth();
        if (!$usuario) return;

        $criterio = sanitize($_GET['q'] ?? '');
        $resultados = [];

        if (strlen($criterio) >= 2) {
            // Buscar clientes
            $clientes = Usuario::buscarClientes($criterio);

            // Buscar apartamentos
            $apartamentos = Apartamento::buscar($criterio);

            // Buscar controles
            $controles = Control::buscar($criterio);

            $resultados = [
                'clientes' => $clientes,
                'apartamentos' => $apartamentos,
                'controles' => $controles
            ];
        }

        require_once __DIR__ . '/../views/consultor/buscar.php';
    }

    /**
     * Ver detalle de cliente (solo lectura)
     */
    public function verCliente(): void
    {
        $usuario = $this->checkAuth();
        if (!$usuario) return;

        $clienteId = intval($_GET['id'] ?? 0);

        if (!$clienteId) {
            redirect('consultor/dashboard');
            return;
        }

        $cliente = Usuario::findById($clienteId);

        if (!$cliente || $cliente->rol !== 'cliente') {
            $_SESSION['error'] = 'Cliente no encontrado';
            redirect('consultor/dashboard');
            return;
        }

        // Información del cliente
        $deudaInfo = Mensualidad::calcularDeudaTotal($clienteId);
        $mensualidades = Mensualidad::getAllByUsuario($clienteId);
        $pagos = Pago::getByUsuario($clienteId);
        $controles = $this->getControlesUsuario($clienteId);

        require_once __DIR__ . '/../views/consultor/ver_cliente.php';
    }

    // ==================== HELPERS ====================

    /**
     * Obtener estadísticas generales
     */
    private function getEstadisticasGenerales(): array
    {
        $sql = "SELECT
                    (SELECT COUNT(*) FROM usuarios WHERE rol = 'cliente' AND activo = TRUE) as total_clientes,
                    (SELECT COUNT(*) FROM apartamentos WHERE activo = TRUE) as total_apartamentos,
                    (SELECT COUNT(*) FROM controles_estacionamiento WHERE estado = 'activo') as controles_activos,
                    (SELECT COUNT(*) FROM controles_estacionamiento WHERE estado = 'bloqueado') as controles_bloqueados,
                    (SELECT COUNT(*) FROM mensualidades WHERE estado = 'vencida') as mensualidades_vencidas,
                    (SELECT COUNT(*) FROM pagos WHERE estado_comprobante = 'pendiente') as pagos_pendientes";

        return Database::fetchOne($sql) ?: [];
    }

    /**
     * Obtener estadísticas de morosidad
     */
    private function getEstadisticasMorosidad(): array
    {
        $sql = "SELECT
                    COUNT(DISTINCT usuario_id) as total_morosos,
                    SUM(monto_usd) as deuda_total,
                    AVG(meses_vencidos) as promedio_meses
                FROM (
                    SELECT
                        au.usuario_id,
                        COUNT(m.id) as meses_vencidos,
                        SUM(m.monto_usd) as monto_usd
                    FROM mensualidades m
                    JOIN apartamento_usuario au ON au.id = m.apartamento_usuario_id
                    WHERE m.estado = 'vencida'
                    GROUP BY au.usuario_id
                ) as morosos";

        return Database::fetchOne($sql) ?: [];
    }

    /**
     * Obtener controles del usuario
     */
    private function getControlesUsuario(int $usuarioId): array
    {
        $sql = "SELECT c.*, a.bloque, a.numero_apartamento
                FROM controles_estacionamiento c
                JOIN apartamento_usuario au ON au.id = c.apartamento_usuario_id
                JOIN apartamentos a ON a.id = au.apartamento_id
                WHERE au.usuario_id = ? AND au.activo = TRUE
                ORDER BY c.posicion_numero, c.receptor";

        return Database::fetchAll($sql, [$usuarioId]);
    }

    /**
     * Exportar morosidad a Excel
     */
    private function exportarMorosidadExcel(): void
    {
        // TODO: Implementar con PHPSpreadsheet
        $_SESSION['info'] = 'Función de exportación en desarrollo';
        redirect('consultor/reporte-morosidad');
    }

    /**
     * Exportar pagos a Excel
     */
    private function exportarPagosExcel(): void
    {
        // TODO: Implementar con PHPSpreadsheet
        $_SESSION['info'] = 'Función de exportación en desarrollo';
        redirect('consultor/reporte-pagos');
    }

    /**
     * Exportar controles a Excel
     */
    private function exportarControlesExcel(): void
    {
        // TODO: Implementar con PHPSpreadsheet
        $_SESSION['info'] = 'Función de exportación en desarrollo';
        redirect('consultor/reporte-controles');
    }
}
