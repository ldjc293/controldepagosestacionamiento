# Reglas de Negocio y Procesos

## Roles y Permisos

| Funcionalidad | Cliente | Operador | Consultor | Admin |
|--------------|---------|----------|-----------|-------|
| Ver propio estado de cuenta | ✓ | - | - | ✓ |
| Cargar comprobantes | ✓ | - | - | ✓ |
| Registrar pagos manuales | - | ✓ | - | ✓ |
| Aprobar comprobantes | - | ✓ | - | ✓ |
| Ver todos los estados de cuenta | - | - | ✓ | ✓ |
| Generar reportes | - | - | ✓ | ✓ |
| Gestionar usuarios | - | - | - | ✓ |
| Gestionar espacios | - | - | - | ✓ |
| Configurar tarifas | - | - | - | ✓ |
| Ver logs del sistema | - | - | - | ✓ |

## Reglas de Negocio

### Gestión de Usuarios y Apartamentos
- Cada usuario puede estar asignado a un apartamento.
- Los apartamentos están identificados por: Bloque + Escalera + Piso + Número.
- Un apartamento puede tener múltiples controles de estacionamiento.
- La cantidad de controles por apartamento determina el monto de la mensualidad.
- Los usuarios pueden solicitar cambios en la cantidad de controles (requiere aprobación del operador).

### Tarifas y Monedas
- **Tarifa base**: $1 USD mensual por control.
- **Monedas aceptadas**:
  - USD en efectivo.
  - Bolívares (Bs) por transferencia (requiere comprobante).
  - Bolívares (Bs) en efectivo.
- **Tasa de cambio**: Se usa la tasa oficial del Banco Central de Venezuela (BCV).
- El administrador actualiza manualmente la tasa de cambio BCV en el sistema.
- Las mensualidades se generan en USD y Bs usando la tasa vigente.

### Generación de Mensualidades
- Las mensualidades se generan **automáticamente el día 5 de cada mes**.
- Se genera una mensualidad por cada apartamento con controles activos.
- Fecha de vencimiento: **último día del mes**.
- El monto se calcula: `Cantidad de controles * Tarifa mensual * Tasa BCV`.
- Si la tarifa cambia, afecta solo las mensualidades NO pagadas ya generadas.

### Pagos
- Los pagos deben ser por el **monto completo** de uno o varios meses.
- No se aceptan pagos parciales.
- Los pagos pueden cubrir múltiples meses atrasados.
- **Pagos en efectivo**: No requieren comprobante, se registran directamente como aprobados.
- **Pagos con transferencia**: Requieren comprobante y aprobación del operador.
- Los comprobantes pueden ser aprobados o rechazados con motivo.
- Si un comprobante es rechazado, se notifica al usuario con el motivo.

### Recibos Oficiales
- Cada pago aprobado genera un recibo oficial en PDF.
- El recibo incluye:
  - Número único de recibo (auto-incremental).
  - Código QR (con URL de verificación o datos del recibo).
  - Datos del cliente y apartamento.
  - Meses pagados.
  - Montos en USD y Bs.
  - Fecha y hora de pago.
- Los recibos se sincronizan con Google Sheets (opcional).

### Morosidad y Bloqueos
- Si un usuario adeuda **más de 3 meses**, recibe una alerta de advertencia.
- Si un usuario adeuda **4 o más meses**, sus controles son **bloqueados automáticamente**.
- Controles bloqueados = no funcionan en el sistema físico del estacionamiento.
- Para reconectar, el usuario debe:
  1. Pagar todos los meses adeudados.
  2. Pagar un cargo adicional de reconexión.
- El monto de reconexión es configurable por el administrador.

### Estados de Controles
- **Activo**: Control funcionando normalmente.
- **Suspendido**: Usuario solicita suspensión temporal (no genera mensualidad, requiere aprobación).
- **Desactivado**: Control dado de baja permanentemente.
- **Perdido**: Control reportado como extraviado (requiere reemplazo).
- **Bloqueado**: Control bloqueado por morosidad (4+ meses).

### Exoneraciones
- El administrador puede marcar usuarios como "exonerados".
- Usuarios exonerados no generan mensualidades.
- Se debe registrar un motivo de exoneración.

### Transferencia de Espacios
- Cuando un residente se muda, el administrador puede transferir el apartamento a un nuevo usuario.
- Se desvincula al usuario anterior y se asigna el nuevo usuario.
- El historial de pagos se mantiene asociado al apartamento.

## Flujo de Pagos Multi-Moneda

### Escenario 1: Cliente paga con transferencia en Bs
1. Cliente inicia sesión y ve su estado de cuenta.
2. Cliente selecciona los meses a pagar.
3. Sistema muestra el monto en USD y en Bs (usando tasa BCV vigente).
4. Cliente selecciona "Transferencia Bs" y carga comprobante (JPG/PNG/PDF).
5. Sistema registra el pago como "Pendiente de aprobación".
6. Operador/Admin revisa el comprobante.
7. Si aprueba: Sistema genera recibo PDF con QR y sincroniza con Google Sheets.
8. Si rechaza: Sistema notifica al cliente con el motivo del rechazo.

### Escenario 2: Cliente paga con efectivo (USD o Bs)
1. Cliente va presencialmente con el operador.
2. Operador busca al cliente por apartamento/nombre.
3. Operador selecciona los meses a pagar.
4. Operador selecciona tipo de pago: "USD efectivo" o "Bs efectivo".
5. Sistema calcula el monto según la moneda.
6. Operador registra el pago (no requiere aprobación).
7. Sistema genera recibo PDF inmediatamente con QR.
8. Se imprime recibo para el cliente.

### Escenario 3: Cliente con 4+ meses de mora (Reconexión)
1. Sistema detecta 4 meses sin pagar y marca controles como "bloqueados".
2. Cliente intenta pagar pero sistema indica que debe pagar reconexión.
3. Cliente paga meses adeudados + cargo de reconexión.
4. Operador/Admin aprueba el pago.
5. Sistema desbloquea los controles automáticamente.
6. Se genera recibo que incluye detalle de reconexión.

### Cálculo de Montos
```
Monto USD = Cantidad de controles * Tarifa mensual USD * Número de meses
Monto Bs = Monto USD * Tasa BCV vigente
```

**Ejemplo:**
- Apartamento con 2 controles
- Tarifa: $1 USD por control
- Paga 3 meses
- Tasa BCV: 36.50 Bs/USD

```
Monto USD = 2 * 1 * 3 = $6 USD
Monto Bs = 6 * 36.50 = 219 Bs