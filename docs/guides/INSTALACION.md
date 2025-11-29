# Guía de Instalación - Sistema de Estacionamiento

## Requisitos del Sistema

- **XAMPP** (PHP 7.4+ y MySQL 5.7+)
- **Composer** (gestor de dependencias PHP)
- **Navegador web moderno** (Chrome, Firefox, Edge, Safari)
- **Git** (opcional, para control de versiones)

---

## Paso 1: Verificar instalación de Composer

Abrir terminal (CMD o PowerShell) y ejecutar:

```bash
composer --version
```

Si no está instalado, descargar desde: https://getcomposer.org/download/

---

## Paso 2: Instalar dependencias PHP

En la carpeta del proyecto, ejecutar:

```bash
cd c:\xampp\htdocs\controldepagosestacionamiento
composer install
```

Esto instalará:
- ✅ PHPMailer (envío de emails)
- ✅ DomPDF (generación de recibos PDF)
- ✅ PHPSpreadsheet (exportación e importación de Excel)
- ✅ chillerlan/php-qrcode (generación de códigos QR)
- ✅ vlucas/phpdotenv (manejo de variables de entorno)

---

## Paso 3: Configurar archivo .env

1. Copiar el archivo de ejemplo:

```bash
copy .env.example .env
```

2. Editar el archivo `.env` con tus datos:

```env
# Base de datos
DB_HOST=localhost
DB_NAME=estacionamiento_db
DB_USER=root
DB_PASS=

# Email (Gmail)
MAIL_USERNAME=tu-email@gmail.com
MAIL_PASSWORD=tu-app-password-aqui

# Aplicación
APP_URL=http://localhost/controldepagosestacionamiento
APP_DEBUG=true
```

**IMPORTANTE para Gmail:**
1. Ir a: https://myaccount.google.com/apppasswords
2. Activar verificación en 2 pasos
3. Generar contraseña de aplicación
4. Usar esa contraseña en `MAIL_PASSWORD`

---

## Paso 4: Crear la base de datos

1. Abrir phpMyAdmin: http://localhost/phpmyadmin

2. Crear base de datos:
   - Clic en "Nuevo" (New)
   - Nombre: `estacionamiento_db`
   - Cotejamiento: `utf8mb4_unicode_ci`
   - Clic en "Crear"

3. Importar schema:
   - Seleccionar la base de datos `estacionamiento_db`
   - Clic en "Importar"
   - Seleccionar archivo: `database/schema.sql`
   - Clic en "Continuar"

4. Importar datos iniciales:
   - Clic en "Importar"
   - Seleccionar archivo: `database/seeds.sql`
   - Clic en "Continuar"

---

## Paso 5: Verificar permisos de carpetas

En Windows, abrir CMD como **Administrador** y ejecutar:

```bash
cd c:\xampp\htdocs\controldepagosestacionamiento

icacls "public\uploads" /grant Everyone:F /T
icacls "logs" /grant Everyone:F /T
```

Esto otorga permisos de escritura a las carpetas de uploads y logs.

---

## Paso 6: Iniciar servicios de XAMPP

1. Abrir **XAMPP Control Panel**
2. Iniciar **Apache**
3. Iniciar **MySQL**

---

## Paso 7: Acceder al sistema

Abrir navegador y acceder a:

```
http://localhost/controldepagosestacionamiento
```

---

## Credenciales de Acceso Iniciales

El archivo `seeds.sql` crea usuarios de prueba con contraseña: **password123**

### Administrador
- **Email:** admin@estacionamiento.local
- **Contraseña:** password123
- **Acceso completo** al sistema

### Operador
- **Email:** operador@estacionamiento.local
- **Contraseña:** password123
- Registrar pagos, aprobar comprobantes

### Consultor
- **Email:** consultor@estacionamiento.local
- **Contraseña:** password123
- Ver reportes (solo lectura)

### Clientes (varios disponibles)
- **Email:** maria.gonzalez@gmail.com
- **Email:** roberto.diaz@gmail.com (primer acceso)
- **Email:** laura.morales@gmail.com
- **Contraseña:** password123 (para todos)

**⚠️ IMPORTANTE:** Cambiar todas las contraseñas después de la instalación.

---

## Paso 8: Configurar tareas programadas (Opcional)

Para Windows, usar el **Programador de tareas**:

### Generar mensualidades (Día 5 de cada mes a las 00:05)

```bash
php C:\xampp\htdocs\controldepagosestacionamiento\cron\generar_mensualidades.php
```

### Verificar bloqueos (Diariamente a las 05:00)

```bash
php C:\xampp\htdocs\controldepagosestacionamiento\cron\verificar_bloqueos.php
```

### Enviar notificaciones (Diariamente a las 08:00)

```bash
php C:\xampp\htdocs\controldepagosestacionamiento\cron\enviar_notificaciones.php
```

### Actualizar tasa BCV (Diariamente a las 12:00)

```bash
php C:\xampp\htdocs\controldepagosestacionamiento\cron\actualizar_tasa_bcv.php
```

---

## Verificación de instalación

### 1. Verificar conexión a BD

Crear archivo `test_db.php` en la raíz:

```php
<?php
require_once 'config/database.php';

try {
    $pdo = Database::getInstance();
    echo "✅ Conexión a base de datos exitosa\n";

    $usuarios = Database::fetchAll("SELECT COUNT(*) as total FROM usuarios");
    echo "✅ Total de usuarios: " . $usuarios[0]['total'] . "\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
```

Ejecutar en navegador: `http://localhost/controldepagosestacionamiento/test_db.php`

### 2. Verificar estructura de archivos

```
✅ composer.json
✅ .env (copiado de .env.example)
✅ .htaccess
✅ public/index.php
✅ config/database.php
✅ config/config.php
✅ database/schema.sql
✅ database/seeds.sql
```

### 3. Verificar carpetas

```
✅ app/controllers/
✅ app/models/
✅ app/views/
✅ public/uploads/comprobantes/
✅ public/uploads/recibos/
✅ logs/
```

---

## Problemas Comunes

### Error: "composer: command not found"
- Instalar Composer desde: https://getcomposer.org/download/
- Reiniciar terminal después de instalar

### Error: "Access denied for user 'root'@'localhost'"
- Verificar credenciales en `.env`
- Por defecto, XAMPP usa usuario `root` sin contraseña

### Error: "Call to undefined function mb_string()"
- Habilitar extensión `php_mbstring` en `php.ini`
- Reiniciar Apache

### Error: "Maximum execution time exceeded"
- Aumentar `max_execution_time` en `php.ini` a 300

### Error: "Permission denied" al subir archivos
- Verificar permisos de carpetas `uploads` y `logs`
- Ejecutar comando `icacls` como administrador

### Página en blanco al acceder
- Verificar que Apache y MySQL estén iniciados
- Revisar logs en `logs/app.log` y `logs/php_errors.log`
- Verificar que `.htaccess` esté habilitado (mod_rewrite activo)

---

## Siguiente Paso

Una vez instalado el sistema, proceder con:

1. **Importar datos reales** desde el archivo Excel de estacionamiento
2. **Cambiar contraseñas** de todos los usuarios de prueba
3. **Configurar email** con credenciales de Gmail reales
4. **Personalizar** tarifas y configuración del negocio
5. **Crear usuarios** reales para operadores y clientes

Ver [README.md](README.md) para más información sobre el uso del sistema.

---

## Soporte

Para reportar problemas o solicitar ayuda:
- Revisar logs en `logs/app.log`
- Revisar errores PHP en `logs/php_errors.log`
- Verificar consola del navegador (F12) para errores JavaScript

---

**✅ ¡Instalación completada!** El sistema está listo para ser usado.
