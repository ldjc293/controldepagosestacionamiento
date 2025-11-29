<?php
/**
 * ClienteController - Funcionalidades para residentes
 *
 * Dashboard, estado de cuenta, registro de pagos, historial
 */

require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../models/Mensualidad.php';
require_once __DIR__ . '/../models/Pago.php';
require_once __DIR__ . '/../models/Control.php';
require_once __DIR__ . '/../models/Apartamento.php';
require_once __DIR__ . '/../helpers/ValidationHelper.php';

class ClienteController
{
    /**
     * Verificar que el usuario esté autenticado como cliente
     */
    private function checkAuth(): ?Usuario
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_rol'] !== 'cliente') {
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
     * Dashboard del cliente
     */
    public function dashboard(): void
    {
        $usuario = $this->checkAuth();
        if (!$usuario) return;

        // Obtener mensualidades pendientes
        $mensualidadesPendientes = Mensualidad::getPendientesByUsuario($usuario->id);

        // Calcular deuda total
        $deudaInfo = Mensualidad::calcularDeudaTotal($usuario->id);

        // Obtener últimos 5 pagos
        $ultimosPagos = Pago::getByUsuario($usuario->id, 5);

        // Obtener controles asignados
        $controles = $this->getControlesUsuario($usuario->id);

        // Verificar si tiene pagos pendientes de aprobación
        $pagosPendientesAprobacion = Pago::getPendientesByUsuario($usuario->id);

        // Obtener notificaciones no leídas
        $notificaciones = $this->getNotificacionesNoLeidas($usuario->id);

        require_once __DIR__ . '/../views/cliente/dashboard.php';
    }

    /**
     * Estado de cuenta detallado
     */
    public function estadoCuenta(): void
    {
        $usuario = $this->checkAuth();
        if (!$usuario) return;

        // Obtener todas las mensualidades
        $mensualidades = Mensualidad::getAllByUsuario($usuario->id);

        // Obtener todos los pagos
        $pagos = Pago::getByUsuario($usuario->id);

        // Calcular estadísticas
        $deudaInfo = Mensualidad::calcularDeudaTotal($usuario->id);

        require_once __DIR__ . '/../views/cliente/estado_cuenta.php';
    }

    /**
     * Formulario para registrar nuevo pago
     */
    public function registrarPago(): void
    {
        $usuario = $this->checkAuth();
        if (!$usuario) return;

        // Obtener mensualidades pendientes
        $mensualidadesPendientes = Mensualidad::getPendientesByUsuario($usuario->id);

        if (empty($mensualidadesPendientes)) {
            $_SESSION['info'] = 'No tienes mensualidades pendientes';
            redirect('cliente/dashboard');
            return;
        }

        // Obtener tasa BCV actual
        $tasaBCV = $this->getTasaBCVActual();

        require_once __DIR__ . '/../views/cliente/registrar_pago.php';
    }

    /**
     * Procesar registro de pago
     */
    public function processRegistrarPago(): void
    {
        $usuario = $this->checkAuth();
        if (!$usuario) return;

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('cliente/registrar-pago');
            return;
        }

        // Validar CSRF
        if (!ValidationHelper::validateCSRFToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'Token de seguridad inválido';
            redirect('cliente/registrar-pago');
            return;
        }

        $moneda = $_POST['moneda'] ?? '';
        $monto = floatval($_POST['monto'] ?? 0);
        $metodoPago = $_POST['metodo_pago'] ?? '';
        $referencia = sanitize($_POST['referencia'] ?? '');
        $fechaPago = $_POST['fecha_pago'] ?? date('Y-m-d');
        $mensualidadesSeleccionadas = $_POST['mensualidades'] ?? [];

        // Validaciones
        if (empty($moneda) || !in_array($moneda, ['USD', 'Bs'])) {
            $_SESSION['error'] = 'Moneda inválida';
            redirect('cliente/registrar-pago');
            return;
        }

        if ($monto <= 0) {
            $_SESSION['error'] = 'El monto debe ser mayor a 0';
            redirect('cliente/registrar-pago');
            return;
        }

        if (empty($metodoPago)) {
            $_SESSION['error'] = 'Debe seleccionar un método de pago';
            redirect('cliente/registrar-pago');
            return;
        }

        if (empty($mensualidadesSeleccionadas)) {
            $_SESSION['error'] = 'Debe seleccionar al menos una mensualidad';
            redirect('cliente/registrar-pago');
            return;
        }

        // Validar archivo de comprobante si fue subido
        $rutaComprobante = null;
        if (isset($_FILES['comprobante']) && $_FILES['comprobante']['error'] !== UPLOAD_ERR_NO_FILE) {
            $validacion = ValidationHelper::validateFile(
                $_FILES['comprobante'],
                ['jpg', 'jpeg', 'png', 'pdf'],
                5 * 1024 * 1024 // 5MB
            );

            if (!$validacion['valid']) {
                $_SESSION['error'] = implode('<br>', $validacion['errors']);
                redirect('cliente/registrar-pago');
                return;
            }

            // Subir archivo
            $rutaComprobante = $this->uploadComprobante($_FILES['comprobante'], $usuario->id);

            if (!$rutaComprobante) {
                $_SESSION['error'] = 'Error al subir el comprobante';
                redirect('cliente/registrar-pago');
                return;
            }
        }

        // Obtener apartamento_usuario_id del cliente
        $sqlApartamento = "SELECT id FROM apartamento_usuario WHERE usuario_id = ? AND activo = TRUE LIMIT 1";
        $apartamentoData = Database::fetchOne($sqlApartamento, [$usuario->id]);
        $apartamentoUsuarioId = $apartamentoData['id'] ?? null;

        if (!$apartamentoUsuarioId) {
            $_SESSION['error'] = 'No se encontró información del apartamento';
            redirect('cliente/registrar-pago');
            return;
        }

        // Registrar pago
        try {
            $pagoId = Pago::registrar([
                'apartamento_usuario_id' => $apartamentoUsuarioId,
                'monto' => $monto,
                'moneda' => $moneda,
                'metodo_pago' => $metodoPago,
                'referencia' => $referencia,
                'fecha_pago' => $fechaPago,
                'comprobante_ruta' => $rutaComprobante,
                'mensualidades_ids' => $mensualidadesSeleccionadas,
                'registrado_por' => $usuario->id
            ]);

            writeLog("Pago registrado por cliente {$usuario->email}: ID $pagoId, Monto: $monto $moneda", 'info');

            $_SESSION['success'] = 'Pago registrado correctamente. Será revisado por un operador';
            redirect('cliente/historial-pagos');

        } catch (Exception $e) {
            writeLog("Error al registrar pago: " . $e->getMessage(), 'error');
            $_SESSION['error'] = 'Error al registrar el pago. Intente nuevamente';
            redirect('cliente/registrar-pago');
        }
    }

    /**
     * Historial de pagos del cliente
     */
    public function historialPagos(): void
    {
        $usuario = $this->checkAuth();
        if (!$usuario) return;

        // Filtros
        $filtros = [
            'estado' => $_GET['estado'] ?? null,
            'mes' => $_GET['mes'] ?? null,
            'anio' => $_GET['anio'] ?? null
        ];

        $pagos = Pago::getByUsuarioConFiltros($usuario->id, $filtros);

        require_once __DIR__ . '/../views/cliente/historial_pagos.php';
    }

    /**
     * Ver detalle de un pago
     */
    public function verPago(): void
    {
        $usuario = $this->checkAuth();
        if (!$usuario) return;

        $pagoId = intval($_GET['id'] ?? 0);

        if (!$pagoId) {
            redirect('cliente/historial-pagos');
            return;
        }

        $pago = Pago::findById($pagoId);

        if (!$pago || $pago->usuario_id != $usuario->id) {
            $_SESSION['error'] = 'Pago no encontrado';
            redirect('cliente/historial-pagos');
            return;
        }

        // Obtener mensualidades asociadas
        $mensualidades = Pago::getMensualidadesPago($pagoId);

        require_once __DIR__ . '/../views/cliente/ver_pago.php';
    }

    /**
     * Descargar recibo de pago (PDF)
     */
    public function descargarRecibo(): void
    {
        $usuario = $this->checkAuth();
        if (!$usuario) return;

        $pagoId = intval($_GET['id'] ?? 0);

        if (!$pagoId) {
            redirect('cliente/historial-pagos');
            return;
        }

        $pago = Pago::findById($pagoId);

        if (!$pago || $pago->usuario_id != $usuario->id) {
            $_SESSION['error'] = 'Pago no encontrado';
            redirect('cliente/historial-pagos');
            return;
        }

        if ($pago->estado !== 'aprobado') {
            $_SESSION['error'] = 'Solo se pueden descargar recibos de pagos aprobados';
            redirect('cliente/ver-pago?id=' . $pagoId);
            return;
        }

        // Generar PDF
        $rutaPdf = $pago->generarRecibo();

        if (!$rutaPdf || !file_exists($rutaPdf)) {
            $_SESSION['error'] = 'Error al generar el recibo';
            redirect('cliente/ver-pago?id=' . $pagoId);
            return;
        }

        // Descargar
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . basename($rutaPdf) . '"');
        header('Content-Length: ' . filesize($rutaPdf));
        readfile($rutaPdf);
        exit;
    }

    /**
     * Ver controles asignados
     */
    public function controles(): void
    {
        $usuario = $this->checkAuth();
        if (!$usuario) return;

        $controles = $this->getControlesUsuario($usuario->id);

        // Obtener información de los apartamentos
        $apartamentos = [];
        foreach ($controles as $control) {
            if (!isset($apartamentos[$control['apartamento_usuario_id']])) {
                $sql = "SELECT a.*, au.cantidad_controles
                        FROM apartamentos a
                        JOIN apartamento_usuario au ON au.apartamento_id = a.id
                        WHERE au.id = ?";
                $apartamento = Database::fetchOne($sql, [$control['apartamento_usuario_id']]);
                $apartamentos[$control['apartamento_usuario_id']] = $apartamento;
            }
        }

        require_once __DIR__ . '/../views/cliente/controles.php';
    }

    /**
     * Perfil del usuario
     */
    public function perfil(): void
    {
        $usuario = $this->checkAuth();
        if (!$usuario) return;

        // Obtener información del apartamento
        $sql = "SELECT a.bloque, a.piso, a.numero_apartamento
                FROM apartamento_usuario au
                JOIN apartamentos a ON a.id = au.apartamento_id
                WHERE au.usuario_id = ? AND au.activo = 1
                LIMIT 1";
        $apartamento = Database::fetchOne($sql, [$usuario->id]);

        // Obtener controles asignados
        $sql = "SELECT ce.numero_control_completo, ce.estado, ce.fecha_asignacion
                FROM apartamento_usuario au
                LEFT JOIN controles_estacionamiento ce ON ce.apartamento_usuario_id = au.id
                WHERE au.usuario_id = ? AND au.activo = 1
                ORDER BY ce.numero_control_completo";
        $controles = Database::fetchAll($sql, [$usuario->id]);

        // Filtrar controles válidos (que no sean NULL)
        $controles = array_filter($controles, function($c) {
            return !empty($c['numero_control_completo']);
        });

        require_once __DIR__ . '/../views/cliente/perfil.php';
    }

    /**
     * Actualizar perfil
     */
    public function updatePerfil(): void
    {
        $usuario = $this->checkAuth();
        if (!$usuario) return;

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('cliente/perfil');
            return;
        }

        // Validar CSRF
        if (!ValidationHelper::validateCSRFToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'Token de seguridad inválido';
            redirect('cliente/perfil');
            return;
        }

        $telefono = sanitize($_POST['telefono'] ?? '');
        $direccion = sanitize($_POST['direccion'] ?? '');

        // Validar teléfono
        if (!empty($telefono) && !ValidationHelper::validatePhone($telefono)) {
            $_SESSION['error'] = 'Formato de teléfono inválido';
            redirect('cliente/perfil');
            return;
        }

        // Actualizar
        $usuario->update([
            'telefono' => $telefono,
            'direccion' => $direccion
        ]);

        $_SESSION['success'] = 'Perfil actualizado correctamente';
        redirect('cliente/perfil');
    }

    /**
     * Cambiar contraseña
     */
    public function cambiarPassword(): void
    {
        $usuario = $this->checkAuth();
        if (!$usuario) return;

        require_once __DIR__ . '/../views/cliente/cambiar_password.php';
    }

    /**
     * Procesar cambio de contraseña
     */
    public function processCambiarPassword(): void
    {
        $usuario = $this->checkAuth();
        if (!$usuario) return;

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('cliente/cambiar-password');
            return;
        }

        // Validar CSRF
        if (!ValidationHelper::validateCSRFToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'Token de seguridad inválido';
            redirect('cliente/cambiar-password');
            return;
        }

        $passwordActual = $_POST['password_actual'] ?? '';
        $passwordNueva = $_POST['password_nueva'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';

        // Validar contraseña actual
        $sql = "SELECT password FROM usuarios WHERE id = ?";
        $result = Database::fetchOne($sql, [$usuario->id]);

        if (!password_verify($passwordActual, $result['password'])) {
            $_SESSION['error'] = 'Contraseña actual incorrecta';
            redirect('cliente/cambiar-password');
            return;
        }

        // Validar que coincidan
        if ($passwordNueva !== $passwordConfirm) {
            $_SESSION['error'] = 'Las contraseñas no coinciden';
            redirect('cliente/cambiar-password');
            return;
        }

        // Validar requisitos
        $validacion = ValidationHelper::validatePassword($passwordNueva);
        if (!$validacion['valid']) {
            $_SESSION['error'] = implode('<br>', $validacion['errors']);
            redirect('cliente/cambiar-password');
            return;
        }

        // Cambiar
        if (!$usuario->cambiarPassword($passwordNueva)) {
            $_SESSION['error'] = 'Error al cambiar la contraseña';
            redirect('cliente/cambiar-password');
            return;
        }

        $_SESSION['success'] = 'Contraseña actualizada correctamente';
        redirect('cliente/perfil');
    }

    /**
     * Notificaciones
     */
    public function notificaciones(): void
    {
        $usuario = $this->checkAuth();
        if (!$usuario) return;

        $notificaciones = $this->getAllNotificaciones($usuario->id);

        require_once __DIR__ . '/../views/cliente/notificaciones.php';
    }

    /**
     * Marcar notificación como leída
     */
    public function marcarNotificacionLeida(): void
    {
        $usuario = $this->checkAuth();
        if (!$usuario) return;

        $notificacionId = intval($_POST['id'] ?? 0);

        if ($notificacionId) {
            $sql = "UPDATE notificaciones SET leido = TRUE WHERE id = ? AND usuario_id = ?";
            Database::execute($sql, [$notificacionId, $usuario->id]);
        }

        echo json_encode(['success' => true]);
        exit;
    }

    // ==================== HELPERS ====================

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
     * Subir comprobante de pago
     */
    private function uploadComprobante(array $file, int $usuarioId): ?string
    {
        $uploadDir = __DIR__ . '/../../uploads/comprobantes/';

        // Crear directorio si no existe
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Generar nombre único
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $nombreArchivo = 'comp_' . $usuarioId . '_' . time() . '.' . $extension;
        $rutaDestino = $uploadDir . $nombreArchivo;

        if (move_uploaded_file($file['tmp_name'], $rutaDestino)) {
            return 'uploads/comprobantes/' . $nombreArchivo;
        }

        return null;
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
     * Obtener notificaciones no leídas
     */
    private function getNotificacionesNoLeidas(int $usuarioId): array
    {
        $sql = "SELECT * FROM notificaciones
                WHERE usuario_id = ? AND leido = FALSE
                ORDER BY fecha_creacion DESC
                LIMIT 5";

        return Database::fetchAll($sql, [$usuarioId]);
    }

    /**
     * Obtener todas las notificaciones
     */
    private function getAllNotificaciones(int $usuarioId): array
    {
        $sql = "SELECT * FROM notificaciones
                WHERE usuario_id = ?
                ORDER BY fecha_creacion DESC";

        return Database::fetchAll($sql, [$usuarioId]);
    }
}
