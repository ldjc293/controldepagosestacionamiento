# Cómo Ejecutar los Scripts de Solución

## Método 1: Desde el Navegador (Recomendado)

Los scripts están diseñados para ejecutarse directamente desde tu navegador web. Sigue estos pasos:

### Paso 1: Asegurar que XAMPP esté funcionando
1. Abre el panel de control de XAMPP
2. Asegúrate que Apache y MySQL estén iniciados (en verde)
3. Si no están iniciados, haz clic en "Start" para cada uno

### Paso 2: Abrir el navegador
1. Abre tu navegador web (Chrome, Firefox, Edge, etc.)
2. En la barra de direcciones, escribe: `http://localhost/`
3. Deberías ver la página de bienvenida de XAMPP

### Paso 3: Ejecutar los scripts en orden

**Script 1: Crear tablas faltantes**
```
http://localhost/controldepagosestacionamiento/crear_tablas_faltantes.php
```
- Copia esta dirección completa en la barra de direcciones del navegador
- Presiona Enter
- Espera a que cargue y muestre los resultados
- Deberías ver mensajes en verde indicando que las tablas se crearon correctamente

**Script 2: Agregar columna meses_bloqueo**
```
http://localhost/controldepagosestacionamiento/agregar_columna_meses_bloqueo.php
```
- Copia esta dirección completa en la barra de direcciones del navegador
- Presiona Enter
- Espera a que cargue y muestre los resultados
- Deberías ver mensajes en verde indicando que la columna se agregó correctamente

**Script 3: Verificar estado de usuarios**
```
http://localhost/controldepagosestacionamiento/verificar_usuarios.php
```
- Copia esta dirección completa en la barra de direcciones del navegador
- Presiona Enter
- Revisa la información que muestra sobre los usuarios
- Anota cualquier problema que detecte

**Script 4: Solucionar problemas de acceso**
```
http://localhost/controldepagosestacionamiento/solucionar_acceso.php
```
- Copia esta dirección completa en la barra de direcciones del navegador
- Presiona Enter
- Espera a que cargue y muestre los resultados
- Deberías ver mensajes en verde indicando que los usuarios fueron actualizados

## Método 2: Desde la Línea de Comandos (Avanzado)

Si prefieres usar la línea de comandos:

### Paso 1: Abrir terminal
1. Presiona `Windows + R`
2. Escribe `cmd` y presiona Enter

### Paso 2: Navegar al directorio de XAMPP
```cmd
cd C:\xampp\htdocs
```

### Paso 3: Ejecutar los scripts con PHP
```cmd
php controldepagosestacionamiento\crear_tablas_faltantes.php
php controldepagosestacionamiento\agregar_columna_meses_bloqueo.php
php controldepagosestacionamiento\verificar_usuarios.php
php controldepagosestacionamiento\solucionar_acceso.php
```

## Verificación Final

Después de ejecutar todos los scripts:

1. **Accede al sistema**:
   ```
   http://localhost/controldepagosestacionamiento/
   ```

2. **Inicia sesión con las credenciales**:
   - Email: `admin@estacionamiento.local`
   - Contraseña: `admin123`

3. **Si funciona correctamente**, elimina los scripts de seguridad:
   - `crear_tablas_faltantes.php`
   - `agregar_columna_meses_bloqueo.php`
   - `verificar_usuarios.php`
   - `solucionar_acceso.php`

## Solución de Problemas Comunes

### "Página no encontrada" (Error 404)
- Verifica que XAMPP esté iniciado
- Asegúrate que la ruta sea correcta: `http://localhost/controldepagosestacionamiento/`
- Verifica que los archivos estén en `C:\xampp\htdocs\controldepagosestacionamiento\`

### "Error de conexión a la base de datos"
- Verifica que MySQL esté iniciado en XAMPP
- Revisa el archivo `.env` para asegurar que los datos de conexión son correctos:
  ```
  DB_HOST=localhost
  DB_PORT=3306
  DB_NAME=estacionamiento_db
  DB_USER=root
  DB_PASS=
  ```

### "Permission denied" o "Acceso denegado"
- Asegúrate que XAMPP tenga permisos de escritura en las carpetas
- Ejecuta el navegador como administrador

### "Script no se ejecuta, solo muestra el código"
- Esto indica que PHP no está configurado correctamente en XAMPP
- Reinstala XAMPP o verifica la configuración de Apache

## Capturas de Pantalla de Referencia

### Ejecución exitosa de un script:
```
✓ Tabla 'configuracion_cron' creada correctamente
✓ Tabla 'login_intentos' creada correctamente
✓ Tabla 'password_reset_tokens' creada correctamente
✓ Vista 'vista_morosidad' creada correctamente
✓ Vista 'vista_controles_vacios' creada correctamente
✓ ¡Proceso completado con éxito!
```

### Errores comunes:
```
Warning: require_once(config/database.php): failed to open stream...
```
→ Solución: Verifica que el archivo exista en la ruta correcta

```
Fatal error: Uncaught PDOException: SQLSTATE[HY000] [2002] No connection could be made...
```
→ Solución: Inicia MySQL en XAMPP

## Contacto

Si después de seguir estos pasos sigues teniendo problemas, proporciona:
1. Captura de pantalla del error exacto
2. Versión de XAMPP instalada
3. Sistema operativo que estás usando
4. Mensaje completo del error que aparece