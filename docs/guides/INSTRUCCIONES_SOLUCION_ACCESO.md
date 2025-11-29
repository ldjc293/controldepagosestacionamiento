# Instrucciones para Solucionar Problemas de Acceso

## Problema
No puedes ingresar al sistema con el usuario y contraseña registrados.

## Causas Posibles
1. Los usuarios no existen en la base de datos
2. Las contraseñas no coinciden con los hashes almacenados
3. Las cuentas están bloqueadas por intentos fallidos
4. Las cuentas están inactivas

## Solución Rápida

### Paso 1: Crear tablas faltantes
Si recibiste un error sobre la tabla `configuracion_cron`, primero ejecuta este script:
```
http://localhost/controldepagosestacionamiento/crear_tablas_faltantes.php
```

Este script creará las tablas y vistas que faltan en la base de datos sin afectar los datos existentes:
- Tabla `configuracion_cron`
- Tabla `login_intentos`
- Tabla `password_reset_tokens`
- Vista `vista_morosidad`
- Vista `vista_controles_vacios`

### Paso 2: Agregar columna meses_bloqueo
Si recibiste un error como "Column not found: 1054 Unknown column 'meses_bloqueo' in 'field list'" al intentar guardar la configuración, ejecuta este script:
```
http://localhost/controldepagosestacionamiento/agregar_columna_meses_bloqueo.php
```

Este script agregará la columna `meses_bloqueo` a la tabla `configuracion_tarifas` que es necesaria para el funcionamiento del sistema de configuración.

### Paso 3: Verificar el estado actual de los usuarios
Accede a la siguiente URL en tu navegador:
```
http://localhost/controldepagosestacionamiento/verificar_usuarios.php
```

Este script te mostrará:
- Cuántos usuarios existen en la base de datos
- El estado de cada usuario (activo, inactivo, bloqueado)
- Si hay intentos fallidos de login
- Si las cuentas requieren primer acceso

### Paso 4: Restablecer las credenciales
Si los usuarios existen pero no puedes acceder, ejecuta el script de restablecimiento:
```
http://localhost/controldepagosestacionamiento/solucionar_acceso.php
```

Este script:
- Creará los usuarios si no existen
- Actualizará las contraseñas a valores conocidos
- Reseteará intentos fallidos
- Desbloqueará las cuentas

## Credenciales Predeterminadas

Después de ejecutar el script de solución, podrás usar estas credenciales:

| Rol | Email | Contraseña |
|-----|-------|------------|
| Administrador | admin@estacionamiento.local | admin123 |
| Operador | operador@estacionamiento.local | operador123 |
| Consultor | consultor@estacionamiento.local | consultor123 |
| Cliente | maria.gonzalez@gmail.com | cliente123 |

## Pasos Adicionales si el Problema Persiste

### 1. Verificar la conexión a la base de datos
Asegúrate de que el archivo `.env` tenga las credenciales correctas:
```
DB_HOST=localhost
DB_PORT=3306
DB_NAME=estacionamiento_db
DB_USER=root
DB_PASS=
```

### 2. Verificar que la base de datos exista
Ejecuta el script de inicialización si es necesario:
```
http://localhost/controldepagosestacionamiento/database/init.php
```

### 3. Cargar los datos iniciales
Si la base de datos está vacía, ejecuta:
```
http://localhost/controldepagosestacionamiento/database/seeds.sql
```

## Notas Importantes

1. **Contraseñas en texto plano**: Las contraseñas se almacenan como hashes BCRYPT en la base de datos. Los scripts creados generan los hashes automáticamente.

2. **Seguridad**: Después de solucionar el problema, se recomienda:
   - Cambiar las contraseñas predeterminadas
   - Eliminar los scripts de solución (solucionar_acceso.php y verificar_usuarios.php)
   - Verificar que todos los usuarios tengan contraseñas seguras

3. **Intentos Fallidos**: El sistema tiene un límite de 5 intentos fallidos antes de bloquear la cuenta por 15 minutos. Los scripts resetean estos contadores.

## Archivos Creados

1. **verificar_usuarios.php**: Muestra el estado actual de los usuarios en la base de datos
2. **solucionar_acceso.php**: Restablece las credenciales de los usuarios a valores conocidos
3. **crear_tablas_faltantes.php**: Crea las tablas y vistas que faltan en la base de datos
4. **agregar_columna_meses_bloqueo.php**: Agrega la columna meses_bloqueo a la tabla configuracion_tarifas

## Flujo Recomendado

1. Ejecuta `crear_tablas_faltantes.php` si recibiste errores de tablas faltantes
2. Ejecuta `agregar_columna_meses_bloqueo.php` si recibiste el error de la columna meses_bloqueo
3. Ejecuta `verificar_usuarios.php` para diagnosticar el problema
4. Si es necesario, ejecuta `solucionar_acceso.php` para solucionarlo
5. Intenta ingresar con las credenciales proporcionadas
6. Si funciona, elimina todos los scripts de solución por seguridad
7. Cambia las contraseñas a valores seguros

## Contacto

Si el problema persiste después de seguir estos pasos, revisa los logs de errores en:
- `logs/app.log`
- Los logs de errores de PHP/XAMPP