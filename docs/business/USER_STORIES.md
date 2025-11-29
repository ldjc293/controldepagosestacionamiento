# USER STORIES - Sistema de Control de Pagos de Estacionamiento

> Historias de usuario detalladas para el sistema de gestión de pagos mensuales del Estacionamiento del Blq 27 al 32

---

## USER STORY #1: PAGO MENSUAL DE ESTACIONAMIENTO

### PERSONAJE

**Nombre:** María González
**Rol:** Cliente/Residente
**Edad:** 42 años
**Situación:** Propietaria de apartamento, trabaja como maestra, vive con su familia
**Nivel técnico:** Básico - usa WhatsApp y apps bancarias regularmente

### CONTEXTO

**Apartamento:** Bloque 29, Apto 7-B
**Controles asignados:**
- Posición 145, Receptor A (control principal - vehículo familiar)
- Posición 145, Receptor B (control secundario - vehículo del esposo)

**Estado actual:** Cliente activo, sin mora
**Historial de pagos:** Al día, generalmente paga en los primeros 5 días del mes
**Forma de pago preferida:** Transferencia en Bolívares (Bs)

### HISTORIA

> **Como** residente del Bloque 29 con 2 controles de estacionamiento asignados,
> **Quiero** pagar mi mensualidad de $2 USD (equivalente en Bs) mediante transferencia bancaria,
> **Para** mantener mis controles activos y evitar bloqueos por mora.

### CRITERIOS DE ACEPTACIÓN

1. ✅ María puede iniciar sesión con su cédula y contraseña
2. ✅ El sistema muestra su mensualidad pendiente del mes actual ($2 USD)
3. ✅ El sistema muestra la tasa BCV del día para convertir USD a Bs
4. ✅ María puede ver sus 2 controles (Posición 145 A y B) y su estado (Activo)
5. ✅ María puede subir un comprobante de pago (.jpg/.png/.pdf, máx 5MB)
6. ✅ El sistema registra el pago como "Pendiente de Aprobación"
7. ✅ El operador revisa y aprueba el comprobante
8. ✅ María recibe un recibo en PDF con formato EST-XXXXXX
9. ✅ El recibo incluye QR code verificable
10. ✅ María puede descargar el recibo desde su perfil

### FLUJO DETALLADO

#### **Paso 1: Acceso al sistema (5 de enero, 9:00 AM)**

María abre su navegador y accede a `http://localhost/controldepagosestacionamiento`

- Sistema muestra página de login
- María ingresa:
  - **Usuario:** `12345678` (su cédula)
  - **Contraseña:** `Maria2024!`
- Click en "Iniciar Sesión"
- Sistema valida credenciales y redirige a Dashboard de Cliente

#### **Paso 2: Visualización del Dashboard**

María ve en su dashboard:

```
┌─────────────────────────────────────────────┐
│ Bienvenida, María González                  │
│ Bloque 29, Apto 7-B                        │
├─────────────────────────────────────────────┤
│ MENSUALIDAD ENERO 2025                     │
│ Estado: PENDIENTE                          │
│ Monto: $2.00 USD                           │
│ Bs. 72.80 (Tasa BCV: 36.40)               │
│ Fecha límite: 31/01/2025                  │
│                                            │
│ [📤 Registrar Pago]                        │
└─────────────────────────────────────────────┘

┌─────────────────────────────────────────────┐
│ MIS CONTROLES DE ESTACIONAMIENTO           │
├─────────────────────────────────────────────┤
│ 🅰️ Posición 145-A                          │
│    Estado: ACTIVO ✅                        │
│    Último pago: 05/12/2024                 │
│                                            │
│ 🅱️ Posición 145-B                          │
│    Estado: ACTIVO ✅                        │
│    Último pago: 05/12/2024                 │
└─────────────────────────────────────────────┘
```

#### **Paso 3: Registro del pago (mismo día, 2:30 PM)**

María realizó la transferencia bancaria por Bs. 72.80 desde su banco móvil.

- Click en **[📤 Registrar Pago]**
- Sistema muestra formulario:

```
┌─────────────────────────────────────────────┐
│ REGISTRAR PAGO - ENERO 2025                │
├─────────────────────────────────────────────┤
│ Mensualidad: Enero 2025                    │
│ Monto USD: $2.00                           │
│                                            │
│ Forma de pago:                             │
│ ○ USD Efectivo                             │
│ ● Bs Transferencia ✓                      │
│ ○ Bs Efectivo                              │
│                                            │
│ Monto pagado (Bs): [72.80________]        │
│ Tasa aplicada: 36.40 (BCV del día)        │
│                                            │
│ Referencia bancaria:                       │
│ [0102-1234-5678-9012-3456_____________]   │
│                                            │
│ Comprobante de pago:                       │
│ [📎 Seleccionar archivo...]                │
│   comprobante_05012025.jpg (2.3 MB) ✓    │
│                                            │
│ [Cancelar]  [✓ Enviar Comprobante]        │
└─────────────────────────────────────────────┘
```

- María completa:
  - Forma de pago: **Bs Transferencia**
  - Monto: **72.80**
  - Referencia: **0102-1234-5678-9012-3456**
  - Archivo: **comprobante_05012025.jpg** (captura de su banco móvil)
- Click en **[✓ Enviar Comprobante]**

#### **Paso 4: Confirmación del registro**

Sistema procesa y muestra mensaje:

```
✅ Comprobante registrado exitosamente

Tu pago ha sido registrado y está pendiente
de aprobación por el operador.

Recibirás una notificación cuando sea
procesado (generalmente en 24-48 horas).

Número de referencia: PAG-2025-00087

[Volver al Dashboard]
```

Estado en dashboard cambia a:

```
┌─────────────────────────────────────────────┐
│ MENSUALIDAD ENERO 2025                     │
│ Estado: PENDIENTE DE APROBACIÓN ⏳         │
│ Comprobante enviado: 05/01/2025 14:35     │
│ Referencia: PAG-2025-00087                 │
└─────────────────────────────────────────────┘
```

#### **Paso 5: Aprobación del operador (6 de enero, 10:00 AM)**

El operador Carlos Ramírez revisa el comprobante:

- Verifica monto: Bs. 72.80 ✓
- Verifica referencia bancaria en sistema del banco ✓
- Verifica tasa aplicada (36.40) vs tasa BCV del 5 de enero ✓
- Click en **[✅ Aprobar Pago]**

Sistema automáticamente:
- Actualiza estado de mensualidad a "PAGADO"
- Genera recibo PDF **EST-000087**
- Envía email a Maria con el recibo adjunto
- Registra en logs: "Pago aprobado por usuario_id 3 (Carlos Ramírez)"

#### **Paso 6: Recepción del recibo (6 de enero, 10:02 AM)**

María recibe email:

```
De: Sistema Estacionamiento Caricuao <noreply@estacionamiento.local>
Para: mariagonzalez@email.com
Asunto: ✅ Pago Aprobado - Recibo EST-000087

Estimada María González,

Su pago correspondiente a la mensualidad de
ENERO 2025 ha sido aprobado exitosamente.

Detalles:
- Recibo: EST-000087
- Fecha de pago: 05/01/2025
- Monto: $2.00 USD (Bs. 72.80)
- Controles: Posición 145-A, 145-B

Adjunto encontrará su recibo en formato PDF.

También puede descargarlo desde su perfil en:
http://localhost/controldepagosestacionamiento

Gracias por su pago puntual.
```

María también ve notificación en el sistema al iniciar sesión:

```
🔔 NUEVA NOTIFICACIÓN
   Su pago de Enero 2025 ha sido aprobado
   Recibo: EST-000087
   [Ver recibo]  [Descargar PDF]
```

#### **Paso 7: Verificación del recibo PDF**

María descarga el PDF y ve:

```
┌─────────────────────────────────────────────┐
│   ESTACIONAMIENTO CARICUAO UD 5            │
│   Bloque 27 al 32                          │
├─────────────────────────────────────────────┤
│ RECIBO DE PAGO                             │
│ No. EST-000087                             │
│                                            │
│ Fecha emisión: 06/01/2025                  │
│ Fecha de pago: 05/01/2025                  │
│                                            │
│ CLIENTE:                                   │
│ María González                             │
│ Cédula: V-12.345.678                       │
│ Apartamento: Bloque 29, Apto 7-B          │
│                                            │
│ CONTROLES:                                 │
│ • Posición 145-A (Receptor A)              │
│ • Posición 145-B (Receptor B)              │
│                                            │
│ DETALLE DE PAGO:                           │
│ Concepto: Mensualidad Enero 2025          │
│ Monto USD: $2.00                           │
│ Tasa BCV: 36.40 (05/01/2025)              │
│ Monto Bs: 72.80                            │
│ Forma de pago: Transferencia Bancaria     │
│ Referencia: 0102-1234-5678-9012-3456      │
│                                            │
│ Estado: PAGADO ✅                           │
│                                            │
│ Procesado por: Carlos Ramírez (Operador)  │
│                                            │
│          [QR CODE]                         │
│   Verificar en: /verificar/EST-000087     │
│                                            │
│ Válido para el periodo: Enero 2025        │
└─────────────────────────────────────────────┘
```

### CASOS EDGE

#### **Caso 1: María intenta pagar dos veces el mismo mes**

- María ya tiene pago aprobado para Enero
- Sistema muestra: "Ya existe un pago registrado para este periodo"
- Botón [Registrar Pago] está deshabilitado

#### **Caso 2: María sube archivo muy grande (8 MB)**

- Sistema valida tamaño antes de enviar
- Mensaje: "El archivo excede el tamaño máximo permitido (5 MB). Por favor, reduzca el tamaño de la imagen"
- Sugerencia: "Puede tomar una nueva foto con menor calidad o usar una app de compresión"

#### **Caso 3: María olvida pagar y llega al día 31**

- Sistema no bloquea aún (tiene 5 días de gracia hasta el 5 de febrero)
- Notificación en dashboard: "⚠️ Pago pendiente - Fecha límite: 31/01/2025"
- Email recordatorio enviado el día 30

#### **Caso 4: María no paga en febrero y llega a 3 meses de mora**

- Dashboard muestra alerta roja: "🚨 MORA: 3 meses - Próximo a bloqueo"
- Email urgente: "Su cuenta será bloqueada si no regulariza el pago antes del 30/04/2025"
- Monto adeudado acumulado: $6.00 USD

#### **Caso 5: Operador rechaza el comprobante**

- El comprobante estaba borroso/ilegible
- Operador marca como **RECHAZADO** con motivo: "Imagen no legible"
- María recibe notificación: "❌ Comprobante rechazado - Motivo: Imagen no legible. Por favor, suba un nuevo comprobante más claro"
- Estado vuelve a "PENDIENTE" para que María pueda subir nuevo comprobante

#### **Caso 6: Sesión expira mientras María llena el formulario**

- María llena el formulario pero la sesión expira (30 min inactividad)
- Al hacer click en [Enviar], sistema detecta sesión expirada
- Redirige a login con mensaje: "Su sesión ha expirado por inactividad. Por favor, inicie sesión nuevamente"
- Los datos del formulario NO se pierden (guardados en localStorage temporalmente)

### NOTAS TÉCNICAS

**Tablas involucradas:**
- `usuarios` (id=15, rol=cliente, Maria González)
- `apartamentos` (id=72, bloque=29, numero=7-B)
- `controles_estacionamiento` (posicion=145, receptor_a=15, receptor_b=15)
- `mensualidades` (apartamento_id=72, mes=1, año=2025, monto_usd=2.00)
- `tasa_cambio_bcv` (fecha=2025-01-05, tasa=36.40)
- `pagos` (mensualidad_id=87, monto_bs=72.80, forma_pago=bs_transferencia, estado=aprobado)
- `notificaciones` (usuario_id=15, tipo=pago_aprobado)

**Archivos subidos:**
- `uploads/comprobantes/2025/01/PAG-2025-00087_comprobante.jpg`

**PDFs generados:**
- `uploads/recibos/2025/01/EST-000087.pdf`

**Logs registrados:**
```sql
- Login exitoso: usuario_id=15, ip=192.168.1.105
- Pago registrado: pago_id=87, usuario_id=15
- Comprobante subido: archivo=PAG-2025-00087_comprobante.jpg
- Pago aprobado: pago_id=87, aprobado_por=3 (Carlos)
- Recibo generado: EST-000087.pdf
- Email enviado: destinatario=mariagonzalez@email.com
```

---

## USER STORY #2: PRIMER ACCESO AL SISTEMA - CLIENTE NUEVO

### PERSONAJE

**Nombre:** Roberto Díaz
**Rol:** Cliente/Residente
**Edad:** 35 años
**Situación:** Acaba de mudarse al Bloque 30, técnico de refrigeración, vive solo
**Nivel técnico:** Intermedio - usa computadoras en el trabajo, maneja apps móviles con facilidad

### CONTEXTO

**Apartamento:** Bloque 30, Apto 12-C
**Controles asignados:**
- Posición 89, Receptor A (único control - vehículo personal)

**Estado actual:** Usuario nuevo creado por el administrador
**Historial de pagos:** Ninguno (primera vez en el sistema)
**Credenciales:** Recibió email con credenciales temporales del administrador
**Situación:** Nunca ha usado el sistema, necesita familiarizarse con la plataforma

### HISTORIA

> **Como** nuevo residente del Bloque 30 que acaba de recibir sus credenciales de acceso,
> **Quiero** ingresar por primera vez al sistema, explorar mi perfil y entender cómo funciona el pago mensual,
> **Para** familiarizarme con la plataforma y realizar mi primer pago de estacionamiento correctamente.

### CRITERIOS DE ACEPTACIÓN

1. ✅ Roberto puede acceder con sus credenciales temporales enviadas por email
2. ✅ El sistema solicita cambio de contraseña en el primer acceso (seguridad obligatoria)
3. ✅ Roberto ve un mensaje de bienvenida explicando el sistema
4. ✅ El dashboard muestra claramente su apartamento, control asignado y estado de cuenta
5. ✅ Roberto puede ver información de ayuda sobre cómo usar el sistema
6. ✅ El sistema muestra su primera mensualidad generada (si ya se generó)
7. ✅ Roberto puede actualizar sus datos personales (teléfono, email alternativo)
8. ✅ Roberto puede ver instrucciones sobre las formas de pago aceptadas
9. ✅ Roberto comprende cómo y cuándo debe pagar su mensualidad
10. ✅ Roberto puede navegar por todas las secciones disponibles para clientes

### FLUJO DETALLADO

#### **Paso 1: Recepción de credenciales (15 de enero, 8:00 AM)**

Roberto recibe un email del administrador:

```
De: Sistema Estacionamiento Caricuao <admin@estacionamiento.local>
Para: roberto.diaz@email.com
Asunto: 🎉 Bienvenido al Sistema de Estacionamiento - Credenciales de Acceso

Estimado Roberto Díaz,

Le damos la bienvenida al sistema de gestión de pagos del
Estacionamiento del Blq 27 al 32, Caricuao Ud 5.

Sus credenciales de acceso son:

🔗 URL: http://localhost/controldepagosestacionamiento
👤 Usuario: 23456789 (su cédula)
🔑 Contraseña temporal: Temp2025*Roberto

IMPORTANTE: Por seguridad, el sistema le solicitará
cambiar su contraseña en el primer acceso.

Su apartamento asignado:
- Bloque: 30
- Apartamento: 12-C
- Control de estacionamiento: Posición 89-A

Mensualidad: $1.00 USD (1 control)
Fecha de vencimiento: Último día de cada mes
Formas de pago: USD efectivo, Bs transferencia, Bs efectivo

Para cualquier duda, puede contactar con la administración.

Saludos cordiales,
Administración del Estacionamiento
```

#### **Paso 2: Primer acceso al sistema (15 de enero, 7:30 PM)**

Roberto llega a casa del trabajo y accede al sistema desde su laptop.

- Abre navegador y va a: `http://localhost/controldepagosestacionamiento`
- Sistema muestra página de login limpia con el logo del estacionamiento
- Roberto ingresa:
  - **Usuario:** `23456789`
  - **Contraseña:** `Temp2025*Roberto`
- Click en **[Iniciar Sesión]**

#### **Paso 3: Cambio de contraseña obligatorio**

Sistema detecta que es el primer acceso y muestra pantalla de cambio de contraseña:

```
┌─────────────────────────────────────────────┐
│   🔐 CAMBIO DE CONTRASEÑA OBLIGATORIO      │
├─────────────────────────────────────────────┤
│ Por seguridad, debe cambiar su contraseña  │
│ temporal antes de continuar.               │
│                                            │
│ Contraseña actual:                         │
│ [Temp2025*Roberto___________________]     │
│                                            │
│ Nueva contraseña:                          │
│ [••••••••••••••••••_________________]     │
│                                            │
│ Confirmar nueva contraseña:                │
│ [••••••••••••••••••_________________]     │
│                                            │
│ ⚠️ Requisitos:                             │
│ • Mínimo 8 caracteres                      │
│ • Al menos 1 mayúscula                     │
│ • Al menos 1 número                        │
│ • Al menos 1 carácter especial             │
│                                            │
│ [Cancelar]  [✓ Cambiar Contraseña]        │
└─────────────────────────────────────────────┘
```

- Roberto ingresa:
  - **Contraseña actual:** `Temp2025*Roberto`
  - **Nueva contraseña:** `Roberto@Blq30!`
  - **Confirmar:** `Roberto@Blq30!`
- Click en **[✓ Cambiar Contraseña]**
- Sistema valida requisitos y confirma cambio

#### **Paso 4: Mensaje de bienvenida**

Después del cambio de contraseña, sistema muestra modal de bienvenida:

```
┌─────────────────────────────────────────────┐
│   👋 ¡BIENVENIDO, ROBERTO DÍAZ!            │
├─────────────────────────────────────────────┤
│                                            │
│ Gracias por usar nuestro sistema.         │
│                                            │
│ 📌 INFORMACIÓN IMPORTANTE:                 │
│                                            │
│ • Su mensualidad se genera el día 5        │
│   de cada mes automáticamente              │
│                                            │
│ • Fecha de vencimiento: último día         │
│   del mes (+ 5 días de gracia)            │
│                                            │
│ • Formas de pago:                          │
│   - USD efectivo (presencial)              │
│   - Bs transferencia (suba comprobante)   │
│   - Bs efectivo (presencial)              │
│                                            │
│ • Tasa de cambio: Actualizada según BCV   │
│                                            │
│ • Recuerde pagar a tiempo para evitar     │
│   bloqueos por morosidad                   │
│                                            │
│ [❓ Ver Tutorial]  [✓ Entendido]          │
└─────────────────────────────────────────────┘
```

Roberto lee la información y hace click en **[✓ Entendido]**

#### **Paso 5: Dashboard - Primera vista**

Roberto ve su dashboard por primera vez:

```
┌─────────────────────────────────────────────┐
│ 👋 Bienvenido, Roberto Díaz                 │
│ 📍 Bloque 30, Apto 12-C                    │
│                                            │
│ 🎉 Esta es su primera vez en el sistema   │
│ ¿Necesita ayuda? [📖 Ver guía rápida]     │
├─────────────────────────────────────────────┤
│ RESUMEN DE CUENTA                          │
│                                            │
│ 🚗 Controles activos: 1                    │
│ 💰 Mensualidad: $1.00 USD/mes             │
│ 📅 Próximo vencimiento: 31/01/2025        │
│ ⚠️ Estado: PENDIENTE DE PAGO               │
└─────────────────────────────────────────────┘

┌─────────────────────────────────────────────┐
│ MENSUALIDAD ENERO 2025                     │
│ Estado: PENDIENTE ⏰                        │
│ Monto: $1.00 USD                           │
│ Bs. 36.40 (Tasa BCV: 36.40)               │
│ Fecha límite: 31/01/2025                  │
│ Días de gracia: Hasta 05/02/2025          │
│                                            │
│ [📤 Registrar Pago]  [ℹ️ Más info]         │
└─────────────────────────────────────────────┘

┌─────────────────────────────────────────────┐
│ MI CONTROL DE ESTACIONAMIENTO              │
├─────────────────────────────────────────────┤
│ 🅰️ Posición 89-A                          │
│    Estado: ACTIVO ✅                        │
│    Asignado desde: 10/01/2025             │
│    Último pago: N/A (cliente nuevo)       │
│                                            │
│ ℹ️ Este control abre el portón del         │
│    estacionamiento de su bloque.          │
└─────────────────────────────────────────────┘

┌─────────────────────────────────────────────┐
│ 🎓 APRENDA A USAR EL SISTEMA               │
├─────────────────────────────────────────────┤
│ [📹 Ver tutorial en video]                 │
│ [📄 Guía de pagos paso a paso]            │
│ [❓ Preguntas frecuentes (FAQ)]            │
│ [📧 Contactar administración]             │
└─────────────────────────────────────────────┘
```

#### **Paso 6: Exploración del perfil**

Roberto hace click en el menú superior en **[Mi Perfil]**:

```
┌─────────────────────────────────────────────┐
│   PERFIL DE USUARIO                        │
├─────────────────────────────────────────────┤
│ INFORMACIÓN PERSONAL                       │
│                                            │
│ Nombre completo:                           │
│ [Roberto Díaz_______________________]     │
│                                            │
│ Cédula:                                    │
│ [V-23.456.789] (no editable)              │
│                                            │
│ Email:                                     │
│ [roberto.diaz@email.com_____________]     │
│                                            │
│ Teléfono:                                  │
│ [+58 424 1234567____________________]     │
│ ⚠️ Por favor, actualice su teléfono       │
│                                            │
│ Apartamento:                               │
│ [Bloque 30, Apto 12-C] (no editable)      │
│                                            │
│ [Actualizar datos]                         │
│                                            │
│ SEGURIDAD                                  │
│ [🔑 Cambiar contraseña]                    │
│                                            │
│ ACTIVIDAD RECIENTE                         │
│ • 15/01/2025 19:35 - Cambio de contraseña │
│ • 15/01/2025 19:30 - Primer acceso        │
└─────────────────────────────────────────────┘
```

Roberto actualiza su teléfono a: `+58 424 5556789`

#### **Paso 7: Ver información de pago**

Roberto hace click en **[ℹ️ Más info]** en la sección de mensualidad:

```
┌─────────────────────────────────────────────┐
│   INFORMACIÓN DE PAGO                      │
├─────────────────────────────────────────────┤
│ FORMAS DE PAGO ACEPTADAS:                  │
│                                            │
│ 1️⃣ USD EFECTIVO (Presencial)              │
│    • Acuda a la administración             │
│    • Recibo inmediato                      │
│    • No requiere comprobante               │
│                                            │
│ 2️⃣ BS TRANSFERENCIA (En línea)            │
│    • Realice la transferencia bancaria     │
│    • Suba comprobante en el sistema        │
│    • Aprobación en 24-48 horas            │
│                                            │
│ 3️⃣ BS EFECTIVO (Presencial)               │
│    • Acuda a la administración             │
│    • Recibo inmediato                      │
│    • No requiere comprobante               │
│                                            │
│ CÁLCULO DE MONTO:                          │
│ • Tarifa: $1 USD por control/mes          │
│ • Usted tiene: 1 control                   │
│ • Mensualidad: $1.00 USD                  │
│ • En Bs: Tarifa USD × Tasa BCV del día    │
│                                            │
│ FECHAS IMPORTANTES:                        │
│ • Generación: Día 5 de cada mes           │
│ • Vencimiento: Último día del mes         │
│ • Gracia: 5 días adicionales              │
│ • Alerta de mora: 3+ meses sin pagar      │
│ • Bloqueo automático: 4+ meses sin pagar  │
│                                            │
│ [Cerrar]                                   │
└─────────────────────────────────────────────┘
```

#### **Paso 8: Revisar historial (vacío)**

Roberto hace click en **[Historial de Pagos]** del menú:

```
┌─────────────────────────────────────────────┐
│   HISTORIAL DE PAGOS                       │
├─────────────────────────────────────────────┤
│                                            │
│       📋 No hay pagos registrados          │
│                                            │
│   Usted es un cliente nuevo.              │
│   Sus pagos aparecerán aquí una vez        │
│   sean procesados.                         │
│                                            │
│   [📤 Hacer mi primer pago]                │
│                                            │
└─────────────────────────────────────────────┘
```

#### **Paso 9: Entender el sistema**

Roberto hace click en **[❓ Preguntas frecuentes (FAQ)]**:

```
┌─────────────────────────────────────────────┐
│   PREGUNTAS FRECUENTES                     │
├─────────────────────────────────────────────┤
│                                            │
│ ❓ ¿Cuándo debo pagar mi mensualidad?      │
│ ➡️ Antes del último día del mes. Tiene    │
│    5 días de gracia adicionales.          │
│                                            │
│ ❓ ¿Qué pasa si no pago a tiempo?          │
│ ➡️ Después de 4 meses sin pagar, su       │
│    control será bloqueado automáticamente.│
│                                            │
│ ❓ ¿Cómo subo un comprobante de pago?      │
│ ➡️ Vaya a "Registrar Pago", seleccione    │
│    "Bs Transferencia" y suba la imagen.  │
│                                            │
│ ❓ ¿Cuánto tarda la aprobación?            │
│ ➡️ Generalmente entre 24 y 48 horas       │
│    hábiles.                               │
│                                            │
│ ❓ ¿Puedo pagar varios meses adelantados?  │
│ ➡️ Sí, contacte a la administración.      │
│                                            │
│ ❓ ¿Cómo cambio mi contraseña?             │
│ ➡️ Vaya a "Mi Perfil" > "Seguridad"      │
│                                            │
│ [Ver más preguntas]                        │
│                                            │
└─────────────────────────────────────────────┘
```

#### **Paso 10: Revisión del menú de navegación**

Roberto explora el menú lateral y encuentra todas las opciones disponibles:

```
┌─────────────────────┐
│  MENÚ               │
├─────────────────────┤
│ 🏠 Dashboard        │
│ 💳 Registrar Pago   │
│ 📜 Historial        │
│ 🚗 Mis Controles    │
│ 👤 Mi Perfil        │
│ 🔔 Notificaciones   │
│ ❓ Ayuda            │
│ 🚪 Cerrar Sesión    │
└─────────────────────┘
```

Roberto hace click en cada sección para familiarizarse:

- **Registrar Pago:** Ve el formulario vacío listo para usar
- **Historial:** Vacío (como vio antes)
- **Mis Controles:** Ve su control 89-A con toda la información
- **Notificaciones:** 1 notificación de bienvenida del sistema

### CASOS EDGE

#### **Caso 1: Roberto intenta acceder con contraseña temporal después de cambiarla**

- Roberto cierra sesión y olvida su nueva contraseña
- Intenta ingresar con la contraseña temporal `Temp2025*Roberto`
- Sistema muestra: "❌ Credenciales incorrectas. La contraseña temporal ya fue cambiada"
- Roberto usa opción **[¿Olvidó su contraseña?]** para recuperarla

#### **Caso 2: Roberto intenta cambiar contraseña con requisitos débiles**

- Nueva contraseña: `123456`
- Sistema valida y muestra: "❌ La contraseña no cumple con los requisitos de seguridad"
- Muestra lista de requisitos faltantes resaltados en rojo

#### **Caso 3: Roberto no actualiza su teléfono**

- Sistema muestra banner persistente en dashboard: "⚠️ Complete su perfil: Por favor, actualice su número de teléfono"
- Banner desaparece cuando actualiza el teléfono

#### **Caso 4: Roberto intenta acceder a funciones de administrador**

- Roberto escribe en URL: `/admin/usuarios`
- Sistema detecta rol insuficiente
- Redirige a dashboard con mensaje: "⛔ Acceso denegado. No tiene permisos para esta sección"
- Log de seguridad registra el intento

#### **Caso 5: Primer pago sin mensualidad generada todavía**

- Roberto se muda el día 1 de enero (antes del día 5)
- Sistema aún no ha generado mensualidad de enero
- Dashboard muestra: "ℹ️ Su primera mensualidad se generará el 5 de enero. Por ahora no tiene pagos pendientes"
- Botón [Registrar Pago] está deshabilitado temporalmente

#### **Caso 6: Roberto cierra el modal de bienvenida sin leer**

- Roberto hace click en [✓ Entendido] sin leer el contenido
- Sistema guarda preferencia: "bienvenida_vista = true"
- En siguiente acceso, sistema muestra banner: "🎓 ¿Necesita ayuda para usar el sistema? [Ver guía rápida]"
- Banner es discreto y se puede cerrar

#### **Caso 7: Email de credenciales llega a spam**

- Roberto no ve el email en su bandeja principal
- Espera 2 días y contacta administrador
- Administrador reenvía credenciales manualmente
- Sistema registra reenvío en logs

### NOTAS TÉCNICAS

**Tablas involucradas:**
- `usuarios` (id=42, rol=cliente, Roberto Díaz, primer_acceso=true → false después de login)
- `apartamentos` (id=85, bloque=30, numero=12-C)
- `apartamento_usuario` (id=58, apartamento_id=85, usuario_id=42, cantidad_controles=1)
- `controles_estacionamiento` (id=89, posicion=89, receptor='A', apartamento_usuario_id=58, estado='activo')
- `mensualidades` (apertura_usuario_id=42, mes=1, año=2025, estado='pendiente')
- `logs_actividad` (accion='primer_acceso', 'cambio_password', 'actualizar_perfil')
- `notificaciones` (usuario_id=42, tipo='bienvenida', mensaje='Bienvenido al sistema...')

**Email enviado:**
- Asunto: "🎉 Bienvenido al Sistema de Estacionamiento - Credencias de Acceso"
- Contiene: URL, usuario (cédula), contraseña temporal, datos del apartamento, instrucciones básicas

**Flags de sistema:**
- `usuarios.primer_acceso = true` → Se cambia a `false` después del primer login exitoso
- `usuarios.password_temporal = true` → Se cambia a `false` después de cambiar contraseña
- `usuarios.perfil_completo = false` → Se cambia a `true` cuando actualiza teléfono/email

**Validaciones de contraseña:**
```php
- Longitud mínima: 8 caracteres
- Al menos 1 mayúscula: [A-Z]
- Al menos 1 número: [0-9]
- Al menos 1 carácter especial: [@$!%*?&#]
- No puede ser igual a la anterior
- No puede contener nombre de usuario
```

**Modal de bienvenida:**
- Se muestra solo en el primer acceso
- Contiene información clave del sistema
- Opciones: [Ver Tutorial] o [Entendido]
- Preferencia guardada: `usuarios.modal_bienvenida_visto = true`

**Logs registrados:**
```sql
- Primer acceso: usuario_id=42, accion='primer_login', ip=192.168.1.110
- Cambio de contraseña: usuario_id=42, accion='cambio_password_obligatorio'
- Modal visto: usuario_id=42, accion='modal_bienvenida_visto'
- Actualizar perfil: usuario_id=42, accion='actualizar_telefono', datos_nuevos='{"telefono":"+58 424 5556789"}'
- Navegación: usuario_id=42, accion='visitar_faq'
```

**Secciones exploradas:**
- Dashboard (vista inicial)
- Mi Perfil (actualización de datos)
- Información de pago (modal explicativo)
- Historial de pagos (vacío)
- FAQ (preguntas frecuentes)
- Notificaciones (1 notificación de bienvenida)

**Experiencia de usuario (UX):**
- Cambio de contraseña obligatorio para seguridad
- Modal de bienvenida con información esencial
- Dashboard con sección de ayuda prominente
- Tooltips e íconos informativos en toda la interfaz
- Mensajes claros sobre próximas acciones
- Navegación intuitiva con menú lateral

---

## USER STORY #3: OPERADOR REGISTRA PAGO EN EFECTIVO

### PERSONAJE

**Nombre:** Carmen Méndez
**Rol:** Operador
**Edad:** 58 años
**Situación:** Trabaja en la administración del edificio desde hace 15 años, conoce a todos los residentes
**Nivel técnico:** Básico - usa el computador solo para tareas específicas de trabajo, prefiere lo simple y directo

### CONTEXTO

**Ubicación de trabajo:** Oficina de administración del estacionamiento, Bloque 28
**Horario:** Lunes a Viernes, 8:00 AM - 4:00 PM
**Experiencia:** Lleva 3 meses usando el nuevo sistema digital, antes todo era en papel
**Preferencias:** Instrucciones claras, botones grandes, confirmaciones visuales

### HISTORIA

> **Como** operadora de la administración del estacionamiento,
> **Quiero** registrar pagos en efectivo de forma rápida y sencilla cuando los residentes vienen a pagar presencialmente,
> **Para** entregarles su recibo oficial inmediatamente sin complicaciones técnicas.

### CRITERIOS DE ACEPTACIÓN

1. ✅ Carmen puede buscar clientes de forma simple (por nombre, apartamento o cédula)
2. ✅ El sistema muestra claramente cuánto debe el cliente
3. ✅ Carmen puede registrar el pago en efectivo con pocos clicks
4. ✅ El sistema calcula automáticamente el monto en Bs según la tasa del día
5. ✅ Carmen puede generar e imprimir el recibo inmediatamente
6. ✅ El sistema muestra confirmación clara de que el pago fue registrado
7. ✅ Carmen puede ver un resumen de todos los pagos del día
8. ✅ La interfaz tiene botones grandes y texto legible
9. ✅ Los mensajes de error son claros y en lenguaje sencillo
10. ✅ Carmen puede deshacer un pago si se equivocó (antes de cerrar sesión)

### FLUJO DETALLADO

#### **Paso 1: Inicio de jornada (Lunes, 18 de enero, 8:15 AM)**

Carmen llega a la oficina, enciende el computador y abre el navegador.

- Accede a: `http://localhost/controldepagosestacionamiento`
- Ingresa sus credenciales:
  - **Usuario:** `8765432`
  - **Contraseña:** `Carmen2025!`
- Click en **[Iniciar Sesión]**

#### **Paso 2: Dashboard del Operador**

Carmen ve su dashboard con información clara:

```
┌─────────────────────────────────────────────┐
│ Buenos días, Carmen Méndez                  │
│ Rol: Operador                              │
│ Fecha: Lunes, 18 de Enero 2025            │
├─────────────────────────────────────────────┤
│ RESUMEN DEL DÍA                            │
│                                            │
│ 💰 Pagos registrados hoy: 3                │
│ 💵 Total recaudado: $8.00 USD              │
│ 📋 Comprobantes pendientes: 5              │
│                                            │
│ [👥 Buscar Cliente]                        │
│ [📝 Ver Pagos del Día]                     │
│ [✅ Aprobar Comprobantes]                  │
└─────────────────────────────────────────────┘
```

#### **Paso 3: Llegada del cliente (9:30 AM)**

El Sr. Pedro Jiménez del Bloque 31, Apto 5-A llega a la oficina con efectivo en dólares.

**Pedro:** "Buenos días Carmen, vengo a pagar mi mensualidad de enero"
**Carmen:** "Buenos días Pedro, un momento que te busco en el sistema"

Carmen hace click en **[👥 Buscar Cliente]**

#### **Paso 4: Búsqueda del cliente**

Sistema muestra pantalla de búsqueda simple:

```
┌─────────────────────────────────────────────┐
│   BUSCAR CLIENTE                           │
├─────────────────────────────────────────────┤
│                                            │
│ Buscar por:                                │
│ ○ Nombre                                   │
│ ● Apartamento ✓                           │
│ ○ Cédula                                   │
│                                            │
│ Bloque:                                    │
│ [31▼]                                      │
│                                            │
│ Apartamento:                               │
│ [5-A________]                              │
│                                            │
│ [🔍 BUSCAR]                                │
│                                            │
└─────────────────────────────────────────────┘
```

- Carmen selecciona **Apartamento**
- Selecciona **Bloque: 31**
- Escribe **Apartamento: 5-A**
- Click en **[🔍 BUSCAR]** (botón grande y visible)

#### **Paso 5: Resultado de búsqueda**

Sistema encuentra al cliente y muestra su información:

```
┌─────────────────────────────────────────────┐
│   CLIENTE ENCONTRADO                       │
├─────────────────────────────────────────────┤
│                                            │
│ 👤 Nombre: Pedro Jiménez                   │
│ 🏢 Apartamento: Bloque 31, Apto 5-A       │
│ 📱 Teléfono: +58 412 5554433              │
│                                            │
│ 🚗 Controles: 2 (Posición 127-A, 127-B)   │
│                                            │
│ 💰 MENSUALIDAD PENDIENTE:                  │
│                                            │
│ Mes: ENERO 2025                           │
│ Monto: $2.00 USD                           │
│ En Bs: 72.80 (Tasa: 36.40)                │
│ Vencimiento: 31/01/2025                   │
│ Estado: PENDIENTE ⏰                        │
│                                            │
│ [💵 Registrar Pago en Efectivo]           │
│ [🔙 Buscar Otro Cliente]                  │
│                                            │
└─────────────────────────────────────────────┘
```

**Carmen:** "Pedro, son $2 dólares por los dos controles"
**Pedro:** "Perfecto, aquí están" *entrega 2 billetes de $1*

Carmen hace click en **[💵 Registrar Pago en Efectivo]**

#### **Paso 6: Registro del pago**

Sistema muestra formulario simplificado:

```
┌─────────────────────────────────────────────┐
│   REGISTRAR PAGO EN EFECTIVO               │
├─────────────────────────────────────────────┤
│                                            │
│ Cliente: Pedro Jiménez                     │
│ Apartamento: Bloque 31, Apto 5-A          │
│                                            │
│ Mensualidad: ENERO 2025                   │
│                                            │
│ Forma de pago:                             │
│ ● USD Efectivo ✓                          │
│ ○ Bs Efectivo                              │
│                                            │
│ ┌─────────────────────────────────────┐   │
│ │ MONTO A PAGAR                       │   │
│ │                                     │   │
│ │ 💵 $2.00 USD                        │   │
│ │                                     │   │
│ │ (Equivalente: Bs 72.80)             │   │
│ └─────────────────────────────────────┘   │
│                                            │
│ Notas (opcional):                          │
│ [________________________________]         │
│                                            │
│ [❌ Cancelar]  [✅ CONFIRMAR PAGO]         │
│                                            │
└─────────────────────────────────────────────┘
```

Todo está pre-llenado, Carmen solo necesita verificar y confirmar.

- **Forma de pago:** USD Efectivo (ya seleccionado)
- **Monto:** $2.00 USD (calculado automáticamente)
- Carmen hace click en **[✅ CONFIRMAR PAGO]** (botón verde grande)

#### **Paso 7: Confirmación y generación de recibo**

Sistema procesa el pago y muestra confirmación:

```
┌─────────────────────────────────────────────┐
│   ✅ PAGO REGISTRADO EXITOSAMENTE          │
├─────────────────────────────────────────────┤
│                                            │
│ Cliente: Pedro Jiménez                     │
│ Recibo: EST-000125                         │
│ Monto: $2.00 USD                           │
│ Fecha: 18/01/2025 09:32 AM                │
│                                            │
│ El recibo se generó correctamente.        │
│                                            │
│ [🖨️ IMPRIMIR RECIBO]                      │
│ [📧 Enviar por Email]                     │
│ [🏠 Volver al Inicio]                     │
│ [👥 Buscar Otro Cliente]                  │
│                                            │
└─────────────────────────────────────────────┘
```

Carmen hace click en **[🖨️ IMPRIMIR RECIBO]**

La impresora empieza a imprimir el recibo automáticamente.

#### **Paso 8: Entrega del recibo**

La impresora termina de imprimir. Carmen toma el recibo y se lo entrega a Pedro.

**Carmen:** "Aquí está tu recibo Pedro, recibo número EST-000125. Quedaste al día con enero"
**Pedro:** "Muchas gracias Carmen, que tengas buen día"
**Carmen:** "Igualmente Pedro, cuídate"

Carmen hace click en **[🏠 Volver al Inicio]**

#### **Paso 9: Registro de otro pago - Cliente con Bs efectivo (10:15 AM)**

Llega la Sra. Ana Rodríguez con bolívares en efectivo.

**Ana:** "Carmen, vengo a pagar con bolívares"
**Carmen:** "Perfecto Ana, déjame buscarte"

Carmen repite el proceso de búsqueda:
- **Bloque:** 29
- **Apartamento:** 3-B

Sistema muestra que Ana debe $1.00 USD (tiene 1 control).

**Carmen:** "Ana, en bolívares son 36 con 40"
**Ana:** "Tengo 40 bolívares, dame el vuelto en el recibo para el próximo mes"

```
┌─────────────────────────────────────────────┐
│   REGISTRAR PAGO EN EFECTIVO               │
├─────────────────────────────────────────────┤
│ Cliente: Ana Rodríguez                     │
│ Apartamento: Bloque 29, Apto 3-B          │
│                                            │
│ Forma de pago:                             │
│ ○ USD Efectivo                             │
│ ● Bs Efectivo ✓                           │
│                                            │
│ ┌─────────────────────────────────────┐   │
│ │ MONTO A PAGAR                       │   │
│ │                                     │   │
│ │ Bs 36.40                            │   │
│ │                                     │   │
│ │ (Equivalente: $1.00 USD)            │   │
│ └─────────────────────────────────────┘   │
│                                            │
│ Notas:                                     │
│ [Cliente pagó con Bs 40___________]       │
│                                            │
│ [✅ CONFIRMAR PAGO]                        │
│                                            │
└─────────────────────────────────────────────┘
```

Carmen anota en **Notas:** "Cliente pagó con Bs 40" y confirma.

Sistema genera recibo **EST-000126** e imprime automáticamente.

#### **Paso 10: Ver resumen del día (3:45 PM - Fin de jornada)**

Antes de cerrar, Carmen revisa los pagos del día:

Click en **[📝 Ver Pagos del Día]**

```
┌─────────────────────────────────────────────┐
│   PAGOS DEL DÍA - 18/01/2025              │
├─────────────────────────────────────────────┤
│                                            │
│ #  | Hora  | Cliente          | Monto     │
│ ---|-------|------------------|-----------|
│ 1  | 08:45 | Luis Pérez       | $1.00 USD │
│ 2  | 09:10 | Carla Suárez     | $3.00 USD │
│ 3  | 09:32 | Pedro Jiménez    | $2.00 USD │
│ 4  | 10:15 | Ana Rodríguez    | Bs 36.40  │
│ 5  | 11:20 | José Martínez    | $1.00 USD │
│ 6  | 02:30 | Elena Vásquez    | $2.00 USD │
│                                            │
│ TOTAL DEL DÍA:                             │
│ 💵 USD: $9.00                              │
│ 💵 Bs: 36.40                               │
│                                            │
│ [📥 Exportar a Excel]                     │
│ [🖨️ Imprimir Resumen]                     │
│ [🏠 Volver]                                │
│                                            │
└─────────────────────────────────────────────┘
```

Carmen revisa que todo esté correcto, cierra sesión y termina su jornada.

### CASOS EDGE

#### **Caso 1: Carmen se equivoca de cliente**

- Carmen busca "Bloque 30, Apto 2-A" pero era "Bloque 31, Apto 2-A"
- Antes de confirmar el pago, revisa y se da cuenta del error
- Click en **[❌ Cancelar]**
- Sistema vuelve a la búsqueda sin registrar nada
- Carmen busca de nuevo correctamente

#### **Caso 2: Cliente no tiene mensualidad pendiente**

- Carmen busca al cliente
- Sistema muestra: "✅ Este cliente está al día. No tiene pagos pendientes"
- Botón [Registrar Pago] está deshabilitado
- Carmen le informa al cliente: "Ya estás al día Pedro, no debes nada"

#### **Caso 3: Cliente tiene 4 meses de mora y control bloqueado**

- Carmen busca al cliente
- Sistema muestra alerta roja:

```
⚠️ CLIENTE CON CONTROL BLOQUEADO
Este cliente debe 4 meses ($4.00 USD)
Debe pagar deuda + reconexión ($2.00)
Total a pagar: $6.00 USD

[💰 Registrar Pago con Reconexión]
```

- Carmen le explica al cliente
- Si el cliente paga, sistema desbloquea automáticamente el control

#### **Caso 4: Se va la luz mientras imprime el recibo**

- Carmen registró el pago exitosamente
- Sistema muestra: "✅ PAGO REGISTRADO - Recibo: EST-000130"
- Al intentar imprimir, se va la luz
- Cuando vuelve la luz, Carmen inicia sesión
- Click en **[📝 Ver Pagos del Día]**
- Busca el recibo EST-000130 en la lista
- Click en el recibo y opción **[🖨️ Reimprimir]**
- Recibo se imprime correctamente

#### **Caso 5: Cliente llega con cambio inexacto en Bs**

- Cliente debe Bs 36.40
- Trae Bs 35 (le falta)
- Carmen intenta confirmar el pago
- Sistema valida y muestra: "❌ El monto ingresado (Bs 35.00) es menor al monto adeudado (Bs 36.40)"
- Carmen le informa al cliente que necesita Bs 1.40 más

#### **Caso 6: Carmen olvida cerrar sesión al irse a almorzar**

- Carmen se va a almorzar sin cerrar sesión (12:00 PM)
- Sesión expira automáticamente después de 30 minutos (12:30 PM)
- Cuando regresa (1:00 PM) e intenta usar el sistema
- Sistema muestra: "⚠️ Su sesión expiró por inactividad. Por favor, inicie sesión nuevamente"
- Carmen inicia sesión de nuevo sin problemas

#### **Caso 7: Impresora sin papel**

- Carmen confirma el pago exitosamente
- Click en [🖨️ IMPRIMIR RECIBO]
- Impresora no tiene papel
- Sistema muestra: "⚠️ Error al imprimir. Verifique que la impresora esté encendida y tenga papel"
- Opciones: **[🔄 Reintentar]** o **[📧 Enviar por Email]**
- Carmen coloca papel y hace click en [🔄 Reintentar]
- Recibo se imprime correctamente

### NOTAS TÉCNICAS

**Tablas involucradas:**
- `usuarios` (id=3, rol=operador, Carmen Méndez)
- `usuarios` (id=58, rol=cliente, Pedro Jiménez)
- `apartamentos` (id=95, bloque=31, numero=5-A)
- `apartamento_usuario` (id=72, apartamento_id=95, usuario_id=58)
- `controles_estacionamiento` (posicion=127, receptor=A y B, apartamento_usuario_id=72)
- `mensualidades` (id=450, mes=1, año=2025, apartamento_usuario_id=72)
- `tasa_cambio_bcv` (fecha=2025-01-18, tasa=36.40)
- `pagos` (id=125, mensualidad_id=450, monto_usd=2.00, moneda_pago=usd_efectivo, registrado_por=3, estado_comprobante=no_aplica)

**Recibos generados:**
- `uploads/recibos/2025/01/EST-000125.pdf` (Pedro Jiménez, $2.00 USD)
- `uploads/recibos/2025/01/EST-000126.pdf` (Ana Rodríguez, Bs 36.40)

**Logs registrados:**
```sql
- Login: usuario_id=3 (Carmen), ip=192.168.1.50, fecha_hora='2025-01-18 08:15:00'
- Búsqueda cliente: usuario_id=3, busqueda='bloque:31,apto:5-A', resultado_id=58
- Pago registrado: pago_id=125, registrado_por=3, cliente_id=58, monto_usd=2.00, moneda=usd_efectivo
- Recibo impreso: recibo_numero='EST-000125', impreso_por=3
- Búsqueda cliente: usuario_id=3, busqueda='bloque:29,apto:3-B', resultado_id=62
- Pago registrado: pago_id=126, registrado_por=3, cliente_id=62, monto_bs=36.40, moneda=bs_efectivo
- Recibo impreso: recibo_numero='EST-000126', impreso_por=3
- Consulta resumen día: usuario_id=3, fecha='2025-01-18'
- Logout: usuario_id=3, fecha_hora='2025-01-18 16:00:00'
```

**Diseño UX para operador (nivel básico):**
- Botones grandes y coloridos (mínimo 48px altura)
- Texto legible (mínimo 16px)
- Iconos visuales para cada acción (🔍 👥 💵 🖨️)
- Confirmaciones visuales claras (colores verde ✅, rojo ❌)
- Máximo 3-4 opciones por pantalla
- Flujo lineal sin bifurcaciones complejas
- Mensajes de error en lenguaje sencillo (sin tecnicismos)
- Pre-llenado automático de campos cuando sea posible
- Atajos de teclado opcionales (Enter para confirmar, Esc para cancelar)

**Validaciones del sistema:**
- Monto pagado ≥ Monto adeudado
- No permitir pagos duplicados para el mismo mes
- Validar que la tasa BCV esté actualizada (máximo 24 horas)
- Confirmar disponibilidad de impresora antes de registrar pago
- Bloquear pago si cliente tiene control suspendido (requiere aprobación admin)

---

## USER STORY #4: ADMINISTRADOR GESTIONA MOROSIDAD Y RECONEXIÓN

### PERSONAJE

**Nombre:** Ing. Miguel Sánchez
**Rol:** Administrador
**Edad:** 38 años
**Situación:** Ingeniero en sistemas, administra el estacionamiento desde hace 2 años
**Nivel técnico:** Avanzado - domina sistemas, bases de datos, Excel avanzado, conoce programación

### CONTEXTO

**Responsabilidades:** Supervisión completa del sistema, toma de decisiones, configuraciones críticas
**Horario:** Flexible, trabaja remotamente y presencialmente
**Herramientas:** Laptop, acceso completo al sistema, reportes avanzados
**Preferencias:** Eficiencia, automatización, datos precisos, dashboards con métricas

### HISTORIA

> **Como** administrador del sistema de estacionamiento,
> **Quiero** gestionar eficientemente la morosidad de clientes, enviar notificaciones automatizadas y procesar reconexiones,
> **Para** mantener la salud financiera del estacionamiento y reducir la carga administrativa manual.

### CRITERIOS DE ACEPTACIÓN

1. ✅ Miguel puede ver un dashboard con métricas clave de morosidad
2. ✅ El sistema muestra lista filtrable de clientes morosos (3+ y 4+ meses)
3. ✅ Miguel puede enviar notificaciones masivas a clientes morosos
4. ✅ El sistema bloquea automáticamente controles con 4+ meses de mora
5. ✅ Miguel puede procesar pagos de reconexión ($2 USD adicionales)
6. ✅ El sistema desbloquea automáticamente controles al pagar reconexión
7. ✅ Miguel puede generar reportes detallados de morosidad
8. ✅ Miguel puede exportar datos a Excel para análisis externo
9. ✅ Miguel puede configurar excepciones (exoneraciones) para casos especiales
10. ✅ El sistema registra todas las acciones en logs auditables

### FLUJO DETALLADO

#### **Paso 1: Acceso al sistema (Viernes, 22 de enero, 10:00 AM)**

Miguel accede desde su laptop en la oficina.

- URL: `http://localhost/controldepagosestacionamiento`
- Login:
  - **Usuario:** `admin@estacionamiento.com`
  - **Contraseña:** `MiguelAdmin2025!`
- Sistema redirige a Dashboard de Administrador

#### **Paso 2: Dashboard de Administrador con métricas**

Miguel ve un dashboard completo con visualización de datos:

```
┌─────────────────────────────────────────────────────────────────┐
│ 🏢 DASHBOARD ADMINISTRADOR - ESTACIONAMIENTO CARICUAO UD 5     │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│ RESUMEN EJECUTIVO - ENERO 2025                                 │
│                                                                 │
│ ┌──────────────┐  ┌──────────────┐  ┌──────────────┐         │
│ │ 👥 CLIENTES  │  │ 💰 INGRESOS  │  │ 📊 OCUPACIÓN │         │
│ │              │  │              │  │              │         │
│ │    250       │  │  $198 USD    │  │    87%       │         │
│ │   activos    │  │  este mes    │  │  218/250     │         │
│ └──────────────┘  └──────────────┘  └──────────────┘         │
│                                                                 │
│ ⚠️ ALERTAS DE MOROSIDAD                                        │
│                                                                 │
│ 🟡 Mora 3 meses (alerta):       12 clientes - $24.00 USD      │
│ 🔴 Mora 4+ meses (bloqueados):   5 clientes - $22.00 USD      │
│                                                                 │
│ Total deuda pendiente: $46.00 USD                             │
│                                                                 │
│ [⚠️ VER MOROSOS]  [📧 Enviar Notificaciones]  [📊 Reportes]  │
│                                                                 │
│ ACCIONES RÁPIDAS                                               │
│ [👥 Gestionar Usuarios] [🚗 Mapa de Controles]               │
│ [💵 Configurar Tarifas] [📈 Actualizar Tasa BCV]             │
│ [📥 Importar Excel] [⚙️ Configuración]                        │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

Miguel nota que hay **5 clientes con 4+ meses de mora** (bloqueados).

Click en **[⚠️ VER MOROSOS]**

#### **Paso 3: Lista de clientes morosos**

Sistema muestra tabla detallada:

```
┌──────────────────────────────────────────────────────────────────┐
│   GESTIÓN DE MOROSIDAD                                          │
├──────────────────────────────────────────────────────────────────┤
│                                                                  │
│ Filtros:                                                         │
│ ☑ 3 meses (alerta)  ☑ 4+ meses (bloqueados)  □ Exonerados     │
│ Buscar: [_________________________]  [🔍]                       │
│                                                                  │
│ CLIENTES CON MORA 4+ MESES (CONTROLES BLOQUEADOS)              │
│                                                                  │
│ #  | Cliente           | Apto      | Meses | Deuda   | Estado  │
│ ---|-------------------|-----------|-------|---------|----------|
│ 1  | Luis Fernández    | Blq 27-3A | 5     | $5.00   | 🔴 BLOQ │
│ 2  | Sandra Mora       | Blq 28-8B | 4     | $8.00   | 🔴 BLOQ │
│ 3  | Carlos Torres     | Blq 30-2C | 6     | $3.00   | 🔴 BLOQ │
│ 4  | Elena Gutiérrez   | Blq 29-5A | 4     | $4.00   | 🔴 BLOQ │
│ 5  | José Ramos        | Blq 31-7B | 7     | $2.00   | 🔴 BLOQ │
│                                                                  │
│ TOTAL DEUDA: $22.00 USD                                         │
│                                                                  │
│ [Acciones]  [📧 Notificar Todos] [📊 Exportar] [🔄 Refrescar] │
│                                                                  │
│ ─────────────────────────────────────────────────────────────────│
│                                                                  │
│ CLIENTES CON MORA 3 MESES (ALERTA - AÚN NO BLOQUEADOS)         │
│                                                                  │
│ #  | Cliente           | Apto      | Meses | Deuda   | Estado  │
│ ---|-------------------|-----------|-------|---------|----------|
│ 1  | Ana Pérez         | Blq 27-1B | 3     | $3.00   | 🟡 MORA │
│ 2  | Roberto Silva     | Blq 29-4A | 3     | $1.00   | 🟡 MORA │
│ 3  | Carmen López      | Blq 30-6C | 3     | $2.00   | 🟡 MORA │
│ ... (9 más)                                                     │
│                                                                  │
│ TOTAL: 12 clientes - $24.00 USD                                │
│                                                                  │
│ [📧 Enviar Alerta a Todos]                                     │
│                                                                  │
└──────────────────────────────────────────────────────────────────┘
```

#### **Paso 4: Enviar notificaciones masivas**

Miguel decide enviar recordatorio a los 12 clientes con 3 meses de mora.

Click en **[📧 Enviar Alerta a Todos]** (sección 3 meses)

Sistema muestra preview de notificación:

```
┌─────────────────────────────────────────────┐
│   ENVIAR NOTIFICACIÓN MASIVA                │
├─────────────────────────────────────────────┤
│                                            │
│ Destinatarios: 12 clientes                 │
│ Tipo: Alerta de mora (3 meses)            │
│                                            │
│ ┌─────────────────────────────────────┐   │
│ │ PREVIEW DEL EMAIL:                  │   │
│ │                                     │   │
│ │ Asunto: ⚠️ Recordatorio de Pago -   │   │
│ │         3 Meses Pendientes          │   │
│ │                                     │   │
│ │ Estimado(a) [Nombre],               │   │
│ │                                     │   │
│ │ Le recordamos que tiene 3 meses de  │   │
│ │ mensualidad pendientes:             │   │
│ │                                     │   │
│ │ • Monto adeudado: $[X] USD          │   │
│ │ • Último pago: [Fecha]              │   │
│ │                                     │   │
│ │ ⚠️ IMPORTANTE:                       │   │
│ │ Si no regulariza su pago antes del  │   │
│ │ próximo mes, su control será        │   │
│ │ bloqueado automáticamente y deberá  │   │
│ │ pagar $2 USD adicionales por        │   │
│ │ reconexión.                         │   │
│ │                                     │   │
│ │ Formas de pago: ...                 │   │
│ └─────────────────────────────────────┘   │
│                                            │
│ También se enviará notificación al        │
│ sistema (campana de alertas).             │
│                                            │
│ [❌ Cancelar]  [✅ ENVIAR A TODOS]         │
│                                            │
└─────────────────────────────────────────────┘
```

Miguel revisa el contenido y hace click en **[✅ ENVIAR A TODOS]**

Sistema procesa:

```
⏳ Enviando notificaciones...

✅ Emails enviados: 12/12
✅ Notificaciones en sistema: 12/12

Total procesados: 12 clientes
Fallos: 0

[✓ Cerrar]
```

#### **Paso 5: Gestionar caso de reconexión**

Miguel nota que Sandra Mora (4 meses de mora) acaba de llamar diciendo que pagará hoy.

Click en **Sandra Mora** en la lista de morosos bloqueados.

Sistema muestra detalle del cliente:

```
┌─────────────────────────────────────────────┐
│   DETALLE DE MOROSIDAD - SANDRA MORA       │
├─────────────────────────────────────────────┤
│                                            │
│ 👤 Cliente: Sandra Mora                    │
│ 🏢 Apartamento: Bloque 28, Apto 8-B       │
│ 🚗 Controles: 4 (Pos: 88A, 88B, 89A, 89B) │
│ 📱 Teléfono: +58 424 5551234              │
│ 📧 Email: sandra.mora@email.com           │
│                                            │
│ ⚠️ ESTADO: BLOQUEADO (4 meses de mora)     │
│                                            │
│ DEUDA DETALLADA:                           │
│ • Octubre 2024:  $4.00 USD  ❌ VENCIDO    │
│ • Noviembre 2024: $4.00 USD  ❌ VENCIDO   │
│ • Diciembre 2024: $4.00 USD  ❌ VENCIDO   │
│ • Enero 2025:    $4.00 USD  ❌ VENCIDO    │
│                                            │
│ Subtotal deuda: $16.00 USD                │
│ Reconexión:     $2.00 USD                 │
│ ───────────────────────────                │
│ TOTAL A PAGAR:  $18.00 USD                │
│                                            │
│ Último pago: 28/09/2024 (4 meses atrás)  │
│ Control bloqueado desde: 05/01/2025       │
│                                            │
│ [💰 Registrar Pago + Reconexión]          │
│ [📧 Enviar Notificación Individual]       │
│ [⚙️ Gestionar Exoneración]                 │
│ [📜 Ver Historial Completo]               │
│                                            │
└─────────────────────────────────────────────┘
```

Miguel hace click en **[💰 Registrar Pago + Reconexión]**

#### **Paso 6: Procesar pago con reconexión**

Sandra llega a la oficina con $18 USD en efectivo. El operador Carmen la registra:

```
┌─────────────────────────────────────────────┐
│   PAGO CON RECONEXIÓN                      │
├─────────────────────────────────────────────┤
│                                            │
│ Cliente: Sandra Mora                       │
│ Apartamento: Bloque 28, Apto 8-B          │
│                                            │
│ DEUDA PENDIENTE:                           │
│ • 4 mensualidades:  $16.00 USD            │
│ • Reconexión:        $2.00 USD            │
│ ─────────────────────────────              │
│ • TOTAL:            $18.00 USD            │
│                                            │
│ Forma de pago:                             │
│ ● USD Efectivo ✓                          │
│ ○ Bs Transferencia                         │
│ ○ Bs Efectivo                              │
│                                            │
│ ☑ Incluir cargo de reconexión ($2 USD)    │
│                                            │
│ Notas:                                     │
│ [Cliente pagó deuda completa + reconex.]  │
│                                            │
│ ⚠️ Al confirmar este pago:                 │
│ • Se marcarán como pagados los 4 meses    │
│ • Se desbloqueará automáticamente el      │
│   control de estacionamiento              │
│ • Se generará recibo oficial              │
│                                            │
│ [❌ Cancelar]  [✅ CONFIRMAR PAGO]         │
│                                            │
└─────────────────────────────────────────────┘
```

Carmen (operador) o Miguel confirman el pago.

Sistema procesa:

```
✅ PAGO PROCESADO EXITOSAMENTE

• 4 mensualidades pagadas (Oct-Ene)
• Reconexión registrada ($2 USD)
• Control DESBLOQUEADO automáticamente
• Recibo generado: EST-000145

El control de Sandra Mora ya está activo.

[🖨️ Imprimir Recibo]  [📧 Enviar Email]  [✓ Cerrar]
```

#### **Paso 7: Generar reporte de morosidad (fin del día)**

Miguel quiere generar un reporte mensual de morosidad para presentar a la junta de condominio.

Click en **[📊 Reportes]** desde el dashboard principal.

Selecciona **"Reporte de Morosidad"**

```
┌─────────────────────────────────────────────┐
│   GENERAR REPORTE DE MOROSIDAD             │
├─────────────────────────────────────────────┤
│                                            │
│ Período:                                   │
│ Desde: [01/01/2025]  Hasta: [31/01/2025]  │
│                                            │
│ Filtros:                                   │
│ ☑ Clientes con mora 1+ meses              │
│ ☑ Clientes con mora 3+ meses (alerta)    │
│ ☑ Clientes con mora 4+ meses (bloqueados) │
│ □ Incluir clientes exonerados              │
│                                            │
│ Agrupar por:                               │
│ ● Nivel de mora                            │
│ ○ Bloque                                   │
│ ○ Monto adeudado                           │
│                                            │
│ Formato de salida:                         │
│ ● Excel (.xlsx)                            │
│ ○ PDF                                      │
│ ○ CSV                                      │
│                                            │
│ Incluir:                                   │
│ ☑ Gráficos estadísticos                   │
│ ☑ Tabla resumen ejecutivo                 │
│ ☑ Lista detallada por cliente             │
│ ☑ Historial de pagos                      │
│                                            │
│ [🔄 Previsualizar]  [📥 GENERAR REPORTE]  │
│                                            │
└─────────────────────────────────────────────┘
```

Miguel hace click en **[📥 GENERAR REPORTE]**

Sistema genera archivo Excel:

```
⏳ Generando reporte...

✅ Reporte generado exitosamente

Archivo: Reporte_Morosidad_Enero_2025.xlsx
Tamaño: 245 KB
Registros: 17 clientes morosos

[📥 Descargar]  [📧 Enviar por Email]  [✓ Cerrar]
```

Miguel descarga el archivo y lo abre en Excel para revisarlo antes de presentarlo.

#### **Paso 8: Configurar exoneración especial**

Miguel recibe una solicitud de la junta: exonerar a la Sra. Carmen López (Blq 30-6C) por 3 meses debido a problemas de salud.

Miguel busca a Carmen López en el sistema:

**Dashboard** → **[👥 Gestionar Usuarios]** → Buscar "Carmen López"

Click en el perfil de Carmen López:

```
┌─────────────────────────────────────────────┐
│   PERFIL - CARMEN LÓPEZ                    │
├─────────────────────────────────────────────┤
│                                            │
│ ... (datos personales) ...                │
│                                            │
│ CONFIGURACIONES ESPECIALES                 │
│                                            │
│ Exoneración:                               │
│ ○ No exonerado                             │
│ ● Exonerar temporalmente                   │
│                                            │
│ Período de exoneración:                    │
│ Desde: [01/02/2025]  Hasta: [30/04/2025]  │
│                                            │
│ Motivo (requerido):                        │
│ [Problemas de salud - Aprobado por junta  │
│  de condominio el 22/01/2025]            │
│                                            │
│ Efectos de la exoneración:                 │
│ • No se generarán mensualidades en el     │
│   período seleccionado                    │
│ • La deuda actual (3 meses) se mantiene   │
│ • El control permanece activo             │
│                                            │
│ [❌ Cancelar]  [✅ APLICAR EXONERACIÓN]    │
│                                            │
└─────────────────────────────────────────────┘
```

Miguel completa los datos y confirma.

Sistema registra la exoneración y no generará mensualidades para Carmen en feb-mar-abr 2025.

### CASOS EDGE

#### **Caso 1: Cliente paga solo deuda pero no reconexión**

- Cliente con 4 meses de mora ($8 USD + $2 reconexión = $10 total)
- Llega con solo $8 USD
- Sistema detecta que falta reconexión
- Mensaje: "⚠️ Para desbloquear el control debe pagar deuda ($8) + reconexión ($2). Falta: $2 USD"
- Control permanece bloqueado hasta pagar completo

#### **Caso 2: Cliente exonerado intenta pagar meses exonerados**

- Cliente exonerado de feb-mar-abr
- Intenta pagar febrero
- Sistema muestra: "ℹ️ Este cliente está exonerado para el período feb-mar-abr 2025. No hay mensualidad generada"

#### **Caso 3: Error al enviar emails masivos**

- Miguel envía notificaciones a 12 clientes
- 10 emails se envían exitosamente
- 2 emails fallan (direcciones inválidas)
- Sistema muestra reporte detallado:

```
✅ Enviados: 10/12
❌ Fallos: 2/12

Clientes con error:
• Ana Pérez (ana@invalido): Email no válido
• Roberto Silva (sin email): Email no registrado

Las notificaciones en sistema se enviaron
correctamente para todos los clientes.

[📧 Reenviar Manualmente]  [✓ Cerrar]
```

#### **Caso 4: Intento de exonerar sin motivo**

- Miguel intenta exonerar a un cliente
- Deja el campo "Motivo" vacío
- Sistema valida: "❌ Debe especificar un motivo de exoneración para fines de auditoría"

#### **Caso 5: Cliente paga justo cuando se completan 4 meses**

- Cliente tiene 3 meses y 29 días de mora
- Sistema ejecuta CRON a medianoche y detecta 4 meses
- Control se bloquea automáticamente (5:00 AM)
- Cliente llega a pagar a las 9:00 AM
- Sistema muestra que ya está bloqueado y debe pagar reconexión
- Log auditable registra: "Control bloqueado automáticamente por CRON job - Fecha: 23/01/2025 05:00:00"

### NOTAS TÉCNICAS

**Tablas involucradas:**
- `usuarios` (id=1, rol=administrador, Miguel Sánchez)
- `mensualidades` (múltiples registros con estado='vencido', filtrados por fecha)
- `pagos` (registro de reconexiones con es_reconexion=TRUE)
- `controles_estacionamiento` (actualización de estado='bloqueado' → 'activo')
- `notificaciones` (envío masivo de alertas)
- `logs_actividad` (registro de todas las acciones críticas)

**Queries complejas:**
```sql
-- Obtener clientes con 4+ meses de mora
SELECT
    u.id, u.nombre_completo, a.bloque, a.numero_apartamento,
    COUNT(m.id) as meses_mora,
    SUM(m.monto_usd) as deuda_total
FROM usuarios u
JOIN apartamento_usuario au ON au.usuario_id = u.id
JOIN apartamento a ON a.id = au.apartamento_id
JOIN mensualidades m ON m.apartamento_usuario_id = au.id
WHERE m.estado IN ('vencido', 'pendiente')
  AND m.fecha_vencimiento < DATE_SUB(CURDATE(), INTERVAL 4 MONTH)
  AND u.exonerado = FALSE
GROUP BY u.id
HAVING meses_mora >= 4;

-- Proceso de bloqueo automático (CRON)
UPDATE controles_estacionamiento ce
SET estado = 'bloqueado',
    fecha_estado = NOW(),
    motivo_estado = 'Bloqueado por morosidad (4+ meses)'
WHERE apartamento_usuario_id IN (
    SELECT au.id FROM apartamento_usuario au
    JOIN mensualidades m ON m.apartamento_usuario_id = au.id
    WHERE m.estado = 'vencido'
    GROUP BY au.id
    HAVING COUNT(*) >= 4
);
```

**Proceso de reconexión:**
1. Cliente paga deuda completa ($16) + reconexión ($2)
2. Sistema registra 4 pagos individuales (uno por cada mes)
3. Sistema registra 1 pago adicional con `es_reconexion = TRUE`
4. Trigger automático actualiza `controles_estacionamiento.estado = 'activo'`
5. Se genera 1 recibo que incluye todos los conceptos
6. Notificación enviada al cliente confirmando desbloqueo

**Logs críticos registrados:**
```sql
- Consulta morosos: usuario_id=1, accion='consultar_morosidad', filtros='3_meses,4_meses'
- Envío masivo emails: usuario_id=1, destinatarios=12, exitosos=12, fallos=0
- Pago reconexión: usuario_id=1, cliente_id=58, monto_reconexion=2.00, meses_pagados=4
- Desbloqueo control: control_id=88, apartamento_usuario_id=72, desbloqueado_por=1
- Exoneración aplicada: usuario_id=62, exonerado_por=1, motivo='Problemas de salud', desde='2025-02-01', hasta='2025-04-30'
- Reporte generado: tipo='morosidad', formato='xlsx', registros=17, generado_por=1
```

**Dashboard métricas (queries en tiempo real):**
- Total clientes activos
- Total ingresos del mes
- % Ocupación de controles
- Clientes con mora 3 meses
- Clientes con mora 4+ meses (bloqueados)
- Deuda total pendiente

**Automatización (CRON jobs):**
- `verificar_bloqueos.php` - Se ejecuta diariamente a las 5:00 AM
- Busca clientes con 4+ meses de mora
- Bloquea controles automáticamente
- Envía notificación al administrador con reporte
- Envía notificación al cliente informando del bloqueo

---

## USER STORY #5: CONSULTOR GENERA REPORTE MENSUAL DE INGRESOS

### PERSONAJE

**Nombre:** Sr. Alberto Rivas
**Rol:** Consultor
**Edad:** 52 años
**Situación:** Contador contratado por la junta de condominio para auditorías mensuales
**Nivel técnico:** Básico - usa Excel regularmente pero no sistemas complejos

### CONTEXTO

**Responsabilidades:** Revisar ingresos, generar reportes contables, auditar pagos
**Frecuencia de uso:** 1-2 veces por mes (fin de mes para reportes)
**Acceso:** Solo lectura, no puede modificar pagos ni configuraciones
**Preferencias:** Reportes claros en Excel, gráficos simples, datos verificables

### HISTORIA

> **Como** consultor contable del estacionamiento,
> **Quiero** generar reportes mensuales de ingresos con desglose por forma de pago y estadísticas,
> **Para** presentar informes precisos a la junta de condominio y mantener la contabilidad al día.

### CRITERIOS DE ACEPTACIÓN

1. ✅ Alberto puede acceder al sistema con permisos de solo lectura
2. ✅ Alberto puede generar reportes de ingresos filtrados por período
3. ✅ Los reportes muestran desglose por forma de pago (USD efectivo, Bs transferencia, Bs efectivo)
4. ✅ Alberto puede exportar reportes a Excel (.xlsx)
5. ✅ Los reportes incluyen gráficos básicos (barras, tortas)
6. ✅ Alberto puede ver lista detallada de todos los pagos del mes
7. ✅ Alberto puede verificar la tasa BCV usada en cada pago
8. ✅ El sistema muestra resumen ejecutivo con totales y promedios
9. ✅ Alberto NO puede modificar, eliminar ni aprobar pagos
10. ✅ Alberto puede imprimir reportes en formato PDF

### FLUJO DETALLADO

#### **Paso 1: Acceso al sistema (Lunes, 1 de febrero, 2:00 PM)**

Alberto accede desde su oficina para generar el reporte de enero.

- URL: `http://localhost/controldepagosestacionamiento`
- Login:
  - **Usuario:** `consultor@estacionamiento.com`
  - **Contraseña:** `AlbertoConsultor2025!`
- Sistema redirige a Dashboard de Consultor

#### **Paso 2: Dashboard del Consultor**

Alberto ve un dashboard simplificado con información de solo lectura:

```
┌─────────────────────────────────────────────┐
│ 📊 DASHBOARD CONSULTOR                     │
│ Alberto Rivas - Acceso de solo lectura     │
├─────────────────────────────────────────────┤
│                                            │
│ RESUMEN GENERAL - ENERO 2025              │
│                                            │
│ ┌────────────────────────────────────┐    │
│ │ INGRESOS DEL MES                   │    │
│ │                                    │    │
│ │  💵 USD: $198.00                   │    │
│ │  💵 Bs:  7,280.00                  │    │
│ │                                    │    │
│ │  Total pagos: 245                  │    │
│ │  Clientes al día: 233 (93%)        │    │
│ │  Clientes morosos: 17 (7%)         │    │
│ └────────────────────────────────────┘    │
│                                            │
│ ACCIONES DISPONIBLES                       │
│                                            │
│ [📊 Generar Reporte de Ingresos]          │
│ [📈 Estadísticas del Mes]                 │
│ [⚠️ Reporte de Morosidad]                 │
│ [🔍 Consultar Pagos]                      │
│                                            │
└─────────────────────────────────────────────┘
```

Alberto hace click en **[📊 Generar Reporte de Ingresos]**

#### **Paso 3: Configurar parámetros del reporte**

Sistema muestra opciones simples:

```
┌─────────────────────────────────────────────┐
│   GENERAR REPORTE DE INGRESOS              │
├─────────────────────────────────────────────┤
│                                            │
│ Período:                                   │
│ ○ Este mes (Enero 2025)                   │
│ ● Mes específico:                          │
│   Mes: [Enero ▼]  Año: [2025 ▼]          │
│ ○ Rango personalizado:                     │
│   Desde: [____]  Hasta: [____]            │
│                                            │
│ Desglosar por:                             │
│ ☑ Forma de pago (USD/Bs efectivo/transf.)│
│ ☑ Por día del mes                          │
│ ☑ Por bloque                               │
│ □ Por operador                             │
│                                            │
│ Incluir en el reporte:                     │
│ ☑ Resumen ejecutivo                        │
│ ☑ Tabla detallada de pagos                │
│ ☑ Gráficos estadísticos                   │
│ ☑ Tasa BCV promedio del mes               │
│ ☑ Comparativa con mes anterior            │
│                                            │
│ Formato de salida:                         │
│ ● Excel (.xlsx)                            │
│ ○ PDF                                      │
│                                            │
│ [🔄 Previsualizar]  [📥 GENERAR]          │
│                                            │
└─────────────────────────────────────────────┘
```

Alberto deja las opciones por defecto (Enero 2025, Excel) y hace click en **[📥 GENERAR]**

#### **Paso 4: Sistema genera el reporte**

Proceso de generación:

```
⏳ GENERANDO REPORTE...

✅ Consultando base de datos...
✅ Procesando 245 pagos...
✅ Calculando totales y promedios...
✅ Generando gráficos estadísticos...
✅ Creando archivo Excel...

✅ REPORTE GENERADO EXITOSAMENTE

Archivo: Reporte_Ingresos_Enero_2025.xlsx
Tamaño: 312 KB
Generado: 01/02/2025 14:05

[📥 DESCARGAR]  [📧 Enviar por Email]
```

Alberto hace click en **[📥 DESCARGAR]**

#### **Paso 5: Revisión del reporte en Excel**

Alberto abre el archivo Excel descargado. El reporte tiene 5 hojas:

**HOJA 1: RESUMEN EJECUTIVO**

```
┌────────────────────────────────────────────────────┐
│  REPORTE DE INGRESOS - ENERO 2025                 │
│  Estacionamiento Caricuao Ud 5 (Blq 27-32)       │
│  Generado: 01/02/2025 14:05                       │
│  Consultor: Alberto Rivas                          │
├────────────────────────────────────────────────────┤
│                                                    │
│  INGRESOS TOTALES                                 │
│  ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━  │
│                                                    │
│  USD Efectivo:        $145.00  (73%)              │
│  Bs Transferencia:    $38.00   (19%)              │
│  Bs Efectivo:         $15.00   (8%)               │
│  ─────────────────────────────                    │
│  TOTAL:               $198.00                      │
│                                                    │
│  En Bs (estimado):    Bs 7,208.40                 │
│  Tasa promedio BCV:   36.40                       │
│                                                    │
│  ESTADÍSTICAS                                     │
│  ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━  │
│                                                    │
│  Total de pagos:              245                  │
│  Clientes activos:            250                  │
│  Clientes que pagaron:        233 (93%)           │
│  Clientes morosos:            17 (7%)             │
│                                                    │
│  Pago promedio:               $0.85 USD           │
│  Día con más pagos:           05/01 (35 pagos)    │
│  Día con menos pagos:         25/01 (3 pagos)     │
│                                                    │
│  COMPARATIVA CON DICIEMBRE 2024                   │
│  ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━  │
│                                                    │
│  Diciembre 2024:      $185.00                     │
│  Enero 2025:          $198.00                     │
│  Variación:           +$13.00 (+7%)               │
│                                                    │
└────────────────────────────────────────────────────┘
```

**HOJA 2: DETALLE DE PAGOS (Tabla)**

```
| # | Fecha      | Recibo     | Cliente         | Apto    | Monto USD | Monto Bs | Forma Pago       | Operador      |
|---|------------|------------|-----------------|---------|-----------|----------|------------------|---------------|
| 1 | 05/01/2025 | EST-000089 | María González  | 29-7B   | $2.00     | -        | USD Efectivo     | Carmen Méndez |
| 2 | 05/01/2025 | EST-000090 | Pedro Jiménez   | 31-5A   | $2.00     | -        | USD Efectivo     | Carmen Méndez |
| 3 | 06/01/2025 | EST-000091 | Ana Rodríguez   | 29-3B   | $1.00     | -        | USD Efectivo     | Carmen Méndez |
| 4 | 06/01/2025 | EST-000092 | Luis Fernández  | 27-3A   | -         | Bs 72.80 | Bs Transferencia | Sistema       |
| 5 | 07/01/2025 | EST-000093 | Sandra Mora     | 28-8B   | $4.00     | -        | USD Efectivo     | Carmen Méndez |
...
| 245 | 31/01/2025 | EST-000333 | Roberto Silva | 29-4A   | $1.00     | -        | USD Efectivo     | Carmen Méndez |
```

**HOJA 3: GRÁFICOS**

- Gráfico de torta: Ingresos por forma de pago (USD 73%, Bs Transf 19%, Bs Efectivo 8%)
- Gráfico de barras: Pagos por día del mes (1-31 enero)
- Gráfico de líneas: Tendencia acumulada de ingresos

**HOJA 4: DESGLOSE POR BLOQUE**

```
| Bloque | Apartamentos | Pagos | Total USD | % del Total |
|--------|--------------|-------|-----------|-------------|
| 27     | 42           | 40    | $32.00    | 16%         |
| 28     | 41           | 39    | $34.00    | 17%         |
| 29     | 43           | 42    | $35.00    | 18%         |
| 30     | 40           | 38    | $31.00    | 16%         |
| 31     | 44           | 42    | $36.00    | 18%         |
| 32     | 40           | 38    | $30.00    | 15%         |
| TOTAL  | 250          | 239   | $198.00   | 100%        |
```

**HOJA 5: TASA BCV**

```
| Fecha      | Tasa BCV | # Pagos | Total Bs  |
|------------|----------|---------|-----------|
| 05/01/2025 | 36.35    | 35      | 2,541.00  |
| 06/01/2025 | 36.38    | 28      | 1,890.80  |
| 07/01/2025 | 36.40    | 32      | 2,045.60  |
...
| 31/01/2025 | 36.52    | 12      | 730.40    |
| PROMEDIO   | 36.40    | 245     | 7,208.40  |
```

#### **Paso 6: Verificar un pago específico**

Alberto quiere verificar el recibo EST-000125 (pago de Pedro Jiménez).

Regresa al sistema web → **[🔍 Consultar Pagos]**

Busca por **Recibo: EST-000125**

```
┌─────────────────────────────────────────────┐
│   DETALLE DE PAGO (SOLO LECTURA)           │
├─────────────────────────────────────────────┤
│                                            │
│ Recibo: EST-000125                         │
│ Estado: ✅ PAGADO                          │
│                                            │
│ INFORMACIÓN DEL CLIENTE                    │
│ Nombre: Pedro Jiménez                      │
│ Apartamento: Bloque 31, Apto 5-A          │
│ Controles: 2 (Posición 127-A, 127-B)      │
│                                            │
│ DETALLE DEL PAGO                           │
│ Fecha de pago: 18/01/2025 09:32 AM        │
│ Mensualidad: Enero 2025                   │
│ Monto USD: $2.00                           │
│ Forma de pago: USD Efectivo                │
│ Registrado por: Carmen Méndez (Operador)  │
│ Aprobado por: N/A (efectivo no requiere)  │
│                                            │
│ TASA DE CAMBIO                             │
│ Tasa BCV del día: 36.40                   │
│ Equivalente en Bs: 72.80                  │
│                                            │
│ [📄 Ver Recibo PDF]  [🔙 Volver]          │
│                                            │
│ ⚠️ Usted tiene permisos de solo lectura   │
│    No puede modificar ni eliminar pagos   │
│                                            │
└─────────────────────────────────────────────┘
```

Alberto verifica que los datos coinciden con su reporte. Todo correcto.

#### **Paso 7: Generar reporte de morosidad**

Alberto necesita también un reporte de clientes morosos para el informe completo.

**[⚠️ Reporte de Morosidad]**

```
┌─────────────────────────────────────────────┐
│   REPORTE DE MOROSIDAD - ENERO 2025        │
├─────────────────────────────────────────────┤
│                                            │
│ CLIENTES MOROSOS: 17                       │
│                                            │
│ Mora 1-2 meses: 0 clientes                │
│ Mora 3 meses (alerta): 12 clientes        │
│ Mora 4+ meses (bloqueados): 5 clientes    │
│                                            │
│ DEUDA TOTAL: $46.00 USD                    │
│                                            │
│ [📥 Exportar a Excel]  [🖨️ Imprimir PDF]  │
│                                            │
└─────────────────────────────────────────────┘
```

Alberto exporta también este reporte para adjuntarlo al informe mensual.

#### **Paso 8: Presentación a la junta (próximo viernes)**

Alberto prepara su presentación usando los reportes descargados.

Crea un PowerPoint con:
1. Resumen ejecutivo (ingresos totales, comparativa)
2. Gráficos de Excel embebidos
3. Tabla de morosidad
4. Recomendaciones (seguimiento a 5 clientes bloqueados)

Presenta a la junta el viernes y todos los datos están verificados y auditables.

### CASOS EDGE

#### **Caso 1: Alberto intenta modificar un pago**

- Alberto hace click en un pago pensando que puede editarlo
- Sistema muestra: "⛔ Acceso denegado - Su rol (Consultor) solo permite lectura. No puede modificar pagos"
- Botones de edición/eliminación NO están visibles para Alberto

#### **Caso 2: Reporte sin pagos en el período**

- Alberto genera reporte para "Septiembre 2024" (antes de que existiera el sistema)
- Sistema muestra: "ℹ️ No hay pagos registrados en el período seleccionado. El reporte estará vacío"
- Genera archivo Excel con solo headers y totales en $0

#### **Caso 3: Alberto intenta acceder a sección de administrador**

- Alberto escribe en URL: `/admin/usuarios`
- Sistema detecta permisos insuficientes
- Redirige a dashboard con mensaje: "⛔ No tiene permisos para acceder a esta sección"
- Log de seguridad registra el intento

#### **Caso 4: Error al generar gráficos**

- Sistema intenta generar gráficos estadísticos
- Librería de gráficos falla
- Sistema continúa generando reporte SIN gráficos
- Mensaje: "⚠️ El reporte se generó correctamente pero no se pudieron incluir gráficos. Los datos están completos"

#### **Caso 5: Alberto olvida su contraseña**

- Intenta recuperar contraseña
- Sistema muestra: "Por favor, contacte al administrador para restablecer su contraseña"
- El consultor NO puede cambiar su propia contraseña (solo admin puede)

### NOTAS TÉCNICAS

**Tablas involucradas:**
- `usuarios` (id=4, rol=consultor, Alberto Rivas)
- `pagos` (SELECT con filtros por fecha, todos los pagos del mes)
- `mensualidades` (JOIN para obtener detalles de meses pagados)
- `tasa_cambio_bcv` (para mostrar tasas diarias)
- `apartamentos` (para desglose por bloque)

**Permisos del rol Consultor:**
```php
// Middleware de autorización
$permissions = [
    'consultor' => [
        'view_pagos' => true,
        'view_reportes' => true,
        'view_estadisticas' => true,
        'view_morosidad' => true,
        'export_excel' => true,
        'export_pdf' => true,
        // Permisos DENEGADOS
        'edit_pagos' => false,
        'delete_pagos' => false,
        'approve_pagos' => false,
        'manage_usuarios' => false,
        'manage_configuracion' => false,
    ]
];
```

**Query principal del reporte:**
```sql
SELECT
    p.id,
    p.numero_recibo,
    p.fecha_pago,
    u.nombre_completo AS cliente,
    CONCAT(a.bloque, '-', a.numero_apartamento) AS apartamento,
    p.monto_usd,
    p.monto_bs,
    p.moneda_pago,
    t.tasa_usd_bs AS tasa_bcv,
    operador.nombre_completo AS operador
FROM pagos p
JOIN apartamento_usuario au ON au.id = p.apartamento_usuario_id
JOIN usuarios u ON u.id = au.usuario_id
JOIN apartamentos a ON a.id = au.apartamento_id
LEFT JOIN usuarios operador ON operador.id = p.registrado_por
LEFT JOIN tasa_cambio_bcv t ON t.id = p.tasa_cambio_id
WHERE MONTH(p.fecha_pago) = 1
  AND YEAR(p.fecha_pago) = 2025
  AND p.estado_comprobante IN ('aprobado', 'no_aplica')
ORDER BY p.fecha_pago ASC;
```

**Generación de Excel con PHPSpreadsheet:**
```php
// Crear archivo Excel con múltiples hojas
$spreadsheet = new Spreadsheet();

// Hoja 1: Resumen Ejecutivo
$sheet1 = $spreadsheet->getActiveSheet();
$sheet1->setTitle('Resumen Ejecutivo');
// ... agregar datos y formato

// Hoja 2: Detalle de Pagos
$sheet2 = $spreadsheet->createSheet();
$sheet2->setTitle('Detalle de Pagos');
// ... agregar datos

// Hoja 3: Gráficos
$sheet3 = $spreadsheet->createSheet();
$sheet3->setTitle('Gráficos');
// ... crear gráficos con Chart library

// Guardar archivo
$writer = new Xlsx($spreadsheet);
$filename = "Reporte_Ingresos_Enero_2025.xlsx";
$writer->save($filename);
```

**Logs registrados:**
```sql
- Login consultor: usuario_id=4, fecha_hora='2025-02-01 14:00:00'
- Generar reporte: tipo='ingresos', periodo='enero_2025', generado_por=4
- Descargar reporte: archivo='Reporte_Ingresos_Enero_2025.xlsx', usuario_id=4
- Consultar pago: recibo='EST-000125', consultado_por=4
- Exportar morosidad: tipo='excel', registros=17, generado_por=4
- Logout: usuario_id=4
```

**Diseño UX para Consultor (nivel básico):**
- Interfaz simplificada sin opciones avanzadas
- Botones claros: "Generar Reporte", "Exportar", "Imprimir"
- Sin acceso a formularios de edición (solo lectura)
- Reportes pre-configurados con opciones simples
- Gráficos generados automáticamente
- Exportación directa a Excel sin configuraciones complejas

---

## USER STORY #6: Cliente olvida su contraseña y la recupera

### PERSONAJE

**Nombre:** Laura Morales
**Rol:** Cliente/Residente
**Edad:** 29 años
**Ocupación:** Profesora de inglés
**Nivel técnico:** Intermedio - usa email regularmente, tiene smartphone
**Situación:** No ha ingresado al sistema en 2 meses, olvidó su contraseña

### CONTEXTO

Laura necesita revisar su estado de cuenta para verificar si su pago de diciembre fue registrado correctamente. Al intentar ingresar al sistema, se da cuenta de que no recuerda su contraseña. Tiene acceso a su email de registro y necesita recuperar el acceso de manera rápida y segura.

### HISTORIA

> **Como** cliente que olvidó su contraseña,
> **Quiero** recuperar el acceso a mi cuenta mediante mi correo electrónico,
> **Para** poder ingresar al sistema sin necesidad de contactar al administrador.

### CRITERIOS DE ACEPTACIÓN

1. ✅ El sistema debe permitir solicitar recuperación de contraseña desde la pantalla de login
2. ✅ Debe enviar un código de verificación de 6 dígitos al email registrado
3. ✅ El código debe expirar después de 15 minutos
4. ✅ El código debe ser de un solo uso (no reutilizable)
5. ✅ El sistema debe validar que el email exista en la base de datos
6. ✅ La nueva contraseña debe cumplir requisitos de seguridad (mínimo 8 caracteres, 1 mayúscula, 1 número)
7. ✅ Debe haber rate limiting: máximo 1 solicitud cada 60 segundos por IP
8. ✅ El sistema debe registrar en logs todos los intentos de recuperación
9. ✅ Debe enviar email de confirmación cuando la contraseña se cambie exitosamente
10. ✅ El sistema NO debe revelar si un email existe o no (anti-enumeración)

### FLUJO DETALLADO

#### **Paso 1: Laura intenta ingresar sin éxito**

- Laura abre navegador y va a `http://estacionamiento.local/login`
- Ingresa su email: `laura.morales@gmail.com`
- Intenta 3 contraseñas diferentes, todas incorrectas
- Sistema muestra: "❌ Email o contraseña incorrectos"
- Laura ve el enlace azul: **[¿Olvidaste tu contraseña?]**

#### **Paso 2: Laura inicia el proceso de recuperación**

- Laura hace clic en **[¿Olvidaste tu contraseña?]**
- Sistema redirige a: `/password/reset`
- Formulario muestra:
  ```
  🔒 Recuperar Contraseña

  Ingresa tu email de registro y te enviaremos un código de verificación.

  📧 Email: [___________________________]

  [Enviar Código]  [Volver al Login]
  ```

#### **Paso 3: Laura ingresa su email**

- Laura escribe: `laura.morales@gmail.com`
- Hace clic en **[Enviar Código]**
- Sistema valida:
  - ✅ Email tiene formato válido
  - ✅ Email existe en tabla `usuarios`
  - ✅ No hay otra solicitud activa de esta IP en los últimos 60 segundos
  - ✅ Usuario no está bloqueado

#### **Paso 4: Sistema genera y envía código**

- Sistema ejecuta:
  ```php
  // Generar código aleatorio de 6 dígitos
  $codigo = random_int(100000, 999999);

  // Calcular expiración (15 minutos)
  $fecha_expiracion = date('Y-m-d H:i:s', strtotime('+15 minutes'));

  // Guardar en base de datos
  INSERT INTO password_reset_tokens (
      usuario_id, email, codigo, fecha_expiracion, ip_address
  ) VALUES (?, ?, ?, ?, ?);
  ```
- Sistema envía email con PHPMailer:
  ```
  De: noreply@estacionamiento.local
  Para: laura.morales@gmail.com
  Asunto: Código de Recuperación de Contraseña

  Hola Laura,

  Recibimos una solicitud para restablecer tu contraseña.

  Tu código de verificación es: 758392

  Este código expira en 15 minutos.

  Si no solicitaste este cambio, ignora este mensaje.

  Saludos,
  Sistema de Estacionamiento
  ```
- Sistema muestra mensaje:
  ```
  ✅ Código enviado

  Hemos enviado un código de 6 dígitos a tu email.
  Revisa tu bandeja de entrada (y spam si no lo encuentras).

  El código expira en 15 minutos.
  ```

#### **Paso 5: Laura recibe el email y verifica el código**

- Laura abre su email y ve el código: **758392**
- Sistema muestra formulario automáticamente:
  ```
  🔐 Verificar Código

  Ingresa el código de 6 dígitos que enviamos a:
  la***@gmail.com (email parcialmente oculto)

  Código: [_ _ _ _ _ _]

  [Verificar]  [Reenviar Código]

  ⏱️ Expira en: 14 minutos 23 segundos
  ```

#### **Paso 6: Laura ingresa el código**

- Laura escribe: `758392`
- Hace clic en **[Verificar]**
- Sistema valida:
  - ✅ Código existe en `password_reset_tokens`
  - ✅ Código NO ha sido usado (`usado = false`)
  - ✅ Código NO ha expirado (`fecha_expiracion > NOW()`)
  - ✅ Código coincide con el email
- Sistema muestra: "✅ Código válido"

#### **Paso 7: Laura establece nueva contraseña**

- Sistema redirige a: `/password/reset/new`
- Formulario muestra:
  ```
  🔑 Establecer Nueva Contraseña

  Nueva Contraseña: [_______________] 👁️
  Repetir Contraseña: [_______________] 👁️

  Requisitos:
  ☐ Mínimo 8 caracteres
  ☐ Al menos 1 letra mayúscula
  ☐ Al menos 1 número
  ☐ No puede ser igual a la contraseña anterior

  [Cambiar Contraseña]
  ```
- Laura escribe: `LauraEst2025` en ambos campos
- Todos los requisitos se marcan: ✅
- Hace clic en **[Cambiar Contraseña]**

#### **Paso 8: Sistema procesa el cambio**

- Sistema ejecuta:
  ```php
  // Verificar que no sea la misma contraseña anterior
  $password_anterior = obtenerPasswordHash($usuario_id);
  if (password_verify($nueva_password, $password_anterior)) {
      throw new Exception("No puedes usar la misma contraseña");
  }

  // Encriptar nueva contraseña
  $nueva_hash = password_hash('LauraEst2025', PASSWORD_BCRYPT);

  // Actualizar en base de datos
  UPDATE usuarios
  SET password = ?, password_temporal = false
  WHERE id = ?;

  // Marcar token como usado
  UPDATE password_reset_tokens
  SET usado = true
  WHERE codigo = ?;

  // Registrar en logs
  INSERT INTO logs_actividad (
      usuario_id, accion, descripcion, ip_address
  ) VALUES (?, 'password_reset', 'Contraseña cambiada exitosamente', ?);
  ```
- Sistema muestra:
  ```
  ✅ Contraseña Actualizada

  Tu contraseña se cambió correctamente.
  Serás redirigido al login en 3 segundos...
  ```

#### **Paso 9: Sistema envía email de confirmación**

- Sistema envía segundo email:
  ```
  De: noreply@estacionamiento.local
  Para: laura.morales@gmail.com
  Asunto: Contraseña Actualizada

  Hola Laura,

  Tu contraseña fue cambiada exitosamente.

  Detalles de seguridad:
  - Fecha: 04 de noviembre, 2025 - 3:45 PM
  - IP: 192.168.1.105

  Si no realizaste este cambio, contacta inmediatamente
  al administrador.

  Saludos,
  Sistema de Estacionamiento
  ```

#### **Paso 10: Laura ingresa con nueva contraseña**

- Sistema redirige a `/login`
- Laura ingresa:
  - Email: `laura.morales@gmail.com`
  - Contraseña: `LauraEst2025`
- Sistema valida credenciales: ✅
- Redirige al dashboard de cliente
- Sistema muestra notificación: "✅ Bienvenida de nuevo, Laura"

### CASOS EDGE

#### **Caso 1: Código expirado**

- Laura solicita código a las 3:00 PM
- Se distrae y recién lo ingresa a las 3:20 PM (20 minutos después)
- Sistema detecta: `fecha_expiracion < NOW()`
- Muestra: "⏰ Este código ha expirado. Por favor, solicita uno nuevo"
- Botón: **[Solicitar Nuevo Código]**

#### **Caso 2: Código incorrecto (3 intentos)**

- Laura ingresa código erróneo: `123456` (intento 1)
- Sistema muestra: "❌ Código incorrecto. Te quedan 2 intentos"
- Laura ingresa: `654321` (intento 2)
- Sistema muestra: "❌ Código incorrecto. Te queda 1 intento"
- Laura ingresa: `999999` (intento 3)
- Sistema muestra: "🚫 Máximo de intentos alcanzado. Debes solicitar un nuevo código"
- Sistema marca token como `usado = true` (invalidado)
- Log registra: "Intentos fallidos de verificación de código"

#### **Caso 3: Email no existe en el sistema**

- Usuario escribe: `email_falso@gmail.com`
- Sistema NO revela que el email no existe (anti-enumeración)
- Muestra el mismo mensaje: "✅ Si el email existe en nuestro sistema, recibirás un código"
- NO envía ningún email
- Log registra: "Intento de recuperación con email no registrado: email_falso@gmail.com"

#### **Caso 4: Contraseñas no coinciden**

- Laura escribe en "Nueva Contraseña": `LauraEst2025`
- Laura escribe en "Repetir Contraseña": `LauraEst2024` (error tipográfico)
- Sistema muestra: "❌ Las contraseñas no coinciden"
- Campos se limpian
- Laura debe volver a escribir ambas contraseñas

#### **Caso 5: Contraseña débil**

- Laura intenta usar: `12345678`
- Sistema valida requisitos:
  - ✅ Mínimo 8 caracteres
  - ❌ Al menos 1 letra mayúscula
  - ✅ Al menos 1 número
- Sistema muestra: "❌ La contraseña no cumple los requisitos de seguridad"
- No permite continuar hasta cumplir todos los requisitos

#### **Caso 6: Email cae en carpeta de SPAM**

- Sistema envía email correctamente
- Laura no ve el email en su bandeja de entrada
- Espera 5 minutos
- Sistema muestra botón: **[¿No recibiste el código? Reenviar]**
- Laura hace clic en reenviar
- Sistema verifica que pasaron al menos 60 segundos
- Genera NUEVO código (invalida el anterior)
- Envía nuevo email
- Muestra: "📧 Código reenviado. Revisa también tu carpeta de SPAM"

#### **Caso 7: Usuario tiene cuenta bloqueada**

- Laura intenta recuperar contraseña
- Sistema detecta: `usuarios.bloqueado_hasta IS NOT NULL AND bloqueado_hasta > NOW()`
- Sistema muestra: "🔒 Tu cuenta está temporalmente bloqueada. Contacta al administrador"
- NO envía código de recuperación
- Log registra: "Intento de recuperación con cuenta bloqueada: usuario_id=X"

#### **Caso 8: Múltiples solicitudes en corto tiempo (Rate Limiting)**

- Laura solicita código a las 3:00:00 PM
- Laura solicita otro código a las 3:00:30 PM (30 segundos después)
- Sistema detecta: última solicitud desde IP 192.168.1.105 fue hace menos de 60 segundos
- Sistema muestra: "⏳ Por favor, espera 30 segundos antes de solicitar otro código"
- Contador regresivo: "Podrás solicitar un nuevo código en: 00:29"

#### **Caso 9: Actividad sospechosa (múltiples IPs)**

- Sistema detecta 5 solicitudes de recuperación para `laura.morales@gmail.com` desde 5 IPs diferentes en 10 minutos
- Sistema marca como actividad sospechosa
- Envía email de alerta a Laura:
  ```
  ⚠️ Actividad Sospechosa Detectada

  Detectamos múltiples intentos de recuperación de contraseña
  desde diferentes ubicaciones.

  Si no fuiste tú, tu cuenta puede estar en riesgo.
  Contacta al administrador inmediatamente.
  ```
- Sistema bloquea temporalmente las solicitudes de recuperación para ese email (1 hora)

#### **Caso 10: Laura intenta usar la misma contraseña anterior**

- Laura ingresa como nueva contraseña: `LauraAnt2024` (su contraseña anterior)
- Sistema ejecuta:
  ```php
  if (password_verify($nueva_password, $password_anterior_hash)) {
      throw new Exception("No puedes reutilizar tu contraseña anterior");
  }
  ```
- Sistema muestra: "❌ No puedes usar la misma contraseña. Elige una diferente"
- Laura debe ingresar una contraseña completamente nueva

### NOTAS TÉCNICAS

**Nueva tabla requerida:**

```sql
CREATE TABLE password_reset_tokens (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT NOT NULL,
    email VARCHAR(255) NOT NULL,
    codigo VARCHAR(6) NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_expiracion TIMESTAMP NOT NULL,
    usado BOOLEAN DEFAULT FALSE,
    intentos_validacion INT DEFAULT 0,
    ip_address VARCHAR(45),
    user_agent TEXT,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_codigo (codigo),
    INDEX idx_email (email),
    INDEX idx_fecha_expiracion (fecha_expiracion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

**Tablas involucradas:**
- `usuarios` (para verificar email, actualizar password)
- `password_reset_tokens` (nueva tabla para códigos de recuperación)
- `logs_actividad` (registrar intentos de recuperación)

**Función PHP para generar código:**

```php
function generarCodigoRecuperacion() {
    // Generar código aleatorio de 6 dígitos
    return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
}
```

**Función PHP para validar código:**

```php
function validarCodigoRecuperacion($email, $codigo) {
    $query = "SELECT * FROM password_reset_tokens
              WHERE email = ?
              AND codigo = ?
              AND usado = false
              AND fecha_expiracion > NOW()
              ORDER BY fecha_creacion DESC
              LIMIT 1";

    $stmt = $pdo->prepare($query);
    $stmt->execute([$email, $codigo]);
    $token = $stmt->fetch();

    if (!$token) {
        // Incrementar intentos fallidos
        incrementarIntentosValidacion($email, $codigo);
        return false;
    }

    return $token;
}
```

**Template de email con PHPMailer:**

```php
$mail = new PHPMailer(true);
$mail->isSMTP();
$mail->Host = 'smtp.gmail.com';
$mail->SMTPAuth = true;
$mail->Username = getenv('SMTP_USERNAME');
$mail->Password = getenv('SMTP_PASSWORD');
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port = 587;

$mail->setFrom('noreply@estacionamiento.local', 'Sistema de Estacionamiento');
$mail->addAddress($email, $nombre_usuario);

$mail->isHTML(true);
$mail->Subject = 'Código de Recuperación de Contraseña';
$mail->Body = "
    <h2>Recuperación de Contraseña</h2>
    <p>Hola {$nombre_usuario},</p>
    <p>Tu código de verificación es:</p>
    <h1 style='color: #007bff; font-size: 36px;'>{$codigo}</h1>
    <p>Este código expira en <strong>15 minutos</strong>.</p>
    <p>Si no solicitaste este cambio, ignora este mensaje.</p>
";

$mail->send();
```

**Rate Limiting con Redis (opcional) o sesión:**

```php
function verificarRateLimiting($ip) {
    // Verificar última solicitud desde esta IP
    $query = "SELECT MAX(fecha_creacion) as ultima_solicitud
              FROM password_reset_tokens
              WHERE ip_address = ?";

    $stmt = $pdo->prepare($query);
    $stmt->execute([$ip]);
    $result = $stmt->fetch();

    if ($result && $result['ultima_solicitud']) {
        $tiempo_transcurrido = time() - strtotime($result['ultima_solicitud']);

        if ($tiempo_transcurrido < 60) {
            $segundos_restantes = 60 - $tiempo_transcurrido;
            throw new Exception("Debes esperar {$segundos_restantes} segundos");
        }
    }

    return true;
}
```

**Logs de seguridad:**

```sql
-- Solicitud de recuperación
INSERT INTO logs_actividad (usuario_id, accion, descripcion, ip_address)
VALUES (5, 'password_reset_request', 'Código enviado a la***@gmail.com', '192.168.1.105');

-- Código validado exitosamente
INSERT INTO logs_actividad (usuario_id, accion, descripcion, ip_address)
VALUES (5, 'password_reset_verify', 'Código verificado correctamente', '192.168.1.105');

-- Contraseña actualizada
INSERT INTO logs_actividad (usuario_id, accion, descripcion, ip_address)
VALUES (5, 'password_reset_complete', 'Contraseña cambiada exitosamente', '192.168.1.105');

-- Intentos fallidos
INSERT INTO logs_actividad (usuario_id, accion, descripcion, ip_address)
VALUES (NULL, 'password_reset_failed', 'Email no registrado: email_falso@gmail.com', '192.168.1.105');
```

**Validación de requisitos de contraseña (JavaScript):**

```javascript
function validarPassword(password) {
    const requisitos = {
        longitud: password.length >= 8,
        mayuscula: /[A-Z]/.test(password),
        numero: /\d/.test(password)
    };

    // Actualizar UI con checkmarks
    document.querySelector('#req-longitud').className =
        requisitos.longitud ? 'check' : 'uncheck';
    document.querySelector('#req-mayuscula').className =
        requisitos.mayuscula ? 'check' : 'uncheck';
    document.querySelector('#req-numero').className =
        requisitos.numero ? 'check' : 'uncheck';

    // Habilitar botón solo si todos los requisitos se cumplen
    const todosValidos = Object.values(requisitos).every(r => r === true);
    document.querySelector('#btn-cambiar').disabled = !todosValidos;

    return todosValidos;
}
```

**Expiración automática de tokens (CRON Job):**

```php
// Script: cron/limpiar_tokens_expirados.php
// Ejecutar diariamente a las 2:00 AM

DELETE FROM password_reset_tokens
WHERE fecha_expiracion < NOW()
OR (usado = true AND fecha_creacion < DATE_SUB(NOW(), INTERVAL 7 DAY));

echo "Tokens expirados eliminados: " . $stmt->rowCount();
```

**Variables de entorno (.env):**

```env
# Configuración SMTP para envío de emails
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USERNAME=noreply@estacionamiento.local
SMTP_PASSWORD=tu_password_smtp
SMTP_ENCRYPTION=tls
SMTP_FROM_EMAIL=noreply@estacionamiento.local
SMTP_FROM_NAME="Sistema de Estacionamiento"

# Configuración de seguridad
PASSWORD_RESET_CODE_EXPIRATION=15 # minutos
PASSWORD_RESET_RATE_LIMIT=60 # segundos entre solicitudes
PASSWORD_RESET_MAX_ATTEMPTS=3 # intentos de validación
```

---

## [ESPACIO PARA PRÓXIMAS USER STORIES]

### Ideas para próximas historias:

1. **USER STORY #7: Cliente solicita suspensión temporal de control**
   - Personaje: Juan Martínez (Cliente)
   - Flujo: Viaja por 3 meses, solicita suspensión, operador aprueba

2. **USER STORY #8: Administrador importa usuarios desde Excel**
   - Personaje: Ing. Miguel Sánchez (Administrador)
   - Flujo: Carga archivo Excel, sistema valida, importa 150 usuarios con sus controles

---

## Notas Generales

- Cada User Story debe seguir el formato: Personaje + Contexto + Historia + Criterios + Flujo + Casos Edge + Notas Técnicas
- Los nombres de personajes son ficticios pero representan casos de uso reales
- Los flujos deben ser lo suficientemente detallados para que un desarrollador pueda implementarlos
- Los casos edge ayudan a identificar validaciones y manejo de errores necesarios
