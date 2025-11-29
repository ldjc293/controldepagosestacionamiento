<?php
$pageTitle = 'Notificaciones';
$breadcrumb = [
    ['label' => 'Inicio', 'url' => url('cliente/dashboard')],
    ['label' => 'Notificaciones', 'url' => '#']
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
                    <i class="bi bi-bell"></i> Todas las Notificaciones
                </h6>
                <div class="btn-group btn-group-sm">
                    <?php if (!empty($notificaciones)): ?>
                        <form action="<?= url('cliente/marcar-todas-leidas') ?>" method="POST" style="display: inline;">
                            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                            <button type="submit" class="btn btn-outline-primary">
                                <i class="bi bi-check-all"></i> Marcar Todas como Leídas
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body">
                <?php if (empty($notificaciones)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-bell-slash text-muted" style="font-size: 80px;"></i>
                        <h5 class="mt-3">No tienes notificaciones</h5>
                        <p class="text-muted">Cuando recibas notificaciones aparecerán aquí</p>
                    </div>
                <?php else: ?>
                    <!-- Filtros -->
                    <div class="mb-3">
                        <div class="btn-group btn-group-sm" role="group">
                            <a href="<?= url('cliente/notificaciones') ?>"
                               class="btn <?= !isset($_GET['tipo']) ? 'btn-primary' : 'btn-outline-primary' ?>">
                                Todas (<?= count($notificaciones) ?>)
                            </a>
                            <a href="<?= url('cliente/notificaciones?tipo=pago') ?>"
                               class="btn <?= ($_GET['tipo'] ?? '') === 'pago' ? 'btn-primary' : 'btn-outline-primary' ?>">
                                <i class="bi bi-cash"></i> Pagos
                            </a>
                            <a href="<?= url('cliente/notificaciones?tipo=mensualidad') ?>"
                               class="btn <?= ($_GET['tipo'] ?? '') === 'mensualidad' ? 'btn-primary' : 'btn-outline-primary' ?>">
                                <i class="bi bi-calendar"></i> Mensualidades
                            </a>
                            <a href="<?= url('cliente/notificaciones?tipo=control') ?>"
                               class="btn <?= ($_GET['tipo'] ?? '') === 'control' ? 'btn-primary' : 'btn-outline-primary' ?>">
                                <i class="bi bi-tag"></i> Controles
                            </a>
                            <a href="<?= url('cliente/notificaciones?tipo=sistema') ?>"
                               class="btn <?= ($_GET['tipo'] ?? '') === 'sistema' ? 'btn-primary' : 'btn-outline-primary' ?>">
                                <i class="bi bi-gear"></i> Sistema
                            </a>
                        </div>
                    </div>

                    <!-- Lista de Notificaciones -->
                    <div class="list-group">
                        <?php foreach ($notificaciones as $notif): ?>
                            <div class="list-group-item <?= !$notif['leido'] ? 'list-group-item-light' : '' ?>">
                                <div class="d-flex w-100 justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center mb-2">
                                            <?php
                                            // Icono según tipo
                                            $iconos = [
                                                'pago' => ['icon' => 'cash-coin', 'color' => 'success'],
                                                'mensualidad' => ['icon' => 'calendar-event', 'color' => 'primary'],
                                                'control' => ['icon' => 'tag', 'color' => 'info'],
                                                'morosidad' => ['icon' => 'exclamation-triangle', 'color' => 'warning'],
                                                'bloqueo' => ['icon' => 'lock', 'color' => 'danger'],
                                                'sistema' => ['icon' => 'gear', 'color' => 'secondary']
                                            ];
                                            $icon = $iconos[$notif['tipo']] ?? $iconos['sistema'];
                                            ?>
                                            <span class="badge bg-<?= $icon['color'] ?> me-2">
                                                <i class="bi bi-<?= $icon['icon'] ?>"></i>
                                            </span>
                                            <h6 class="mb-0">
                                                <?= htmlspecialchars($notif['titulo']) ?>
                                                <?php if (!$notif['leido']): ?>
                                                    <span class="badge bg-danger ms-2">Nuevo</span>
                                                <?php endif; ?>
                                            </h6>
                                        </div>
                                        <p class="mb-2"><?= nl2br(htmlspecialchars($notif['mensaje'])) ?></p>
                                        <small class="text-muted">
                                            <i class="bi bi-clock"></i>
                                            <?= date('d/m/Y H:i', strtotime($notif['fecha_creacion'])) ?>
                                        </small>
                                    </div>
                                    <div class="ms-3">
                                        <?php if (!$notif['leido']): ?>
                                            <form action="<?= url('cliente/marcar-leida') ?>" method="POST" style="display: inline;">
                                                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                                                <input type="hidden" name="notificacion_id" value="<?= $notif['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-primary" title="Marcar como leída">
                                                    <i class="bi bi-check"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Paginación (si implementas) -->
                    <?php if (isset($totalPaginas) && $totalPaginas > 1): ?>
                        <nav class="mt-4">
                            <ul class="pagination justify-content-center">
                                <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                                    <li class="page-item <?= ($paginaActual ?? 1) === $i ? 'active' : '' ?>">
                                        <a class="page-link" href="<?= url('cliente/notificaciones?pagina=' . $i) ?>">
                                            <?= $i ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
