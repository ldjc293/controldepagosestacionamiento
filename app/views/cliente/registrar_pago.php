<?php
$pageTitle = 'Registrar Pago';
$breadcrumb = [
    ['label' => 'Inicio', 'url' => url('cliente/dashboard')],
    ['label' => 'Registrar Pago', 'url' => '#']
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
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="bi bi-upload"></i> Registrar Nuevo Pago
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i>
                            <strong>Instrucciones:</strong>
                            <ul class="mb-0 mt-2">
                                <li>Selecciona las mensualidades que deseas pagar</li>
                                <li>Ingresa el monto exacto que pagaste</li>
                                <li>Sube una foto clara del comprobante de pago</li>
                                <li>Tu pago será revisado por un operador en las próximas 24 horas</li>
                            </ul>
                        </div>

                        <form action="<?= url('cliente/process-registrar-pago') ?>" method="POST" enctype="multipart/form-data" id="formPago">
                            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">

                            <!-- Mensualidades a Pagar -->
                            <div class="mb-4">
                                <label class="form-label fw-bold">
                                    <i class="bi bi-list-check"></i> Mensualidades a Pagar *
                                </label>
                                <div class="border rounded p-3" style="max-height: 300px; overflow-y: auto;">
                                    <?php if (empty($mensualidadesPendientes)): ?>
                                        <p class="text-muted text-center mb-0">No tienes mensualidades pendientes</p>
                                    <?php else: ?>
                                        <?php foreach ($mensualidadesPendientes as $mensualidad): ?>
                                            <div class="form-check mb-2 p-3 border rounded mensualidad-item" data-monto="<?= $mensualidad['monto_usd'] ?>">
                                                <input class="form-check-input mensualidad-checkbox"
                                                       type="checkbox"
                                                       name="mensualidades[]"
                                                       value="<?= $mensualidad['id'] ?>"
                                                       id="mens_<?= $mensualidad['id'] ?>">
                                                <label class="form-check-label w-100" for="mens_<?= $mensualidad['id'] ?>">
                                                    <div class="d-flex justify-content-between">
                                                        <div>
                                                            <strong><?= date('F Y', strtotime($mensualidad['mes_correspondiente'])) ?></strong>
                                                            <br>
                                                            <small class="text-muted">
                                                                Vence: <?= date('d/m/Y', strtotime($mensualidad['fecha_vencimiento'])) ?>
                                                            </small>
                                                        </div>
                                                        <div class="text-end">
                                                            <strong class="text-primary"><?= formatUSD($mensualidad['monto_usd']) ?></strong>
                                                            <?php if ($mensualidad['estado'] === 'vencida'): ?>
                                                                <br><span class="badge bg-danger">Vencida</span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                                <div class="mt-2 p-3 bg-light rounded">
                                    <div class="d-flex justify-content-between">
                                        <span>Mensualidades seleccionadas:</span>
                                        <strong id="mensualidadesCount">0</strong>
                                    </div>
                                    <div class="d-flex justify-content-between mt-2">
                                        <span>Total a pagar:</span>
                                        <strong class="text-primary" id="totalUSD"><?= formatUSD(0) ?></strong>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <!-- Moneda -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Moneda de Pago *</label>
                                    <select class="form-select" name="moneda" id="moneda" required>
                                        <option value="USD">Dólares (USD)</option>
                                        <option value="Bs">Bolívares (Bs)</option>
                                    </select>
                                </div>

                                <!-- Monto -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Monto Pagado *</label>
                                    <input type="number"
                                           class="form-control"
                                           name="monto"
                                           id="monto"
                                           step="0.01"
                                           min="0.01"
                                           required
                                           placeholder="0.00">
                                    <small class="text-muted" id="montoConversion"></small>
                                </div>
                            </div>

                            <div class="row">
                                <!-- Método de Pago -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Método de Pago *</label>
                                    <select class="form-select" name="metodo_pago" required>
                                        <option value="">Seleccione...</option>
                                        <option value="transferencia">Transferencia Bancaria</option>
                                        <option value="pago_movil">Pago Móvil</option>
                                        <option value="zelle">Zelle</option>
                                        <option value="efectivo">Efectivo</option>
                                        <option value="otro">Otro</option>
                                    </select>
                                </div>

                                <!-- Referencia -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Número de Referencia</label>
                                    <input type="text"
                                           class="form-control"
                                           name="referencia"
                                           placeholder="Ej: 123456789">
                                    <small class="text-muted">Opcional</small>
                                </div>
                            </div>

                            <!-- Fecha de Pago -->
                            <div class="mb-3">
                                <label class="form-label fw-bold">Fecha del Pago *</label>
                                <input type="date"
                                       class="form-control"
                                       name="fecha_pago"
                                       value="<?= date('Y-m-d') ?>"
                                       max="<?= date('Y-m-d') ?>"
                                       required>
                            </div>

                            <!-- Comprobante -->
                            <div class="mb-4">
                                <label class="form-label fw-bold">Comprobante de Pago</label>
                                <input type="file"
                                       class="form-control"
                                       name="comprobante"
                                       id="comprobante"
                                       accept="image/*,.pdf">
                                <small class="text-muted">
                                    Formatos: JPG, PNG, PDF (Máximo 5MB) - Opcional pero recomendado
                                </small>
                                <div id="previewComprobante" class="mt-3"></div>
                            </div>

                            <!-- Botones -->
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary" id="btnSubmit">
                                    <i class="bi bi-upload"></i> Registrar Pago
                                </button>
                                <a href="<?= url('cliente/dashboard') ?>" class="btn btn-outline-secondary">
                                    Cancelar
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-md-4">
                <!-- Tasa de Cambio -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="bi bi-currency-exchange"></i> Tasa de Cambio BCV
                        </h6>
                    </div>
                    <div class="card-body text-center">
                        <div class="mb-2">
                            <small class="text-muted">1 USD =</small>
                        </div>
                        <h2 class="mb-0 text-primary" id="tasaBCV"><?= number_format($tasaBCV, 2) ?> Bs</h2>
                        <small class="text-muted">Actualizado hoy</small>
                    </div>
                </div>

                <!-- Datos Bancarios -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="bi bi-bank"></i> Datos Bancarios
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <small class="text-muted">Banco</small>
                            <div class="fw-bold">Banco de Venezuela</div>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted">Titular</small>
                            <div class="fw-bold">Asociación de Vecinos</div>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted">Cuenta Corriente (Bs)</small>
                            <div class="d-flex align-items-center">
                                <code>0102-0000-0000000000</code>
                                <button class="btn btn-sm btn-link" onclick="copyToClipboard('0102-0000-0000000000')">
                                    <i class="bi bi-clipboard"></i>
                                </button>
                            </div>
                        </div>
                        <hr>
                        <div class="mb-3">
                            <small class="text-muted">Zelle (USD)</small>
                            <div class="d-flex align-items-center">
                                <code>pagos@estacionamiento.com</code>
                                <button class="btn btn-sm btn-link" onclick="copyToClipboard('pagos@estacionamiento.com')">
                                    <i class="bi bi-clipboard"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$additionalJS = <<<JS
<script>
const tasaBCV = {$tasaBCV};

// Calcular total al seleccionar mensualidades
document.querySelectorAll('.mensualidad-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', calcularTotal);
});

function calcularTotal() {
    const checkboxes = document.querySelectorAll('.mensualidad-checkbox:checked');
    let total = 0;

    checkboxes.forEach(checkbox => {
        const item = checkbox.closest('.mensualidad-item');
        total += parseFloat(item.dataset.monto);
    });

    document.getElementById('mensualidadesCount').textContent = checkboxes.length;
    document.getElementById('totalUSD').textContent = formatUSD(total);
    document.getElementById('monto').value = total.toFixed(2);

    actualizarConversion();
}

// Actualizar conversión cuando cambia moneda o monto
document.getElementById('moneda').addEventListener('change', actualizarConversion);
document.getElementById('monto').addEventListener('input', actualizarConversion);

function actualizarConversion() {
    const moneda = document.getElementById('moneda').value;
    const monto = parseFloat(document.getElementById('monto').value) || 0;
    const divConversion = document.getElementById('montoConversion');

    if (monto > 0) {
        if (moneda === 'USD') {
            const montoBs = monto * tasaBCV;
            divConversion.textContent = 'Equivalente: ' + formatBs(montoBs);
        } else {
            const montoUSD = monto / tasaBCV;
            divConversion.textContent = 'Equivalente: ' + formatUSD(montoUSD);
        }
    } else {
        divConversion.textContent = '';
    }
}

// Preview de comprobante
document.getElementById('comprobante').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const preview = document.getElementById('previewComprobante');

    if (file) {
        const reader = new FileReader();

        reader.onload = function(e) {
            if (file.type.startsWith('image/')) {
                preview.innerHTML = '<img src="' + e.target.result + '" class="img-fluid rounded" style="max-height: 300px;">';
            } else {
                preview.innerHTML = '<div class="alert alert-info"><i class="bi bi-file-pdf"></i> Archivo PDF seleccionado: ' + file.name + '</div>';
            }
        };

        reader.readAsDataURL(file);
    } else {
        preview.innerHTML = '';
    }
});

// Validar formulario
document.getElementById('formPago').addEventListener('submit', function(e) {
    const checkboxes = document.querySelectorAll('.mensualidad-checkbox:checked');

    if (checkboxes.length === 0) {
        e.preventDefault();
        alert('Debes seleccionar al menos una mensualidad');
        return false;
    }

    const btn = document.getElementById('btnSubmit');
    setButtonLoading(btn, true);
});
</script>
JS;

require_once __DIR__ . '/../layouts/footer.php';
?>
