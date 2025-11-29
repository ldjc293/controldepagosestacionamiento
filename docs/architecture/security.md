# Seguridad

## Medidas Implementadas

### Autenticación y Autorización
- **Hashing de Contraseñas**: Se utiliza `password_hash()` con algoritmo BCRYPT.
- **Control de Acceso Basado en Roles (RBAC)**: 4 niveles de acceso (Cliente, Operador, Consultor, Administrador).
- **Gestión de Sesiones**:
  - Tokens CSRF en todos los formularios para prevenir ataques Cross-Site Request Forgery.
  - Regeneración de ID de sesión al iniciar sesión para prevenir fijación de sesión.
  - Expiración de sesión tras 30 minutos de inactividad.
- **Protección contra Fuerza Bruta**:
  - Bloqueo de cuenta tras 5 intentos fallidos de inicio de sesión.
  - Bloqueo temporal de 30 minutos.
  - Rate limiting en recuperación de contraseña (60 segundos entre intentos).

### Protección de Datos
- **Inyección SQL**: Uso estricto de PDO con Prepared Statements para todas las consultas a la base de datos.
- **XSS (Cross-Site Scripting)**: Sanitización de todas las salidas de datos utilizando `htmlspecialchars()`.
- **Validación de Archivos**:
  - Verificación de tipos MIME permitidos (JPG, PNG, PDF).
  - Validación de extensiones de archivo.
  - Sanitización de nombres de archivos subidos.
  - Generación de nombres únicos con hash para evitar colisiones y ejecución de scripts maliciosos.

### Auditoría
- **Logs de Actividad**: Registro detallado de acciones críticas en la tabla `logs_actividad`.
  - Usuario responsable.
  - Acción realizada.
  - Módulo afectado.
  - Datos anteriores y nuevos (en formato JSON).
  - Dirección IP y User Agent.
- **Logs del Sistema**: Archivos de log en `logs/app.log` y `logs/errors.log` para depuración y monitoreo.

### Configuración Segura
- **Variables de Entorno**: Credenciales sensibles (BD, SMTP) almacenadas en archivo `.env` fuera del alcance público.
- **Permisos de Archivos**: Directorios de carga (`uploads/`) y logs configurados con permisos restringidos.