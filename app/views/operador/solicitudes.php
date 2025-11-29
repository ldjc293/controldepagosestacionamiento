<?php
$pageTitle = 'Solicitudes de Cambios';
$breadcrumb = [
    ['label' => 'Inicio', 'url' => url('operador/dashboard')],
    ['label' => 'Solicitudes', 'url' => '#']
];

require_once __DIR__ . '/../layouts/header.php';
?>

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

<div class="main-content">
    <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

    <div class="content-area">
        <?php require_once __DIR__ . '/../layouts/alerts.php'; ?>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="bi bi-exclamation-triangle"></i> Solicitudes de Cambios Pendientes
                </h6>
                <span class="badge bg-warning" style="font-size: 14px;">
                    <?= is_array($solicitudes) ? count($solicitudes) : 0 ?> pendientes
                </span>
            </div>
            <div class="card-body">
                <?php if (!is_array($solicitudes) || count($solicitudes) === 0): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-check-circle text-success" style="font-size: 80px;"></i>
                        <h5 class="mt-3">¡Todo en orden!</h5>
                        <p class="text-muted">No hay solicitudes de cambios pendientes</p>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        <strong>Importante:</strong> Revisa cada solicitud cuidadosamente antes de aprobar o rechazar.
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Solicitante</th>
                                    <th>Tipo de Solicitud</th>
                                    <th>Motivo</th>
                                    <th>Fecha</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (is_array($solicitudes) ? $solicitudes : [] as $solicitud): ?>
                                    <tr>
                                        <td>
                                            <span class="badge bg-secondary">#<?= str_pad($solicitud['id'], 5, '0', STR_PAD_LEFT) ?></span>
                                        </td>
                                        <td>
                                            <strong><?= htmlspecialchars($solicitud['solicitante_nombre']) ?></strong>
                                        </td>
                                        <td>
                                            <?php
                                            $tipos = [
                                                'cambio_cantidad_controles' => 'Cambio de Cantidad de Controles',
                                                'suspension_control' => 'Suspensión de Control',
                                                'desactivacion_control' => 'Desactivación de Control'
                                            ];
                                            echo $tipos[$solicitud['tipo_solicitud']] ?? ucfirst($solicitud['tipo_solicitud']);
                                            ?>
                                        </td>
                                        <td>
                                            <span class="text-truncate d-block" style="max-width: 200px;" title="<?= htmlspecialchars($solicitud['motivo']) ?>">
                                                <?= htmlspecialchars($solicitud['motivo']) ?>
                                            </span>
                                        </td>
                                        <td><?= date('d/m/Y H:i', strtotime($solicitud['fecha_solicitud'])) ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-success"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#modalAprobarSolicitud<?= $solicitud['id'] ?>">
                                                    <i class="bi bi-check-circle"></i> Aprobar
                                                </button>
                                                <button type="button" class="btn btn-danger"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#modalRechazarSolicitud<?= $solicitud['id'] ?>">
                                                    <i class="bi bi-x-circle"></i> Rechazar
                                                </button>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Modal Aprobar Solicitud -->
                                    <div class="modal fade" id="modalAprobarSolicitud<?= $solicitud['id'] ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form method="POST" action="<?= url('operador/process-solicitud') ?>">
                                                    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                                                    <input type="hidden" name="solicitud_id" value="<?= $solicitud['id'] ?>">
                                                    <input type="hidden" name="accion" value="aprobar">

                                                    <div class="modal-header">
                                                        <h5 class="modal-title">
                                                            <i class="bi bi-check-circle text-success"></i> Aprobar Solicitud
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="alert alert-success">
                                                            <strong>Solicitud:</strong> <?= htmlspecialchars($solicitud['motivo']) ?>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label">Observaciones (opcional)</label>
                                                            <textarea class="form-control" name="observaciones" rows="3"
                                                                    placeholder="Añade cualquier observación relevante..."></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                        <button type="submit" class="btn btn-success">
                                                            <i class="bi bi-check-circle"></i> Confirmar Aprobación
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Modal Rechazar Solicitud -->
                                    <div class="modal fade" id="modalRechazarSolicitud<?= $solicitud['id'] ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form method="POST" action="<?= url('operador/process-solicitud') ?>">
                                                    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                                                    <input type="hidden" name="solicitud_id" value="<?= $solicitud['id'] ?>">
                                                    <input type="hidden" name="accion" value="rechazar">

                                                    <div class="modal-header">
                                                        <h5 class="modal-title">
                                                            <i class="bi bi-x-circle text-danger"></i> Rechazar Solicitud
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="alert alert-warning">
                                                            <strong>Solicitud:</strong> <?= htmlspecialchars($solicitud['motivo']) ?>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label">Motivo del rechazo <span class="text-danger">*</span></label>
                                                            <textarea class="form-control" name="observaciones" rows="3"
                                                                    placeholder="Explica por qué se rechaza esta solicitud..." required></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                        <button type="submit" class="btn btn-danger">
                                                            <i class="bi bi-x-circle"></i> Confirmar Rechazo
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
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