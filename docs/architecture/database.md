# Base de Datos

## Tablas Principales

### `usuarios`
- **id** (PK, AUTO_INCREMENT)
- **nombre_completo**
- **email** (UNIQUE)
- **password** (HASHED)
- **telefono**
- **rol** (ENUM: 'cliente', 'operador', 'consultor', 'administrador')
- **activo** (BOOLEAN)
- **intentos_fallidos** (INT, default: 0)
- **bloqueado_hasta** (DATETIME, NULL)
- **fecha_registro**
- **ultimo_acceso**
- **exonerado** (BOOLEAN, default: FALSE)
- **motivo_exoneracion** (TEXT, NULL)

### `apartamentos`
- **id** (PK, AUTO_INCREMENT)
- **bloque** (VARCHAR)
- **escalera** (VARCHAR)
- **piso** (INT)
- **numero_apartamento** (VARCHAR)
- **activo** (BOOLEAN)
- **fecha_creacion**

### `apartamento_usuario`
- **id** (PK, AUTO_INCREMENT)
- **apartamento_id** (FK -> apartamentos)
- **usuario_id** (FK -> usuarios)
- **cantidad_controles** (INT)
- **fecha_asignacion**
- **activo** (BOOLEAN)

### `controles_estacionamiento`
- **id** (PK, AUTO_INCREMENT)
- **apartamento_usuario_id** (FK -> apartamento_usuario, NULL si está vacío)
- **posicion_numero** (INT, 1-250, posición física del control)
- **receptor** (ENUM: 'A', 'B', indica en qué receptor está)
- **numero_control_completo** (VARCHAR, UNIQUE, Ej: '15A', '15B', '250A')
- **estado** (ENUM: 'activo', 'suspendido', 'desactivado', 'perdido', 'bloqueado', 'vacio')
- **motivo_estado** (TEXT, NULL)
- **fecha_estado**
- **aprobado_por** (FK -> usuarios, NULL)
- **fecha_asignacion** (DATETIME, NULL)
- **INDEX UNIQUE**(posicion_numero, receptor)

### `configuracion_tarifas`
- **id** (PK, AUTO_INCREMENT)
- **monto_mensual_usd** (DECIMAL, tarifa en USD por control)
- **fecha_vigencia_inicio**
- **fecha_vigencia_fin**
- **activo** (BOOLEAN)

### `tasa_cambio_bcv`
- **id** (PK, AUTO_INCREMENT)
- **tasa_usd_bs** (DECIMAL)
- **fecha_registro**
- **registrado_por** (FK -> usuarios)

### `mensualidades`
- **id** (PK, AUTO_INCREMENT)
- **apartamento_usuario_id** (FK -> apartamento_usuario)
- **mes** (INT, 1-12)
- **anio** (INT)
- **cantidad_controles** (INT, snapshot al momento de generar)
- **monto_usd** (DECIMAL, monto total en USD)
- **monto_bs** (DECIMAL, monto total en Bs)
- **tasa_cambio_id** (FK -> tasa_cambio_bcv, tasa usada para conversión)
- **estado** (ENUM: 'pendiente', 'pagado', 'parcialmente_pagado', 'vencido')
- **fecha_vencimiento** (último día del mes)
- **fecha_generacion** (día 5 del mes)
- **bloqueado** (BOOLEAN, TRUE si tiene 4+ meses sin pagar)

### `pagos`
- **id** (PK, AUTO_INCREMENT)
- **mensualidad_id** (FK -> mensualidades)
- **apartamento_usuario_id** (FK -> apartamento_usuario)
- **numero_recibo** (VARCHAR, UNIQUE, auto-incremental)
- **monto_usd** (DECIMAL)
- **monto_bs** (DECIMAL)
- **tasa_cambio_id** (FK -> tasa_cambio_bcv)
- **moneda_pago** (ENUM: 'usd_efectivo', 'bs_transferencia', 'bs_efectivo')
- **fecha_pago**
- **comprobante_ruta** (PATH a archivo, NULL si es efectivo)
- **estado_comprobante** (ENUM: 'pendiente', 'aprobado', 'rechazado', 'no_aplica')
- **motivo_rechazo** (TEXT, NULL)
- **registrado_por** (FK -> usuarios, quien registró el pago)
- **aprobado_por** (FK -> usuarios, NULL)
- **fecha_aprobacion** (DATETIME, NULL)
- **es_reconexion** (BOOLEAN, default: FALSE)
- **monto_reconexion_usd** (DECIMAL, NULL)
- **google_sheets_sync** (BOOLEAN, default: FALSE)
- **notas** (TEXT)

### `solicitudes_cambios`
- **id** (PK, AUTO_INCREMENT)
- **apartamento_usuario_id** (FK -> apartamento_usuario)
- **tipo_solicitud** (ENUM: 'cambio_cantidad_controles', 'suspension_control', 'desactivacion_control')
- **cantidad_controles_nueva** (INT, NULL)
- **control_id** (FK -> controles_estacionamiento, NULL)
- **motivo** (TEXT)
- **estado** (ENUM: 'pendiente', 'aprobada', 'rechazada')
- **fecha_solicitud**
- **aprobado_por** (FK -> usuarios, NULL)
- **fecha_respuesta** (DATETIME, NULL)
- **observaciones** (TEXT, NULL)

### `notificaciones`
- **id** (PK, AUTO_INCREMENT)
- **usuario_id** (FK -> usuarios)
- **tipo** (ENUM: 'alerta_3_meses', 'alerta_bloqueo', 'comprobante_rechazado', 'pago_aprobado', 'solicitud_aprobada', 'solicitud_rechazada')
- **titulo** (VARCHAR)
- **mensaje** (TEXT)
- **leido** (BOOLEAN, default: FALSE)
- **fecha_creacion**
- **fecha_lectura** (DATETIME, NULL)
- **email_enviado** (BOOLEAN, default: FALSE)

### `logs_actividad`
- **id** (PK, AUTO_INCREMENT)
- **usuario_id** (FK -> usuarios)
- **accion** (descripción de la acción realizada)
- **modulo** (VARCHAR, ej: 'pagos', 'usuarios', 'controles')
- **tabla_afectada**
- **registro_id**
- **datos_anteriores** (JSON, NULL)
- **datos_nuevos** (JSON, NULL)
- **ip_address**
- **user_agent**
- **fecha_hora**

### `password_reset_tokens`
- **email** (VARCHAR)
- **token** (VARCHAR)
- **created_at** (TIMESTAMP)

## Vistas

### `vista_morosidad`
Consulta rápida de usuarios con deudas pendientes.

### `vista_controles_vacios`
Listado de controles disponibles para asignación.

## Procedimientos Almacenados

### `sp_generar_mensualidades_mes`
Generación masiva de mensualidades para todos los usuarios activos.