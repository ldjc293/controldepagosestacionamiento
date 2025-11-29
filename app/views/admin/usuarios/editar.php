<?php
$pageTitle = 'Editar Usuario';
$breadcrumb = [
    ['label' => 'Inicio', 'url' => url('admin/dashboard')],
    ['label' => 'Usuarios', 'url' => url('admin/usuarios')],
    ['label' => 'Editar', 'url' => '#']
];

require_once __DIR__ . '/../../layouts/header.php';
?>

<?php require_once __DIR__ . '/../../layouts/sidebar.php'; ?>

<div class="main-content">
    <?php require_once __DIR__ . '/../../layouts/topbar.php'; ?>

    <div class="content-area">
        <?php require_once __DIR__ . '/../../layouts/alerts.php'; ?>

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="bi bi-pencil"></i> Editar Usuario #<?= $usuario->id ?>
                        </h6>
                    </div>
                    <div class="card-body">
                        <form action="<?= url('admin/usuarios/process-editar') ?>" method="POST" id="formEditarUsuario">
                            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                            <input type="hidden" name="usuario_id" value="<?= $usuario->id ?>">

                            <!-- Datos Personales -->
                            <h6 class="mb-3 fw-bold">Datos Personales</h6>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Nombre Completo *</label>
                                    <input type="text"
                                           class="form-control"
                                           name="nombre_completo"
                                           value="<?= htmlspecialchars($usuario->nombre_completo) ?>"
                                           required
                                           maxlength="100">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Cédula</label>
                                    <div class="input-group">
                                        <?php
                                        // Separar el tipo y número de cédula
                                        $cedulaTipo = '';
                                        $cedulaNumero = '';
                                        if (!empty($usuario->cedula)) {
                                            $partes = explode('-', $usuario->cedula);
                                            if (count($partes) === 2) {
                                                $cedulaTipo = $partes[0];
                                                $cedulaNumero = $partes[1];
                                            }
                                        }
                                        ?>
                                        <select class="form-select" name="cedula_tipo" id="cedulaTipo" style="max-width: 80px;">
                                            <option value="">-</option>
                                            <option value="V" <?= $cedulaTipo === 'V' ? 'selected' : '' ?>>V</option>
                                            <option value="E" <?= $cedulaTipo === 'E' ? 'selected' : '' ?>>E</option>
                                            <option value="J" <?= $cedulaTipo === 'J' ? 'selected' : '' ?>>J</option>
                                        </select>
                                        <input type="text"
                                               class="form-control"
                                               name="cedula_numero"
                                               id="cedulaNumero"
                                               value="<?= htmlspecialchars($cedulaNumero) ?>"
                                               placeholder="12345678"
                                               pattern="\d{6,8}"
                                               maxlength="8">
                                    </div>
                                    <small class="text-muted">Ingrese solo números (6 a 8 dígitos) - Opcional</small>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Email *</label>
                                    <input type="email"
                                           class="form-control"
                                           name="email"
                                           value="<?= htmlspecialchars($usuario->email) ?>"
                                           required
                                           maxlength="100">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Teléfono</label>
                                    <input type="tel"
                                           class="form-control"
                                           name="telefono"
                                           value="<?= htmlspecialchars($usuario->telefono ?? '') ?>"
                                           maxlength="20">
                                </div>
                            </div>

                            <hr class="my-4">

                            <!-- Datos de Cuenta -->
                            <h6 class="mb-3 fw-bold">Datos de Cuenta</h6>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Rol *</label>
                                    <select class="form-select" name="rol" id="selectRol" required>
                                        <option value="cliente" <?= $usuario->rol === 'cliente' ? 'selected' : '' ?>>Cliente</option>
                                        <option value="operador" <?= $usuario->rol === 'operador' ? 'selected' : '' ?>>Operador</option>
                                        <option value="consultor" <?= $usuario->rol === 'consultor' ? 'selected' : '' ?>>Consultor</option>
                                        <option value="administrador" <?= $usuario->rol === 'administrador' ? 'selected' : '' ?>>Administrador</option>
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Estado *</label>
                                    <select class="form-select" name="activo" required>
                                        <option value="1" <?= $usuario->activo ? 'selected' : '' ?>>Activo</option>
                                        <option value="0" <?= !$usuario->activo ? 'selected' : '' ?>>Inactivo</option>
                                    </select>
                                </div>
                            </div>

                            <?php if ($usuario->rol === 'cliente' && isset($apartamento) && $apartamento): ?>
                                <div class="alert alert-info">
                                    <i class="bi bi-building"></i>
                                    <strong>Apartamento:</strong>
                                    <?php if (is_array($apartamento)): ?>
                                        Bloque <?= htmlspecialchars($apartamento['bloque'] ?? 'N/A') ?> - Piso <?= htmlspecialchars($apartamento['piso'] ?? 'N/A') ?> - Apto <?= htmlspecialchars($apartamento['numero_apartamento'] ?? 'N/A') ?>
                                    <?php else: ?>
                                        Bloque <?= htmlspecialchars($apartamento->bloque ?? 'N/A') ?> - Piso <?= htmlspecialchars($apartamento->piso ?? 'N/A') ?> - Apto <?= htmlspecialchars($apartamento->numero_apartamento ?? 'N/A') ?>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <hr class="my-4">

                            <!-- Cambiar Contraseña (Opcional) -->
                            <h6 class="mb-3 fw-bold">Cambiar Contraseña (Opcional)</h6>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Nueva Contraseña</label>
                                    <div class="input-group">
                                        <input type="password"
                                               class="form-control"
                                               name="password"
                                               id="password"
                                               minlength="8">
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password')">
                                            <i class="bi bi-eye" id="icon_password"></i>
                                        </button>
                                    </div>
                                    <small class="text-muted">Dejar en blanco para no cambiar</small>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Confirmar Contraseña</label>
                                    <div class="input-group">
                                        <input type="password"
                                               class="form-control"
                                               name="password_confirm"
                                               id="password_confirm"
                                               minlength="8">
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password_confirm')">
                                            <i class="bi bi-eye" id="icon_password_confirm"></i>
                                        </button>
                                    </div>
                                    <small id="passwordMatch" class="text-muted"></small>
                                </div>
                            </div>

                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" name="cambiar_password_siguiente" id="cambiarPassword" value="1">
                                <label class="form-check-label" for="cambiarPassword">
                                    Requerir cambio de contraseña en el próximo inicio de sesión
                                </label>
                            </div>

                            <!-- Botones -->
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary" id="btnSubmit">
                                    <i class="bi bi-check-circle"></i> Guardar Cambios
                                </button>
                                <a href="<?= url('admin/usuarios') ?>" class="btn btn-outline-secondary">
                                    Cancelar
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-md-4">
                <!-- Información -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="bi bi-info-circle"></i> Información
                        </h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0" style="font-size: 14px;">
                            <li class="mb-2">
                                <strong>ID:</strong> <?= $usuario->id ?>
                            </li>
                            <li class="mb-2">
                                <strong>Fecha de registro:</strong><br>
                                <?= ($usuario->fecha_registro ?? false) ? date('d/m/Y H:i', strtotime($usuario->fecha_registro)) : 'No disponible' ?>
                            </li>
                            <li class="mb-2">
                                <strong>Último acceso:</strong><br>
                                <?= ($usuario->ultimo_acceso ?? false) ? date('d/m/Y H:i', strtotime($usuario->ultimo_acceso)) : 'Nunca' ?>
                            </li>
                            <?php if ($usuario->intentos_fallidos > 0): ?>
                                <li class="mb-0">
                                    <strong class="text-danger">Intentos fallidos:</strong>
                                    <?= $usuario->intentos_fallidos ?>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>

                <?php if ($usuario->rol === 'cliente'): ?>
                    <!-- Estadísticas del Cliente -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="bi bi-graph-up"></i> Estadísticas
                            </h6>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled mb-0" style="font-size: 14px;">
                                <li class="mb-2">
                                    <strong>Controles asignados:</strong> <?= $estadisticas['controles'] ?? 0 ?>
                                </li>
                                <li class="mb-2">
                                    <strong>Pagos realizados:</strong> <?= $estadisticas['pagos'] ?? 0 ?>
                                </li>
                                <li class="mb-2">
                                    <strong>Mensualidades vencidas:</strong>
                                    <span class="badge bg-<?= ($estadisticas['vencidas'] ?? 0) > 0 ? 'danger' : 'success' ?>">
                                        <?= $estadisticas['vencidas'] ?? 0 ?>
                                    </span>
                                </li>
                                <li class="mb-0">
                                    <strong>Deuda total:</strong>
                                    <span class="<?= ($estadisticas['deuda'] ?? 0) > 0 ? 'text-danger' : 'text-success' ?>">
                                        <?= formatUSD($estadisticas['deuda'] ?? 0) ?>
                                    </span>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Controles de Estacionamiento -->
                    <?php if (isset($controles) && !empty($controles)): ?>
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="bi bi-controller"></i> Controles Asignados
                                </h6>
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled mb-0">
                                    <?php foreach ($controles as $control): ?>
                                        <li class="mb-3 pb-3 border-bottom">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <strong>
                                                    <i class="bi bi-controller"></i>
                                                    Control #<?= htmlspecialchars($control['numero_control_completo']) ?>
                                                </strong>
                                                <?php
                                                $estadoBadge = [
                                                    'activo' => 'success',
                                                    'suspendido' => 'warning',
                                                    'desactivado' => 'secondary',
                                                    'perdido' => 'danger',
                                                    'bloqueado' => 'dark',
                                                    'vacio' => 'light'
                                                ];
                                                $colorBadge = $estadoBadge[$control['estado']] ?? 'secondary';
                                                ?>
                                                <span class="badge bg-<?= $colorBadge ?>">
                                                    <?= ucfirst($control['estado']) ?>
                                                </span>
                                            </div>
                                            <?php if ($control['fecha_asignacion']): ?>
                                                <small class="text-muted">
                                                    <i class="bi bi-calendar-check"></i>
                                                    Asignado: <?= date('d/m/Y', strtotime($control['fecha_asignacion'])) ?>
                                                </small>
                                            <?php endif; ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    <?php elseif (isset($controles)): ?>
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="bi bi-controller"></i> Controles Asignados
                                </h6>
                            </div>
                            <div class="card-body text-center py-4">
                                <i class="bi bi-inbox text-muted" style="font-size: 32px;"></i>
                                <p class="text-muted mt-2 mb-0">
                                    <small>Sin controles asignados</small>
                                </p>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
$additionalJS = <<<JS
<script>
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = document.getElementById('icon_' + fieldId);

    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    }
}

// Check password match
document.getElementById('password_confirm').addEventListener('input', function() {
    const password = document.getElementById('password').value;
    const confirm = this.value;
    const matchText = document.getElementById('passwordMatch');

    if (confirm.length === 0 && password.length === 0) {
        matchText.textContent = '';
        return;
    }

    if (password === confirm) {
        matchText.textContent = '✓ Las contraseñas coinciden';
        matchText.className = 'text-success';
    } else {
        matchText.textContent = '✗ Las contraseñas no coinciden';
        matchText.className = 'text-danger';
    }
});

// Validar cédula - solo números
const cedulaNumero = document.getElementById('cedulaNumero');
if (cedulaNumero) {
    cedulaNumero.addEventListener('input', function() {
        // Solo permitir números
        this.value = this.value.replace(/[^\d]/g, '');
    });
}

// Form validation
document.getElementById('formEditarUsuario').addEventListener('submit', function(e) {
    const password = document.getElementById('password').value;
    const confirm = document.getElementById('password_confirm').value;

    // Solo validar si se ingresó una contraseña
    if (password.length > 0 || confirm.length > 0) {
        if (password !== confirm) {
            e.preventDefault();
            alert('Las contraseñas no coinciden');
            return false;
        }
    }

    const btn = document.getElementById('btnSubmit');
    setButtonLoading(btn, true);
});
</script>
JS;
?>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>
