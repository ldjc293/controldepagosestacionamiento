<?php
/**
 * Acceso directo al dashboard del operador (sin autenticación)
 * Para pruebas de diagnóstico
 */

// Forzar sesión de operador
session_start();
$_SESSION['user_id'] = 2;
$_SESSION['user_rol'] = 'operador';
$_SESSION['user_email'] = 'operador@estacionamiento.com';
$_SESSION['user_nombre'] = 'Operador Principal';

// Cargar configuración
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/app/models/Usuario.php';
require_once __DIR__ . '/app/models/Pago.php';
require_once __DIR__ . '/app/models/Mensualidad.php';
require_once __DIR__ . '/app/models/Control.php';
require_once __DIR__ . '/app/helpers/ValidationHelper.php';

// Simular el controlador
class OperadorControllerTest {
    public function dashboardDirect(): void
    {
        // Simular las variables que el controlador debería proveer
        $pagosPendientes = Pago::getPendientesAprobar();
        $estadisticasHoy = $this->getEstadisticasHoyDirect();
        $ultimasActividades = $this->getUltimasActividadesDirect(10);

        echo "<!DOCTYPE html>";
        echo "<html lang='es'>";
        echo "<head>";
        echo "<meta charset='UTF-8'>";
        echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
        echo "<title>Dashboard Operador - Acceso Directo</title>";
        echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>";
        echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css' rel='stylesheet'>";
        echo "<style>";
        echo ".stat-card { padding: 20px; border-radius: 8px; background: white; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 20px; }";
        echo ".stat-card .icon { font-size: 24px; width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 10px; }";
        echo ".stat-card .value { font-size: 32px; font-weight: bold; margin-bottom: 5px; }";
        echo ".stat-card .label { color: #6c757d; font-size: 14px; }";
        echo ".main-content { padding: 20px; }";
        echo "</style>";
        echo "</head>";
        echo "<body>";
        echo "<div class='container-fluid'>";
        echo "<h1>Dashboard Operador - Acceso Directo</h1>";
        echo "<p style='color: orange;'><i class='bi bi-info-circle'></i> Modo de diagnóstico - Acceso directo sin autenticación</p>";

        // Estadísticas del Día
        echo "<div class='row mb-4'>";
        echo "<div class='col-md-3'>";
        echo "<div class='stat-card'>";
        echo "<div class='icon' style='background: rgba(245, 158, 11, 0.1); color: #f59e0b;'>";
        echo "<i class='bi bi-hourglass-split'></i>";
        echo "</div>";
        echo "<div class='value'>" . count($pagosPendientes ?? []) . "</div>";
        echo "<div class='label'>Pagos Pendientes</div>";
        echo "</div>";
        echo "</div>";

        echo "<div class='col-md-3'>";
        echo "<div class='stat-card'>";
        echo "<div class='icon' style='background: rgba(16, 185, 129, 0.1); color: #10b981;'>";
        echo "<i class='bi bi-check-circle'></i>";
        echo "</div>";
        echo "<div class='value'>" . ($estadisticasHoy['aprobados_hoy'] ?? 0) . "</div>";
        echo "<div class='label'>Aprobados Hoy</div>";
        echo "</div>";
        echo "</div>";

        echo "<div class='col-md-3'>";
        echo "<div class='stat-card'>";
        echo "<div class='icon' style='background: rgba(239, 68, 68, 0.1); color: #ef4444;'>";
        echo "<i class='bi bi-x-circle'></i>";
        echo "</div>";
        echo "<div class='value'>" . ($estadisticasHoy['rechazados_hoy'] ?? 0) . "</div>";
        echo "<div class='label'>Rechazados Hoy</div>";
        echo "</div>";
        echo "</div>";

        echo "<div class='col-md-3'>";
        echo "<div class='stat-card'>";
        echo "<div class='icon' style='background: rgba(59, 130, 246, 0.1); color: #3b82f6;'>";
        echo "<i class='bi bi-cash-stack'></i>";
        echo "</div>";
        echo "<div class='value'>$" . ($estadisticasHoy['total_usd'] ?? 0) . "</div>";
        echo "<div class='label'>Total USD Hoy</div>";
        echo "</div>";
        echo "</div>";
        echo "</div>";

        // Acciones rápidas
        echo "<div class='row mb-4'>";
        echo "<div class='col-12'>";
        echo "<div class='card'>";
        echo "<div class='card-header'>";
        echo "<h5 class='mb-0'>Acciones Rápidas</h5>";
        echo "</div>";
        echo "<div class='card-body'>";
        echo "<a href='" . url('operador/pagos-pendientes') . "' class='btn btn-warning me-2'>";
        echo "<i class='bi bi-eye'></i> Ver Pagos Pendientes";
        echo "</a>";
        echo "<a href='" . url('operador/registrar-pago-presencial') . "' class='btn btn-success me-2'>";
        echo "<i class='bi bi-cash-stack'></i> Registrar Pago";
        echo "</a>";
        echo "<a href='" . url('auth/logout') . "' class='btn btn-secondary'>";
        echo "<i class='bi bi-box-arrow-right'></i> Cerrar Sesión";
        echo "</a>";
        echo "</div>";
        echo "</div>";
        echo "</div>";

        // Información de depuración
        echo "<div class='row'>";
        echo "<div class='col-12'>";
        echo "<div class='card'>";
        echo "<div class='card-header'>";
        echo "<h5 class='mb-0'>Información de Depuración</h5>";
        echo "</div>";
        echo "<div class='card-body'>";
        echo "<table class='table table-sm'>";
        echo "<tr><th>Variable</th><th>Valor</th></tr>";
        echo "<tr><td>Sesión User ID</td><td>" . ($_SESSION['user_id'] ?? 'No set') . "</td></tr>";
        echo "<tr><td>Sesión Rol</td><td>" . ($_SESSION['user_rol'] ?? 'No set') . "</td></tr>";
        echo "<tr><td>Pagos Pendientes</td><td>" . count($pagosPendientes ?? []) . "</td></tr>";
        echo "<tr><td>Estadísticas Hoy</td><td>" . (count($estadisticasHoy ?? []) > 0 ? 'Array' : 'Vacío') . "</td></tr>";
        echo "<tr><td>Actividades</td><td>" . count($ultimasActividades ?? []) . "</td></tr>";
        echo "</table>";
        echo "</div>";
        echo "</div>";
        echo "</div>";

        echo "</div>";
        echo "</body>";
        echo "</html>";
    }

    private function getEstadisticasHoyDirect(): array
    {
        try {
            $sql = "SELECT
                        COUNT(*) as total_pagos,
                        SUM(CASE WHEN estado_comprobante = 'aprobado' THEN 1 ELSE 0 END) as aprobados_hoy,
                        SUM(CASE WHEN estado_comprobante = 'rechazado' THEN 1 ELSE 0 END) as rechazados_hoy,
                        SUM(CASE WHEN estado_comprobante = 'aprobado' THEN monto_usd ELSE 0 END) as total_usd,
                        SUM(CASE WHEN estado_comprobante = 'aprobado' THEN monto_bs ELSE 0 END) as total_bs
                    FROM pagos
                    WHERE DATE(fecha_pago) = CURDATE()";

            return Database::fetchOne($sql) ?: [];
        } catch (Exception $e) {
            error_log("Error en getEstadisticasHoyDirect: " . $e->getMessage());
            return [];
        }
    }

    private function getUltimasActividadesDirect(int $limit = 10): array
    {
        try {
            $sql = "SELECT * FROM logs_actividad
                    ORDER BY fecha_hora DESC
                    LIMIT ?";

            return Database::fetchAll($sql, [$limit]) ?: [];
        } catch (Exception $e) {
            error_log("Error en getUltimasActividadesDirect: " . $e->getMessage());
            return [];
        }
    }
}

// Ejecutar dashboard directo
$controller = new OperadorControllerTest();
$controller->dashboardDirect();
?>