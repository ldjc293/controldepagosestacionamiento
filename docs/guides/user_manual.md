# Manual de Usuario

## Funcionalidades por Rol

### Para Clientes (Residentes)
- **Login seguro**: Acceso con usuario y contraseña.
- **Perfil**: Actualizar datos personales (teléfono, email) y cambiar contraseña.
- **Estado de Cuenta**: Consultar deudas pendientes, próximo vencimiento y monto adeudado.
- **Pagos**:
  - Cargar comprobantes de pago (imagen/PDF) en diferentes monedas (USD efectivo, Bs transferencia, Bs efectivo).
  - Ver historial de pagos con recibos en PDF.
- **Controles**:
  - Gestionar controles de estacionamiento (solicitar suspensión, desactivación por pérdida, cambio de cantidad).
  - Ver información de apartamento vinculado (bloque, escalera, piso, número).
- **Notificaciones**: Alertas cuando adeuden más de 3 meses.

### Para Operadores
- **Dashboard**: Vista de comprobantes pendientes de aprobar y pagos del día.
- **Gestión de Pagos**:
  - Registrar pagos manuales para clientes (múltiples monedas).
  - Aprobar o rechazar comprobantes de pago (con motivo de rechazo).
  - Eliminar pagos registrados por error.
- **Gestión de Clientes**: Buscar clientes por nombre/número de apartamento/bloque.
- **Gestión de Controles**:
  - Aprobar solicitudes de cambio de cantidad de controles.
  - Aprobar suspensiones temporales de controles.
- **Recibos**: Generar recibos oficiales en PDF con numeración, código QR y registro en Google Sheets.
- **Historial**: Ver historial de transacciones del día/semana/mes.

### Para Consultores Internos
- **Dashboard**: Resumen de morosidad y estadísticas generales.
- **Consultas**:
  - Ver estado de cuenta de todos los clientes (solo lectura).
  - Ver clientes en riesgo de desconexión (más de 3 meses de deuda).
- **Reportes**:
  - Reportes de morosidad (quién debe y cuánto).
  - Reportes de ingresos por mes/año.
  - Reportes de pagos del día/semana/mes.
  - Consultar estadísticas generales del estacionamiento.
  - Exportar datos a Excel.

### Para Administrador
- **Acceso Total**: Todas las funcionalidades anteriores + alertas del sistema.
- **Gestión de Usuarios**: Crear, editar, eliminar, cambiar roles, transferir espacios, importar desde Excel.
- **Configuración**:
  - Tarifas mensuales (afecta mensualidades generadas no pagadas).
  - Tasa de cambio BCV (para conversión USD a Bs).
  - Exoneraciones especiales para clientes.
- **Gestión de Apartamentos**:
  - Gestión de bloque, escalera, piso, número.
  - Asignar/desasignar usuarios a apartamentos.
- **Gestión de Controles**:
  - Gestión de controles de estacionamiento por apartamento.
  - **Informe de posiciones vacías**: Ver los 250 controles (A/B) y detectar posiciones sin asignar.
  - **Mapa de controles**: Visualización de todos los controles con filtros por receptor (A/B) y estado.
  - Asignar controles vacíos a nuevos residentes.
  - Gestionar bloqueos de controles (4 meses de mora).
- **Pagos y Recibos**:
  - Aprobación/rechazo de comprobantes de pago.
  - Eliminar pagos registrados por error.
  - Registrar pagos de reconexión.
- **Sistema**:
  - Respaldo de base de datos.
  - Registro de actividades (logs completos: quién hizo qué y cuándo).
  - Envío masivo de notificaciones por email.