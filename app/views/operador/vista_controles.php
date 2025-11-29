<?php
$pageTitle = 'Vista de Controles de Estacionamiento';
$breadcrumb = [
    ['label' => 'Inicio', 'url' => url('operador/dashboard')],
    ['label' => 'Vista de Controles', 'url' => '#']
];

require_once __DIR__ . '/../layouts/header.php';
?>

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

<div class="main-content">
    <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

    <div class="content-area">
        <?php require_once __DIR__ . '/../layouts/alerts.php'; ?>

        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-0">
                            <i class="bi bi-grid-3x3"></i> Vista de Controles de Estacionamiento
                        </h4>
                        <small class="text-muted">Controles ordenados por número con información del propietario</small>
                    </div>
                    <a href="<?= url('operador/clientes-controles') ?>" class="btn btn-outline-primary">
                        <i class="bi bi-people"></i> Ver Clientes
                    </a>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Buscar</label>
                        <input type="text"
                               class="form-control"
                               name="busqueda"
                               value="<?= htmlspecialchars($_GET['busqueda'] ?? '') ?>"
                               placeholder="Nombre, email, cédula, control o apartamento">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Estado</label>
                        <select class="form-select" name="estado">
                            <option value="">Todos los estados</option>
                            <option value="activo" <?= (isset($_GET['estado']) && $_GET['estado'] == 'activo') ? 'selected' : '' ?>>Activo</option>
                            <option value="bloqueado" <?= (isset($_GET['estado']) && $_GET['estado'] == 'bloqueado') ? 'selected' : '' ?>>Bloqueado</option>
                            <option value="suspendido" <?= (isset($_GET['estado']) && $_GET['estado'] == 'suspendido') ? 'selected' : '' ?>>Suspendido</option>
                            <option value="desactivado" <?= (isset($_GET['estado']) && $_GET['estado'] == 'desactivado') ? 'selected' : '' ?>>Desactivado</option>
                            <option value="perdido" <?= (isset($_GET['estado']) && $_GET['estado'] == 'perdido') ? 'selected' : '' ?>>Perdido</option>
                            <option value="vacio" <?= (isset($_GET['estado']) && $_GET['estado'] == 'vacio') ? 'selected' : '' ?>>Vacío</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Receptor</label>
                        <select class="form-select" name="receptor">
                            <option value="">Todos</option>
                            <option value="A" <?= (isset($_GET['receptor']) && $_GET['receptor'] == 'A') ? 'selected' : '' ?>>Receptor A</option>
                            <option value="B" <?= (isset($_GET['receptor']) && $_GET['receptor'] == 'B') ? 'selected' : '' ?>>Receptor B</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Bloque</label>
                        <select class="form-select" name="bloque">
                            <option value="">Todos los bloques</option>
                            <?php for ($i = 27; $i <= 32; $i++): ?>
                                <option value="<?= $i ?>" <?= (isset($_GET['bloque']) && $_GET['bloque'] == $i) ? 'selected' : '' ?>>
                                    Bloque <?= $i ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                    <div class="col-md-1">
                        <label class="form-label">&nbsp;</label>
                        <a href="<?= url('operador/vista-controles') ?>" class="btn btn-outline-secondary w-100">
                            <i class="bi bi-x-circle"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabla de Controles -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="bi bi-list-ol"></i> Controles de Estacionamiento
                    <span class="badge bg-primary ms-2"><?= count($controles) ?> controles</span>
                </h6>
            </div>
            <div class="card-body">
                <?php if (empty($controles)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-grid-3x3 text-muted" style="font-size: 64px;"></i>
                        <p class="text-muted mt-3">No se encontraron controles con los filtros aplicados</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>N° Control</th>
                                    <th>Posición</th>
                                    <th>Estado</th>
                                    <th>Propietario</th>
                                    <th>Apartamento</th>
                                    <th>Fecha Asignación</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($controles as $control): ?>
                                    <tr>
                                        <td>
                                            <span class="badge bg-primary fs-6">
                                                <?= htmlspecialchars($control['numero_control_completo']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark">
                                                Posición <?= $control['posicion_numero'] ?> - Receptor <?= $control['receptor'] ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php
                                            $estadoClass = 'bg-secondary';
                                            $estadoIcon = 'bi-circle';
                                            switch ($control['estado']) {
                                                case 'activo':
                                                    $estadoClass = 'bg-success';
                                                    $estadoIcon = 'bi-check-circle';
                                                    break;
                                                case 'bloqueado':
                                                    $estadoClass = 'bg-danger';
                                                    $estadoIcon = 'bi-lock';
                                                    break;
                                                case 'suspendido':
                                                    $estadoClass = 'bg-warning';
                                                    $estadoIcon = 'bi-pause-circle';
                                                    break;
                                                case 'desactivado':
                                                    $estadoClass = 'bg-secondary';
                                                    $estadoIcon = 'bi-dash-circle';
                                                    break;
                                                case 'perdido':
                                                    $estadoClass = 'bg-dark';
                                                    $estadoIcon = 'bi-question-circle';
                                                    break;
                                                case 'vacio':
                                                    $estadoClass = 'bg-light text-dark';
                                                    $estadoIcon = 'bi-circle';
                                                    break;
                                            }
                                            ?>
                                            <span class="badge <?= $estadoClass ?>">
                                                <i class="bi <?= $estadoIcon ?>"></i>
                                                <?= ucfirst($control['estado']) ?>
                                            </span>
                                            <?php if (!empty($control['motivo_estado'])): ?>
                                                <br><small class="text-muted"><?= htmlspecialchars($control['motivo_estado']) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($control['propietario_nombre'])): ?>
                                                <div>
                                                    <strong><?= htmlspecialchars($control['propietario_nombre']) ?></strong>
                                                    <br>
                                                    <small class="text-muted">
                                                        <i class="bi bi-envelope"></i> <?= htmlspecialchars($control['propietario_email'] ?? 'Sin email') ?>
                                                        <?php if (!empty($control['propietario_cedula'])): ?>
                                                            <br><i class="bi bi-card-text"></i> <?= htmlspecialchars($control['propietario_cedula']) ?>
                                                        <?php endif; ?>
                                                    </small>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-muted">Sin asignar</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($control['apartamento'])): ?>
                                                <span class="badge bg-secondary">
                                                    <?= htmlspecialchars($control['apartamento']) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($control['fecha_asignacion'])): ?>
                                                <small class="text-muted">
                                                    <?= date('d/m/Y', strtotime($control['fecha_asignacion'])) ?>
                                                </small>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>