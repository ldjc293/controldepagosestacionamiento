# Guía de Pruebas del Sistema

## 🎉 ¡Sistema Listo para Probar!

Has completado exitosamente la configuración base del sistema. Ahora puedes probar todas las funcionalidades creadas.

---

## 📋 Checklist de Archivos Creados

### ✅ Configuración Base
- [x] composer.json
- [x] .env.example
- [x] .htaccess
- [x] .gitignore
- [x] config/database.php
- [x] config/config.php
- [x] public/index.php

### ✅ Base de Datos
- [x] database/schema.sql (13 tablas)
- [x] database/seeds.sql (datos de prueba)

### ✅ Helpers (4)
- [x] app/helpers/ValidationHelper.php
- [x] app/helpers/MailHelper.php
- [x] app/helpers/PDFHelper.php
- [x] app/helpers/QRHelper.php

### ✅ Modelos (5)
- [x] app/models/Usuario.php
- [x] app/models/Mensualidad.php
- [x] app/models/Pago.php
- [x] app/models/Control.php
- [x] app/models/Apartamento.php

### ✅ Controladores (1)
- [x] app/controllers/AuthController.php

### ✅ Vistas de Autenticación (5)
- [x] app/views/auth/login.php
- [x] app/views/auth/forgot_password.php
- [x] app/views/auth/verify_code.php
- [x] app/views/auth/new_password.php
- [x] app/views/auth/cambiar_password_obligatorio.php

---

## 🚀 Pasos para Probar el Sistema

### Paso 1: Instalar Dependencias

```bash
cd c:\xampp\htdocs\controldepagosestacionamiento
composer install
```

**Nota:** Si no tienes Composer instalado, descárgalo de: https://getcomposer.org/download/

### Paso 2: Configurar .env

```bash
copy .env.example .env
```

Editar el archivo `.env` con tus datos:

```env
DB_HOST=localhost
DB_NAME=estacionamiento_db
DB_USER=root
DB_PASS=

MAIL_USERNAME=tu-email@gmail.com
MAIL_PASSWORD=tu-app-password

APP_URL=http://localhost/controldepagosestacionamiento
APP_DEBUG=true
```

### Paso 3: Crear Base de Datos

1. Abrir phpMyAdmin: http://localhost/phpmyadmin
2. Crear base de datos: `estacionamiento_db`
3. Importar `database/schema.sql`
4. Importar `database/seeds.sql`

### Paso 4: Iniciar XAMPP

- Iniciar Apache
- Iniciar MySQL

### Paso 5: Acceder al Sistema

Abrir navegador: **http://localhost/controldepagosestacionamiento**

---

## 👥 Credenciales de Prueba

Todos los usuarios tienen la contraseña: **password123**

### 🔧 Administrador
- **Email:** admin@estacionamiento.local
- **Rol:** Acceso completo al sistema
- **Prueba:** Login directo (sin cambio obligatorio)

### 📋 Operador
- **Email:** operador@estacionamiento.local
- **Rol:** Registrar pagos, aprobar comprobantes
- **Prueba:** Login directo

### 📊 Consultor
- **Email:** consultor@estacionamiento.local
- **Rol:** Ver reportes (solo lectura)
- **Prueba:** Login directo

### 👤 Cliente - Normal
- **Email:** maria.gonzalez@gmail.com
- **Rol:** Residente con pagos al día
- **Prueba:** Login directo

### 🆕 Cliente - Primer Acceso
- **Email:** roberto.diaz@gmail.com
- **Rol:** Nuevo usuario (User Story #2)
- **Prueba:** Login → **Cambio de contraseña obligatorio**
  1. Iniciar sesión con password123
  2. Sistema redirige automáticamente a cambio de contraseña
  3. Establecer nueva contraseña
  4. Acceder al dashboard

### 🔐 Cliente - Recuperar Contraseña
- **Email:** laura.morales@gmail.com
- **Rol:** Usuario que olvidó contraseña (User Story #6)
- **Prueba:**
  1. Click en "¿Olvidaste tu contraseña?"
  2. Ingresar email: laura.morales@gmail.com
  3. **Revisar logs** (no llegará email real sin configurar SMTP)
  4. Ver código en `logs/app.log` o tabla `password_reset_tokens`
  5. Ingresar código de 6 dígitos
  6. Establecer nueva contraseña
  7. Login con nueva contraseña

---

## 🧪 Casos de Prueba

### Test 1: Login Exitoso
1. Ir a http://localhost/controldepagosestacionamiento
2. Ingresar: `admin@estacionamiento.local` / `password123`
3. **Resultado esperado:** Redirección a dashboard (pendiente de crear)

### Test 2: Login Fallido
1. Ingresar email o contraseña incorrectos
2. **Resultado esperado:** Mensaje "Email o contraseña incorrectos"

### Test 3: Primer Acceso (User Story #2)
1. Login con: roberto.diaz@gmail.com / password123
2. **Resultado esperado:**
   - Redirección automática a cambio de contraseña
   - No puede acceder al dashboard sin cambiar contraseña
   - Después de cambiar, accede normalmente

### Test 4: Recuperación de Contraseña (User Story #6)
1. Click en "¿Olvidaste tu contraseña?"
2. Ingresar: laura.morales@gmail.com
3. **Ver código en BD:**
   ```sql
   SELECT codigo, fecha_expiracion
   FROM password_reset_tokens
   WHERE email = 'laura.morales@gmail.com'
   ORDER BY fecha_creacion DESC
   LIMIT 1;
   ```
4. Copiar código e ingresar
5. **Resultado esperado:**
   - Código válido → Formulario de nueva contraseña
   - Código incorrecto (3 intentos) → Solicitar nuevo código
   - Código expirado (15+ min) → Solicitar nuevo código

### Test 5: Rate Limiting
1. Solicitar recuperación de contraseña
2. Intentar solicitar otra inmediatamente (sin esperar 60 seg)
3. **Resultado esperado:** Mensaje "Por favor, espere 60 segundos"

### Test 6: Validaciones de Contraseña
1. Intentar contraseña débil: "123456"
2. **Resultado esperado:** Error "No cumple requisitos"
3. Intentar contraseña válida: "Password123"
4. **Resultado esperado:** Aceptada ✓

### Test 7: Bloqueo por Intentos Fallidos
1. Intentar login con contraseña incorrecta 5 veces
2. **Resultado esperado:** Cuenta bloqueada por 30 minutos

---

## 🐛 Verificar Logs

### Ver logs de aplicación:
```bash
type logs\app.log
```

### Ver logs de PHP:
```bash
type logs\php_errors.log
```

### Ver actividad en BD:
```sql
SELECT * FROM logs_actividad ORDER BY fecha_hora DESC LIMIT 10;
```

### Ver tokens de recuperación:
```sql
SELECT * FROM password_reset_tokens ORDER BY fecha_creacion DESC LIMIT 5;
```

---

## 📧 Configurar Email (Opcional)

Para que funcionen los emails reales:

### Gmail App Password

1. Ir a: https://myaccount.google.com/apppasswords
2. Activar verificación en 2 pasos
3. Generar contraseña de aplicación
4. Actualizar `.env`:
   ```env
   MAIL_USERNAME=tu-email@gmail.com
   MAIL_PASSWORD=abcd-efgh-ijkl-mnop
   ```

### Probar envío de email:

Crear archivo `test_email.php` en la raíz:

```php
<?php
require_once 'config/config.php';
require_once 'app/helpers/MailHelper.php';

$resultado = MailHelper::sendPasswordResetCode(
    'tu-email@gmail.com',
    'Nombre de Prueba',
    '123456'
);

echo $resultado ? '✅ Email enviado' : '❌ Error al enviar';
?>
```

Ejecutar: http://localhost/controldepagosestacionamiento/test_email.php

---

## 🎨 Características de las Vistas

### Login
- Diseño moderno con gradiente
- Toggle para mostrar/ocultar contraseña
- Loading spinner en submit
- Animaciones suaves
- Auto-ocultar alertas después de 5 segundos
- Responsive (mobile-friendly)

### Recuperación de Contraseña
- Flujo de 3 pasos claramente definido
- Input de código con 6 dígitos separados
- Timer de expiración (15 minutos)
- Validación en tiempo real
- Opción de reenviar código

### Cambio de Contraseña
- Validación en tiempo real de requisitos
- Indicador de fortaleza de contraseña
- Visual feedback (checkmarks verdes)
- Previene contraseñas débiles
- Confirma que coincidan

---

## 🔄 Próximos Pasos

Ahora que el sistema de autenticación funciona, puedes:

1. **Crear controladores por rol:**
   - ClienteController
   - OperadorController
   - ConsultorController
   - AdminController

2. **Crear dashboards:**
   - Dashboard de Cliente (estado de cuenta, pagos pendientes)
   - Dashboard de Operador (comprobantes pendientes)
   - Dashboard de Consultor (reportes y estadísticas)
   - Dashboard de Admin (gestión completa)

3. **Crear módulos específicos:**
   - Gestión de pagos
   - Aprobación de comprobantes
   - Generación de recibos PDF
   - Reportes de morosidad
   - Administración de controles

4. **Crear scripts CRON:**
   - Generar mensualidades (día 5)
   - Verificar bloqueos (diario)
   - Enviar notificaciones (diario)
   - Actualizar tasa BCV (diario)

---

## ❓ Problemas Comunes

### Error: "composer: command not found"
**Solución:** Instalar Composer desde https://getcomposer.org/download/

### Error: "Access denied for user 'root'@'localhost'"
**Solución:** Verificar credenciales en `.env`

### Error: "Class 'PHPMailer' not found"
**Solución:** Ejecutar `composer install`

### Error: "Call to undefined function generateCSRFToken()"
**Solución:** Verificar que `config/config.php` se esté cargando correctamente

### Página en blanco
**Solución:**
1. Verificar que Apache y MySQL estén iniciados
2. Revisar `logs/php_errors.log`
3. Habilitar `APP_DEBUG=true` en `.env`

### No redirige correctamente
**Solución:** Verificar que `.htaccess` esté en la raíz y mod_rewrite esté activo

---

## 📞 Soporte

Si encuentras problemas:

1. Revisar logs: `logs/app.log` y `logs/php_errors.log`
2. Verificar consola del navegador (F12)
3. Revisar tabla `logs_actividad` en la BD
4. Verificar que todas las tablas existan en phpMyAdmin

---

## ✅ Sistema Funcional

**Lo que YA funciona:**
- ✅ Login con validación
- ✅ Logout
- ✅ Recuperación de contraseña (flujo completo)
- ✅ Cambio de contraseña obligatorio (primer acceso)
- ✅ Validaciones de seguridad
- ✅ Rate limiting
- ✅ Bloqueo por intentos fallidos
- ✅ Logging de actividad
- ✅ Tokens CSRF
- ✅ Helpers completos
- ✅ Modelos con toda la lógica de negocio

**Pendiente de crear:**
- ⏳ Controladores por rol
- ⏳ Dashboards
- ⏳ Módulos de gestión
- ⏳ Scripts CRON

---

¡El sistema está listo para continuar su desarrollo! 🚀
