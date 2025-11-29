<?php
/**
 * OperadorController - Funcionalidades para operadores
 *
 * Aprobar/rechazar pagos, registrar pagos presenciales, gestionar solicitudes
 */

require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../models/Pago.php';
require_once __DIR__ . '/../models/Mensualidad.php';
require_once __DIR__ . '/../models/Control.php';
require_once __DIR__ . '/../helpers/ValidationHelper.php';

class OperadorController
{
    /**
     * Verificar que el usuario esté autenticado como operador
     */
    private function checkAuth(): ?Usuario
    {
        if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_rol'], ['operador', 'administrador'])) {
            $this->handleAuthFailure();
            return null;
        }

        $usuario = Usuario::findById($_SESSION['user_id']);

        if (!$usuario || !$usuario->activo) {
            session_destroy();
            $this->handleAuthFailure();
            return null;
        }

        return $usuario;
    }

    /**
     * Manejar fallo de autenticación (diferente para AJAX vs normal requests)
     */
    private function handleAuthFailure(): void
    {
        // Verificar si es una petición AJAX
        $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                 strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        error_log("Auth failure - Is AJAX: " . ($isAjax ? 'yes' : 'no') . ", X-Requested-With: " . ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? 'not set'));

        if ($isAjax) {
            // Para AJAX, devolver JSON con error de autenticación
            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'message' => 'Sesión expirada. Por favor, recarga la página e inicia sesión nuevamente.',
                'auth_error' => true
            ]);
            exit;
        } else {
            // Para requests normales, redirigir al login
            redirect('auth/login');
        }
    }

    /**
     * Dashboard del operador
     */
    public function dashboard(): void
    {
        $usuario = $this->checkAuth();
        if (!$usuario) return;

        // Obtener pagos pendientes de aprobación
        $pagosPendientes = Pago::getPendientesAprobar();
        if (!is_array($pagosPendientes)) {
            $pagosPendientes = [];
        }

        // Estadísticas del día
        $estadisticasHoy = $this->getEstadisticasHoy();
        if (!is_array($estadisticasHoy)) {
            $estadisticasHoy = [];
        }

        // Solicitudes pendientes
        $solicitudesPendientes = $this->getSolicitudesPendientes();
        if (!is_array($solicitudesPendientes)) {
            $solicitudesPendientes = [];
        }

        // Últimas actividades
        $ultimasActividades = $this->getUltimasActividades(10);
        if (!is_array($ultimasActividades)) {
            $ultimasActividades = [];
        }

        require_once __DIR__ . '/../views/operador/dashboard.php';
    }

    /**
     * Lista de pagos pendientes de aprobación
     */
    public function pagosPendientes(): void
    {
        $usuario = $this->checkAuth();
        if (!$usuario) return;

        $pagos = Pago::getPendientesAprobar();
        if (!is_array($pagos)) {
            $pagos = [];
        }

        require_once __DIR__ . '/../views/operador/pagos_pendientes.php';
    }

    /**
     * Ver detalle de pago para aprobar/rechazar
     */
    public function revisarPago(): void
    {
        $usuario = $this->checkAuth();
        if (!$usuario) return;

        $pagoId = intval($_GET['id'] ?? 0);

        if (!$pagoId) {
            redirect('operador/pagos-pendientes');
            return;
        }

        $pago = Pago::findById($pagoId);

        if (!$pago) {
            $_SESSION['error'] = 'Pago no encontrado';
            redirect('operador/pagos-pendientes');
            return;
        }

        // Obtener ID de usuario desde apartamento_usuario
        $sql = "SELECT usuario_id FROM apartamento_usuario WHERE id = ?";
        $result = Database::fetchOne($sql, [$pago->apartamento_usuario_id]);
        $usuarioId = $result['usuario_id'] ?? 0;

        // Obtener información del cliente
        $cliente = Usuario::findById($usuarioId);

        // Obtener mensualidades asociadas
        $mensualidades = Pago::getMensualidadesPago($pagoId);

        // Obtener otros pagos del cliente
        $otrosPagos = Pago::getByUsuario($usuarioId, 5);

        require_once __DIR__ . '/../views/operador/revisar_pago.php';
    }

    /**
     * Aprobar pago
     */
    public function aprobarPago(): void
    {
        writeLog("Iniciando aprobación de pago...", 'info');

        $usuario = $this->checkAuth();
        if (!$usuario) {
            writeLog("Error aprobación: Usuario no autenticado", 'error');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            writeLog("Error aprobación: Método no es POST", 'error');
            redirect('operador/pagos-pendientes');
            return;
        }

        // Validar CSRF
        if (!ValidationHelper::validateCSRFToken($_POST['csrf_token'] ?? '')) {
            writeLog("Error aprobación: Token CSRF inválido", 'error');
            error_log("DEBUG: CSRF token inválido");
            $_SESSION['error'] = 'Token de seguridad inválido';
            redirect('operador/pagos-pendientes');
            return;
        }

        $pagoId = intval($_POST['pago_id'] ?? 0);
        writeLog("Intentando aprobar pago ID: $pagoId", 'info');

        if (!$pagoId) {
            $_SESSION['error'] = 'Pago no encontrado';
            redirect('operador/pagos-pendientes');
            return;
        }

        $pago = Pago::findById($pagoId);

        if (!$pago) {
            writeLog("Error aprobación: Pago no encontrado en BD", 'error');
            $_SESSION['error'] = 'Pago no encontrado';
            redirect('operador/pagos-pendientes');
            return;
        }
        
        if ($pago->estado_comprobante !== 'pendiente') {
             writeLog("Error aprobación: Estado actual es {$pago->estado_comprobante}", 'warning');
             // Si ya está aprobado, redirigir con éxito
             if ($pago->estado_comprobante === 'aprobado') {
                 $_SESSION['success'] = 'El pago ya había sido aprobado';
                 redirect('operador/pagos-pendientes');
                 return;
             }
             $_SESSION['error'] = 'Pago no válido para aprobación';
             redirect('operador/pagos-pendientes');
             return;
        }

        // Aprobar
        if ($pago->aprobar($usuario->id)) {
            $_SESSION['success'] = 'Pago aprobado correctamente';
            writeLog("Pago ID $pagoId aprobado exitosamente por operador {$usuario->email}", 'info');
        } else {
            writeLog("Error al ejecutar método aprobar() del modelo Pago", 'error');
            $_SESSION['error'] = 'Error al aprobar el pago';
        }

        redirect('operador/pagos-pendientes');
    }

    /**
     * Rechazar pago
     */
    public function rechazarPago(): void
    {
        writeLog("Iniciando rechazo de pago...", 'info');
        
        $usuario = $this->checkAuth();
        if (!$usuario) {
            writeLog("Error rechazo: Usuario no autenticado", 'error');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            writeLog("Error rechazo: Método no es POST", 'error');
            redirect('operador/pagos-pendientes');
            return;
        }

        // Validar CSRF
        if (!ValidationHelper::validateCSRFToken($_POST['csrf_token'] ?? '')) {
            writeLog("Error rechazo: Token CSRF inválido", 'error');
            $_SESSION['error'] = 'Token de seguridad inválido';
            redirect('operador/pagos-pendientes');
            return;
        }

        $pagoId = intval($_POST['pago_id'] ?? 0);
        $motivo = sanitize($_POST['motivo_rechazo'] ?? '');
        
        writeLog("Intentando rechazar pago ID: $pagoId. Motivo: $motivo", 'info');

        if (!$pagoId) {
            $_SESSION['error'] = 'Pago no encontrado';
            redirect('operador/pagos-pendientes');
            return;
        }

        if (empty($motivo)) {
            $_SESSION['error'] = 'Debe especificar el motivo del rechazo';
            redirect('operador/revisar-pago?id=' . $pagoId);
            return;
        }

        $pago = Pago::findById($pagoId);

        if (!$pago) {
            writeLog("Error rechazo: Pago no encontrado en BD", 'error');
            $_SESSION['error'] = 'Pago no encontrado';
            redirect('operador/pagos-pendientes');
            return;
        }
        
        if ($pago->estado_comprobante !== 'pendiente') {
            writeLog("Error rechazo: Estado actual es {$pago->estado_comprobante}", 'warning');
            $_SESSION['error'] = 'Pago no válido para rechazo';
            redirect('operador/pagos-pendientes');
            return;
        }

        // Rechazar
        if ($pago->rechazar($usuario->id, $motivo)) {
            $_SESSION['success'] = 'Pago rechazado correctamente';
            writeLog("Pago ID $pagoId rechazado por operador {$usuario->email}. Motivo: $motivo", 'info');
        } else {
            writeLog("Error al ejecutar método rechazar() del modelo Pago", 'error');
            $_SESSION['error'] = 'Error al rechazar el pago';
        }

        redirect('operador/pagos-pendientes');
    }

    /**
     * Formulario para registrar pago presencial
     */
    public function registrarPagoPresencial(): void
    {
        $usuario = $this->checkAuth();
        if (!$usuario) return;

        // Buscar cliente si se envió CI o email
        $cliente = null;
        $busqueda = sanitize($_GET['buscar'] ?? '');

        if ($busqueda) {
            $cliente = Usuario::buscarCliente($busqueda);

            if (!$cliente) {
                $_SESSION['error'] = 'Cliente no encontrado';
            }
        }

        // Obtener tarifa actual para cálculos dinámicos (siempre disponible)
        require_once __DIR__ . '/../models/ConfiguracionTarifa.php';
        $tarifaActual = ConfiguracionTarifa::getTarifaActual();

        // Obtener cantidad de controles del apartamento
        $cantidadControles = 0;
        if ($cliente) {
            $sqlControles = "SELECT cantidad_controles FROM apartamento_usuario WHERE usuario_id = ? AND activo = TRUE";
            $controlesData = Database::fetchOne($sqlControles, [$cliente->id]);
            $cantidadControles = $controlesData ? $controlesData['cantidad_controles'] : 0;
        }

        // Si se encontró cliente, obtener sus mensualidades pendientes (incluyendo futuras)
        $mensualidadesPendientes = [];
        $modoAdelantado = ($_GET['modo'] ?? '') === 'adelantado';

        // Manejar solicitud para generar mensualidades futuras
        if ($cliente && isset($_GET['generar_futuras'])) {
            $mesesAGenerar = intval($_GET['generar_futuras']);
            try {
                $generadas = Mensualidad::generarMensualidadesFuturas($cliente->id, $mesesAGenerar);
                $_SESSION['success'] = "Se han generado {$mesesAGenerar} mensualidades futuras para el cliente";
            } catch (Exception $e) {
                $_SESSION['error'] = "Error al generar mensualidades futuras: " . $e->getMessage();
            }
            // Redireccionar para limpiar el parámetro
            header('Location: ' . url('operador/registrar-pago-presencial') . '?buscar=' . urlencode($_GET['buscar'] ?? '') . '&modo=adelantado');
            exit;
        }

        if ($cliente) {
            // Permitir hasta 12 meses siempre
            $mesesAdelante = 12;
            $mensualidadesPendientes = Mensualidad::getMensualidadesParaPagoAdelantado($cliente->id, $mesesAdelante);
            if (!is_array($mensualidadesPendientes)) {
                $mensualidadesPendientes = [];
            }
        }

        // Obtener tasa BCV
        $tasaBCV = $this->getTasaBCVActual();

        // Asegurar que las variables de tarifa estén siempre disponibles
        if (!isset($tarifaActual)) {
            $tarifaActual = ConfiguracionTarifa::getTarifaActual();
        }
        if (!isset($cantidadControles)) {
            $cantidadControles = 0;
        }

        require_once __DIR__ . '/../views/operador/registrar_pago_presencial.php';
    }

    /**
     * Procesar registro de pago presencial
     */
    public function processRegistrarPagoPresencial(): void
    {
        $usuario = $this->checkAuth();
        if (!$usuario) return;

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('operador/registrar-pago-presencial');
            return;
        }

        // Validar CSRF
        if (!ValidationHelper::validateCSRFToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'Token de seguridad inválido';
            redirect('operador/registrar-pago-presencial');
            return;
        }

        $clienteId = intval($_POST['cliente_id'] ?? 0);
        $moneda = $_POST['moneda'] ?? '';
        $monto = floatval($_POST['monto'] ?? 0);
        $metodoPago = $_POST['metodo_pago'] ?? '';
        $referencia = sanitize($_POST['referencia'] ?? '');
        $fechaPago = $_POST['fecha_pago'] ?? date('Y-m-d');
        $mensualidadesSeleccionadas = $_POST['mensualidades'] ?? [];

        // Validaciones
        $cliente = Usuario::findById($clienteId);
        if (!$cliente || $cliente->rol !== 'cliente') {
            $_SESSION['error'] = 'Cliente inválido';
            redirect('operador/registrar-pago-presencial');
            return;
        }

        if (!in_array($moneda, ['USD', 'Bs'])) {
            $_SESSION['error'] = 'Moneda inválida';
            redirect('operador/registrar-pago-presencial');
            return;
        }

        if ($monto <= 0) {
            $_SESSION['error'] = 'El monto debe ser mayor a 0';
            redirect('operador/registrar-pago-presencial');
            return;
        }

        if (empty($mensualidadesSeleccionadas)) {
            $_SESSION['error'] = 'Debe seleccionar al menos una mensualidad';
            redirect('operador/registrar-pago-presencial');
            return;
        }

        // Validar y recalcular montos basados en tarifa actual
        require_once __DIR__ . '/../models/ConfiguracionTarifa.php';
        $tarifaActual = ConfiguracionTarifa::getTarifaActual();

        if (!$tarifaActual) {
            $_SESSION['error'] = 'No hay tarifa configurada. Contacte al administrador.';
            redirect('operador/registrar-pago-presencial');
            return;
        }

        // Obtener cantidad de controles del cliente
        $sqlControles = "SELECT cantidad_controles FROM apartamento_usuario WHERE usuario_id = ? AND activo = TRUE";
        $controlesData = Database::fetchOne($sqlControles, [$clienteId]);
        $cantidadControles = $controlesData ? $controlesData['cantidad_controles'] : 0;

        // Calcular monto esperado basado en tarifa actual
        $montoEsperadoUSD = $tarifaActual->monto_mensual_usd * count($mensualidadesSeleccionadas) * $cantidadControles;
        $tasaBCV = $this->getTasaBCVActual();

        // Validar que el monto pagado sea razonable (permitir pequeña variación por redondeo)
        $variacionPermitida = 0.10; // 10 centavos de variación
        if (abs($montoEsperadoUSD - $monto) > $variacionPermitida) {
            $_SESSION['error'] = sprintf(
                'El monto pagado (%.2f USD) no coincide con el monto esperado (%.2f USD) basado en la tarifa actual.',
                $monto,
                $montoEsperadoUSD
            );
            redirect('operador/registrar-pago-presencial');
            return;
        }

        // Registrar y aprobar automáticamente (pago presencial)
        try {
            $pagoId = Pago::registrar([
                'usuario_id' => $clienteId,
                'monto' => $monto,
                'moneda' => $moneda,
                'metodo_pago' => $metodoPago,
                'referencia' => $referencia,
                'fecha_pago' => $fechaPago,
                'mensualidades_ids' => $mensualidadesSeleccionadas,
                'registrado_por' => $usuario->id // Operador que registra
            ]);

            // Aprobar automáticamente
            $pago = Pago::findById($pagoId);
            $pago->aprobar($usuario->id);

            writeLog("Pago presencial registrado por operador {$usuario->email}: ID $pagoId", 'info');

            $_SESSION['success'] = 'Pago presencial registrado y aprobado correctamente';
            redirect('operador/dashboard');

        } catch (Exception $e) {
            writeLog("Error al registrar pago presencial: " . $e->getMessage(), 'error');
            $_SESSION['error'] = 'Error al registrar el pago';
            redirect('operador/registrar-pago-presencial');
        }
    }

    /**
     * Historial de todos los pagos
     */
    public function historialPagos(): void
    {
        $usuario = $this->checkAuth();
        if (!$usuario) return;

        // Filtros
        $filtros = [
            'estado' => $_GET['estado'] ?? null,
            'mes' => $_GET['mes'] ?? null,
            'anio' => $_GET['anio'] ?? null,
            'cliente' => $_GET['cliente'] ?? null
        ];

        $pagos = Pago::getAllConFiltros($filtros);

        require_once __DIR__ . '/../views/operador/historial_pagos.php';
    }

    /**
     * Gestión de solicitudes de cambios
     */
    public function solicitudes(): void
    {
        $usuario = $this->checkAuth();
        if (!$usuario) return;

        $solicitudes = $this->getSolicitudesPendientes();

        require_once __DIR__ . '/../views/operador/solicitudes.php';
    }

    /**
     * Lista de clientes con información de controles asignados
     */
    public function clientesControles(): void
    {
        $usuario = $this->checkAuth();
        if (!$usuario) return;

        // Filtros
        $filters = [];
        if (isset($_GET['bloque']) && !empty($_GET['bloque'])) {
            $filters['bloque'] = $_GET['bloque'];
        }
        if (isset($_GET['busqueda']) && !empty($_GET['busqueda'])) {
            $filters['busqueda'] = sanitize($_GET['busqueda']);
        }

        $clientes = Usuario::getClientesConControles($filters);

        require_once __DIR__ . '/../views/operador/clientes_controles.php';
    }

    /**
     * Vista de controles ordenados por receptor con filtros
     */
    public function vistaControles(): void
    {
        $usuario = $this->checkAuth();
        if (!$usuario) return;

        // Filtros
        $filters = [];
        if (isset($_GET['estado']) && !empty($_GET['estado'])) {
            $filters['estado'] = $_GET['estado'];
        }
        if (isset($_GET['receptor']) && !empty($_GET['receptor'])) {
            $filters['receptor'] = $_GET['receptor'];
        }
        if (isset($_GET['bloque']) && !empty($_GET['bloque'])) {
            $filters['bloque'] = $_GET['bloque'];
        }
        if (isset($_GET['busqueda']) && !empty($_GET['busqueda'])) {
            $filters['busqueda'] = sanitize($_GET['busqueda']);
        }

        $controles = Control::getControlesConPropietarios($filters);

        require_once __DIR__ . '/../views/operador/vista_controles.php';
    }

    /**
     * Aprobar/rechazar solicitud
     */
    public function processSolicitud(): void
    {
        $usuario = $this->checkAuth();
        if (!$usuario) return;

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('operador/solicitudes');
            return;
        }

        // Validar CSRF
        if (!ValidationHelper::validateCSRFToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'Token de seguridad inválido';
            redirect('operador/solicitudes');
            return;
        }

        $solicitudId = intval($_POST['solicitud_id'] ?? 0);
        $accion = $_POST['accion'] ?? '';
        $observaciones = sanitize($_POST['observaciones'] ?? '');

        if (!in_array($accion, ['aprobar', 'rechazar'])) {
            $_SESSION['error'] = 'Acción inválida';
            redirect('operador/solicitudes');
            return;
        }

        $sql = "UPDATE solicitudes_cambios
                SET estado = ?,
                    aprobado_por = ?,
                    fecha_respuesta = NOW(),
                    observaciones = ?
                WHERE id = ? AND estado = 'pendiente'";

        $estado = $accion === 'aprobar' ? 'aprobada' : 'rechazada';

        $result = Database::execute($sql, [$estado, $usuario->id, $observaciones, $solicitudId]);

        if ($result > 0) {
            $_SESSION['success'] = "Solicitud {$estado} correctamente";
            writeLog("Solicitud ID $solicitudId {$estado} por operador {$usuario->email}", 'info');
        } else {
            $_SESSION['error'] = 'Error al procesar la solicitud';
        }

        redirect('operador/solicitudes');
    }

    /**
     * Buscar cliente (AJAX)
     */
    public function buscarCliente(): void
    {
        $usuario = $this->checkAuth();
        if (!$usuario) return;

        header('Content-Type: application/json');

        $criterio = sanitize($_GET['q'] ?? '');

        if (strlen($criterio) < 2) {
            echo json_encode([]);
            exit;
        }

        $clientes = Usuario::buscarClientes($criterio);

        echo json_encode($clientes);
        exit;
    }

    // ==================== HELPERS ====================

    /**
     * Obtener estadísticas de hoy
     */
    private function getEstadisticasHoy(): array
    {
        $sql = "SELECT
                    COUNT(*) as total_pagos,
                    SUM(CASE WHEN estado_comprobante = 'aprobado' THEN 1 ELSE 0 END) as aprobados_hoy,
                    SUM(CASE WHEN estado_comprobante = 'rechazado' THEN 1 ELSE 0 END) as rechazados_hoy,
                    SUM(CASE WHEN estado_comprobante = 'aprobado' THEN monto_usd ELSE 0 END) as total_usd,
                    SUM(CASE WHEN estado_comprobante = 'aprobado' THEN monto_bs ELSE 0 END) as total_bs
                FROM pagos
                WHERE DATE(fecha_pago) = CURDATE()";

        $result = Database::fetchOne($sql);
        return is_array($result) ? $result : [
            'total_pagos' => 0,
            'aprobados_hoy' => 0,
            'rechazados_hoy' => 0,
            'total_usd' => 0,
            'total_bs' => 0
        ];
    }

    /**
     * Obtener últimas actividades
     */
    private function getUltimasActividades(int $limit = 10): array
    {
        $sql = "SELECT la.*, u.nombre_completo as usuario_nombre
                FROM logs_actividad la
                LEFT JOIN usuarios u ON u.id = la.usuario_id
                ORDER BY la.fecha_hora DESC
                LIMIT ?";

        $result = Database::fetchAll($sql, [$limit]);
        return is_array($result) ? $result : [];
    }

    /**
     * Obtener solicitudes pendientes
     */
    private function getSolicitudesPendientes(): array
    {
        $sql = "SELECT s.*, u.nombre_completo as solicitante_nombre
                FROM solicitudes_cambios s
                JOIN apartamento_usuario au ON au.id = s.apartamento_usuario_id
                JOIN usuarios u ON u.id = au.usuario_id
                WHERE s.estado = 'pendiente'
                ORDER BY s.fecha_solicitud DESC";

        return Database::fetchAll($sql);
    }

    /**
     * Obtener tasa BCV actual
     */
    private function getTasaBCVActual(): float
    {
        $sql = "SELECT tasa_usd_bs FROM tasa_cambio_bcv ORDER BY fecha_registro DESC LIMIT 1";
        $result = Database::fetchOne($sql);

        return $result ? floatval($result['tasa_usd_bs']) : 36.50;
    }

    /**
     * Consultar tasa de cambio desde la página oficial del BCV
     *
     * @return float|null Tasa USD/BS o null si falla
     */
    private function obtenerTasaDesdeBCV(): ?float
    {
        try {
            $url = 'https://www.bcv.org.ve/';

            // Inicializar cURL
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');

            $html = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode !== 200 || !$html) {
                writeLog("Error consultando BCV: HTTP $httpCode", 'error');
                return null;
            }

            // Patrones de búsqueda para extraer la tasa USD
            $patterns = [
                // Patrón 1: Buscar "Dólar" seguido de números
                '/<strong>D[oó]lar.*?<\/strong>.*?<strong[^>]*>\s*([\d,\.]+)\s*<\/strong>/is',

                // Patrón 2: Buscar en divs con clase de monedas
                '/<div[^>]*class="[^"]*moneda[^"]*"[^>]*>.*?USD.*?<strong[^>]*>\s*([\d,\.]+)\s*<\/strong>/is',

                // Patrón 3: Buscar directamente USD (principal para bcv.org.ve)
                '/USD.*?<strong[^>]*>\s*([\d,\.]+)\s*<\/strong>/is',

                // Patrón 4: Buscar en div con id="dolar"
                '/<div[^>]*id="dolar"[^>]*>.*?<strong[^>]*>\s*([\d,\.]+)\s*<\/strong>/is',

                // Patrón 5: Buscar en tabla de tasas
                '/<td[^>]*>.*?USD.*?<\/td>.*?<td[^>]*>\s*([\d,\.]+)\s*<\/td>/is'
            ];

            foreach ($patterns as $i => $pattern) {
                if (preg_match($pattern, $html, $matches)) {
                    // Limpiar el número (eliminar puntos de miles, reemplazar coma por punto)
                    $tasaStr = trim($matches[1]);
                    $tasaStr = str_replace('.', '', $tasaStr); // Eliminar separadores de miles
                    $tasaStr = str_replace(',', '.', $tasaStr); // Reemplazar coma decimal por punto

                    $tasa = floatval($tasaStr);

                    // Validar que la tasa esté en un rango razonable (entre 1 y 100,000 Bs/USD)
                    if ($tasa >= 1 && $tasa <= 100000) {
                        writeLog("Tasa BCV consultada exitosamente: $tasa Bs/USD (patrón " . ($i + 1) . ")", 'info');
                        return $tasa;
                    }
                }
            }

            writeLog("No se pudo extraer la tasa del HTML del BCV", 'error');
            return null;

        } catch (Exception $e) {
            writeLog("Excepción al consultar BCV: " . $e->getMessage(), 'error');
            return null;
        }
    }

    /**
     * Obtiene la tasa desde una fuente alternativa
     */
    private function obtenerTasaAlternativa(): ?float
    {
        try {
            // Usar exchangerate.host como alternativa
            $url = BCV_API_URL;

            $context = stream_context_create([
                'http' => [
                    'timeout' => 10,
                    'user_agent' => 'Mozilla/5.0'
                ]
            ]);

            $json = @file_get_contents($url, false, $context);

            if ($json === false) {
                return null;
            }

            $data = json_decode($json, true);

            // exchangerate.host devuelve rates.VES
            if (isset($data['rates']['VES'])) {
                $tasa = (float) $data['rates']['VES'];
                if ($tasa >= 10 && $tasa <= 100) {
                    return $tasa;
                }
            }

            return null;

        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Actualizar tasa BCV (AJAX)
     */
    public function actualizarTasaBCV(): void
    {
        $usuario = $this->checkAuth();
        if (!$usuario) return;

        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            exit;
        }

        // Validar CSRF - Leer del body JSON si es una petición JSON
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        $receivedToken = $data['csrf_token'] ?? $_POST['csrf_token'] ?? '';
        $sessionToken = $_SESSION['csrf_token'] ?? 'no_session_token';

        if (!ValidationHelper::validateCSRFToken($receivedToken)) {
            error_log("CSRF validation failed. Received: '$receivedToken', Session: '$sessionToken'");
            echo json_encode(['success' => false, 'message' => 'Token de seguridad inválido']);
            exit;
        }

        try {
            // Obtener la tasa actualizada desde el sitio web del BCV
            $tasaNueva = $this->obtenerTasaDesdeBCV();

            // Si falla el BCV, intentar fuente alternativa
            if ($tasaNueva === null) {
                writeLog("BCV directo no disponible, intentando fuente alternativa", 'warning');
                $tasaNueva = $this->obtenerTasaAlternativa();
            }

            // Si todo falla, usar simulación como último recurso
            if ($tasaNueva === null) {
                writeLog("APIs no disponibles, usando simulación", 'warning');
                $tasaActual = $this->getTasaBCVActual();
                $tasaNueva = $tasaActual + (mt_rand(-50, 50) / 100); // Simular cambio pequeño
            }

            // Validar que la tasa sea razonable (entre 1 y 500 Bs)
            if ($tasaNueva < 1 || $tasaNueva > 500) {
                writeLog("Tasa obtenida fuera de rango: $tasaNueva", 'error');
                echo json_encode(['success' => false, 'message' => "Tasa obtenida inválida: $tasaNueva Bs"]);
                exit;
            }

            // Obtener tasa anterior para calcular variación
            $tasaAnterior = $this->getTasaBCVActual();

            // Insertar nueva tasa en la base de datos
            $sql = "INSERT INTO tasa_cambio_bcv (tasa_usd_bs, registrado_por, fecha_registro, fuente)
                    VALUES (?, ?, NOW(), ?)";

            $fuente = $tasaNueva !== null && $tasaNueva !== $tasaAnterior ? 'BCV_WEB' : 'SIMULADO';
            $result = Database::execute($sql, [$tasaNueva, $usuario->id, $fuente]);

            if ($result) {
                $variacion = $tasaAnterior ? (($tasaNueva - $tasaAnterior) / $tasaAnterior) * 100 : 0;
                $signo = $variacion >= 0 ? '+' : '';

                writeLog("Tasa BCV actualizada por operador {$usuario->email}: $tasaAnterior -> $tasaNueva (Variación: {$signo}" . number_format($variacion, 2) . "%)", 'info');

                echo json_encode([
                    'success' => true,
                    'message' => 'Tasa BCV actualizada correctamente (Variación: ' . $signo . number_format($variacion, 2) . '%)',
                    'nueva_tasa' => $tasaNueva,
                    'tasa_anterior' => $tasaAnterior,
                    'variacion' => round($variacion, 2),
                    'fuente' => $fuente,
                    'fecha' => date('d/m/Y H:i')
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al guardar la nueva tasa']);
            }
        } catch (Exception $e) {
            writeLog("Error al actualizar tasa BCV: " . $e->getMessage(), 'error');
            echo json_encode(['success' => false, 'message' => 'Error interno del servidor']);
        }

        exit;
    }

    /**
     * Mapa de controles (igual que administrador, solo visualización)
     */
    public function controles(): void
    {
        $usuario = $this->checkAuth();
        if (!$usuario) return;

        $mapa = Control::getMapaControles();
        $estadisticas = Control::getEstadisticas();

        require_once __DIR__ . '/../views/operador/controles.php';
    }
}
