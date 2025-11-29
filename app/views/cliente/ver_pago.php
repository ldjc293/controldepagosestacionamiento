<?php
$pageTitle = 'Detalle del Pago';
$breadcrumb = [
    ['label' => 'Inicio', 'url' => url('cliente/dashboard')],
    ['label' => 'Historial de Pagos', 'url' => url('cliente/historial-pagos')],
    ['label' => 'Detalle', 'url' => '#']
];

require_once __DIR__ . '/../layouts/header.php';
?>

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>

<div class="main-content">
    <?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

    <div class="content-area">
        <?php require_once __DIR__ . '/../layouts/alerts.php'; ?>

        <div class="row">
            <div class="col-md-8">
                <!-- Información del Pago -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="bi bi-receipt"></i> Pago #<?= str_pad($pago->id, 5, '0', STR_PAD_LEFT) ?>
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <small class="text-muted">Fecha de Pago</small>
                                <h5><?= date('d/m/Y', strtotime($pago->fecha_pago)) ?></h5>
                                <p class="text-muted mb-0">
                                    Registrado: <?= date('d/m/Y H:i', strtotime($pago->fecha_pago)) ?>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted">Estado</small>
                                <div>
                                    <?php if ($pago->estado_comprobante === 'aprobado'): ?>
                                        <span class="badge bg-success fs-5">
                                            <i class="bi bi-check-circle"></i> Aprobado
                                        </span>
                                    <?php elseif ($pago->estado_comprobante === 'rechazado'): ?>
                                        <span class="badge bg-danger fs-5">
                                            <i class="bi bi-x-circle"></i> Rechazado
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-warning fs-5">
                                            <i class="bi bi-hourglass-split"></i> Pendiente
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <small class="text-muted">Monto Pagado</small>
                                <h3 class="text-primary mb-0">
                                    <?php if ($pago->moneda_pago === 'usd_efectivo' || $pago->moneda_pago === 'usd_zelle'): ?>
                                        <?= formatUSD($pago->monto_usd) ?>
                                    <?php else: ?>
                                        <?= formatBs($pago->monto_bs) ?>
                                    <?php endif; ?>
                                </h3>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted">Método de Pago</small>
                                <div>
                                    <?php
                                    $metodos = [
                                        'usd_efectivo' => ['label' => 'USD Efectivo', 'icon' => 'cash'],
                                        'usd_zelle' => ['label' => 'Zelle', 'icon' => 'phone'],
                                        'bs_transferencia' => ['label' => 'Transferencia Bs', 'icon' => 'bank'],
                                        'bs_pago_movil' => ['label' => 'Pago Móvil', 'icon' => 'phone'],
                                        'bs_efectivo' => ['label' => 'Bs Efectivo', 'icon' => 'cash']
                                    ];
                                    $metodo = $metodos[$pago->moneda_pago] ?? ['label' => $pago->moneda_pago, 'icon' => 'credit-card'];
                                    ?>
                                    <strong>
                                        <i class="bi bi-<?= $metodo['icon'] ?>"></i>
                                        <?= $metodo['label'] ?>
                                    </strong>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted">Referencia</small>
                                <div>
                                    <?php if ($pago->notas): ?>
                                        <code><?= htmlspecialchars($pago->notas) ?></code>
                                    <?php else: ?>
                                        <span class="text-muted">Sin referencia</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <!-- Mensualidades Cubiertas -->
                        <h6 class="mb-3">
                            <i class="bi bi-calendar-check"></i> Mensualidades Pagadas
                        </h6>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Mes/Año</th>
                                        <th>Monto Original</th>
                                        <th>Monto Aplicado</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($mensualidades as $mens): ?>
                                        <tr>
                                            <td><?= date('F Y', strtotime($mens['mes_correspondiente'])) ?></td>
                                            <td><?= formatUSD($mens['monto_usd']) ?></td>
                                            <td><strong><?= formatUSD($mens['monto_aplicado_usd']) ?></strong></td>
                                            <td>
                                                <span class="badge bg-success">Pagado</span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <?php if ($pago->estado_comprobante === 'rechazado' && $pago->notas): ?>
                            <div class="alert alert-danger mt-3">
                                <strong><i class="bi bi-exclamation-triangle"></i> Motivo del Rechazo:</strong>
                                <p class="mb-0 mt-2"><?= nl2br(htmlspecialchars($pago->notas)) ?></p>
                            </div>
                        <?php endif; ?>

                        <?php if ($pago->estado_comprobante === 'aprobado' && $pago->fecha_aprobacion): ?>
                            <div class="alert alert-success mt-3">
                                <i class="bi bi-check-circle"></i>
                                Aprobado el <?= date('d/m/Y H:i', strtotime($pago->fecha_aprobacion)) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Comprobante -->
                <?php if ($pago->comprobante_ruta): ?>
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="bi bi-file-earmark-image"></i> Comprobante Adjunto
                            </h6>
                        </div>
                        <div class="card-body text-center">
                            <?php
                            $extension = pathinfo($pago->comprobante_ruta, PATHINFO_EXTENSION);
                            $isPDF = strtolower($extension) === 'pdf';
                            ?>

                            <?php if ($isPDF): ?>
                                <a href="<?= url($pago->comprobante_ruta) ?>" target="_blank" class="btn btn-lg btn-primary">
                                    <i class="bi bi-file-pdf"></i> Ver Comprobante PDF
                                </a>
                            <?php else: ?>
                                <img src="<?= url($pago->comprobante_ruta) ?>"
                                     alt="Comprobante"
                                     class="img-fluid rounded"
                                     style="max-height: 600px;">
                                <div class="mt-3">
                                    <a href="<?= url($pago->comprobante_ruta) ?>" target="_blank" class="btn btn-primary">
                                        <i class="bi bi-arrows-fullscreen"></i> Ver en Tamaño Completo
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <div class="col-md-4">
                <!-- Acciones -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="bi bi-tools"></i> Acciones
                        </h6>
                    </div>
                    <div class="card-body d-grid gap-2">
                        <?php if ($pago->estado_comprobante === 'aprobado' && $pago->recibo_ruta): ?>
                            <a href="<?= url($pago->recibo_ruta) ?>" target="_blank" class="btn btn-success">
                                <i class="bi bi-file-pdf"></i> Descargar Recibo
                            </a>
                        <?php endif; ?>

                        <a href="<?= url('cliente/historial-pagos') ?>" class="btn btn-outline-primary">
                            <i class="bi bi-arrow-left"></i> Volver al Historial
                        </a>

                        <?php if ($pago->estado_comprobante === 'rechazado'): ?>
                            <a href="<?= url('cliente/registrar-pago') ?>" class="btn btn-warning">
                                <i class="bi bi-plus-circle"></i> Registrar Nuevo Pago
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Información Adicional -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="bi bi-info-circle"></i> Información
                        </h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0" style="font-size: 14px;">
                            <li class="mb-2">
                                <i class="bi bi-calendar"></i>
                                <strong>Fecha de registro:</strong><br>
                                <?= date('d/m/Y H:i:s', strtotime($pago->fecha_pago)) ?>
                            </li>

                            <?php if ($pago->estado_comprobante === 'aprobado'): ?>
                                <li class="mb-2">
                                    <i class="bi bi-check-circle"></i>
                                    <strong>Aprobado por:</strong><br>
                                    Operador #<?= $pago->aprobado_por ?>
                                </li>
                                <li class="mb-2">
                                    <i class="bi bi-clock"></i>
                                    <strong>Fecha de aprobación:</strong><br>
                                    <?= date('d/m/Y H:i:s', strtotime($pago->fecha_aprobacion)) ?>
                                </li>
                            <?php elseif ($pago->estado_comprobante === 'rechazado'): ?>
                                <li class="mb-2">
                                    <i class="bi bi-x-circle"></i>
                                    <strong>Rechazado por:</strong><br>
                                    Operador #<?= $pago->rechazado_por ?>
                                </li>
                            <?php else: ?>
                                <li class="mb-2">
                                    <i class="bi bi-hourglass-split"></i>
                                    <strong>Estado:</strong><br>
                                    En espera de revisión
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
