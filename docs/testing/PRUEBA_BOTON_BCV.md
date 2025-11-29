# 🧪 Guía de Prueba: Botón "Actualizar desde BCV"

## ✅ Mejoras Implementadas

### 1. **Comunicación AJAX**
- Ya no recarga la página completa
- Respuesta en tiempo real
- Mejor experiencia de usuario

### 2. **Feedback Visual Mejorado**
- Indicador de carga mientras consulta
- Notificaciones toast con Bootstrap
- Actualización automática del campo de tasa

### 3. **Manejo de Errores Robusto**
- Mensajes de error claros y específicos
- Timeout de 60 segundos (por defecto del navegador)
- Retry manual disponible

### 4. **Actualización Automática de la UI**
- El campo de tasa se actualiza sin recargar
- La fecha de actualización se actualiza automáticamente
- El botón se restaura después de 2 segundos

---

## 📋 Pasos para Probar

### **Paso 1: Acceder a la Configuración**
1. Abrir en el navegador: `http://localhost/controldepagosestacionamiento/admin/configuracion`
2. Iniciar sesión como administrador si es necesario

### **Paso 2: Localizar el Botón**
1. Buscar la sección "Configuración General"
2. Encontrar el campo "Tasa de Cambio BCV (Bs por USD)"
3. Al lado del campo deshabilitado está el botón **"Actualizar desde BCV"**

### **Paso 3: Probar la Funcionalidad**
1. Click en el botón **"Actualizar desde BCV"**
2. Confirmar en el diálogo que aparece
3. **Observar el proceso:**
   - El botón se deshabilita
   - El texto cambia a: "⏳ Consultando BCV..."
   - Esperar 5-15 segundos (depende de la velocidad del BCV)

### **Paso 4: Verificar el Resultado**

#### ✅ **Si tiene éxito:**
- Aparece una notificación verde (toast) en la esquina superior derecha
- El mensaje dice: "Tasa BCV actualizada correctamente a X.XX Bs/USD"
- El campo de tasa se actualiza con el nuevo valor
- La fecha de "Última actualización" se actualiza
- El botón se restaura después de 2 segundos

#### ❌ **Si hay un error:**
- Aparece una notificación roja (toast)
- El mensaje explica el error específico
- El botón se restaura inmediatamente
- Puedes intentar de nuevo

---

## 🔍 Casos de Prueba

### **Caso 1: Actualización Exitosa**
**Precondición:** Conexión a internet estable

**Pasos:**
1. Click en "Actualizar desde BCV"
2. Confirmar el diálogo
3. Esperar la respuesta

**Resultado Esperado:**
- ✅ Notificación verde de éxito
- ✅ Campo de tasa actualizado
- ✅ Fecha actualizada

---

### **Caso 2: Sin Conexión a Internet**
**Precondición:** Desconectar internet temporalmente

**Pasos:**
1. Desconectar la conexión a internet
2. Click en "Actualizar desde BCV"
3. Confirmar el diálogo

**Resultado Esperado:**
- ❌ Notificación roja: "Error de conexión. Verifique su conexión a internet e intente nuevamente."
- ✅ Botón se restaura para reintentar

---

### **Caso 3: BCV No Disponible**
**Precondición:** El sitio del BCV está caído o bloqueando

**Pasos:**
1. Click en "Actualizar desde BCV"
2. Confirmar el diálogo
3. Esperar hasta 30 segundos

**Resultado Esperado:**
- ❌ Notificación: "No se pudo obtener la tasa del BCV. Verifique su conexión a internet o intente más tarde."
- ✅ Botón se restaura

---

### **Caso 4: Verificación en Base de Datos**
**Pasos:**
1. Después de una actualización exitosa
2. Abrir phpMyAdmin o ejecutar consulta SQL:
```sql
SELECT id, tasa_usd_bs, fecha_registro, fuente
FROM tasa_cambio_bcv
ORDER BY fecha_registro DESC
LIMIT 5;
```

**Resultado Esperado:**
- ✅ Nuevo registro con fuente "BCV Automático"
- ✅ Tasa actualizada (actualmente ~226.13 Bs/USD)
- ✅ Fecha y hora del registro actual

---

## 🐛 Troubleshooting

### **Problema: El botón no hace nada**
**Soluciones:**
1. Abrir la consola del navegador (F12 → Console)
2. Buscar errores de JavaScript
3. Verificar que `URL_BASE` esté definido correctamente
4. Verificar que el archivo tenga los cambios guardados (Ctrl+F5 para limpiar caché)

### **Problema: Error "Token de seguridad inválido"**
**Soluciones:**
1. Recargar la página completamente (Ctrl+F5)
2. Cerrar sesión y volver a iniciar
3. Verificar que las cookies estén habilitadas

### **Problema: Timeout (tarda demasiado)**
**Soluciones:**
1. El BCV puede estar lento, esperar hasta 60 segundos
2. Verificar conexión a internet
3. Intentar nuevamente más tarde

### **Problema: No aparecen las notificaciones toast**
**Verificación:**
1. Abrir la consola del navegador
2. Si hay errores de Bootstrap, la función usará `alert()` como fallback
3. Verificar que Bootstrap 5 esté cargado correctamente

---

## 🔧 Archivos Modificados

### 1. **AdminController.php** (líneas 1430-1537)
- ✅ Método `actualizarTasaBCV()` actualizado para soportar AJAX
- ✅ Agregado método helper `isAjaxRequest()`
- ✅ Retorna JSON cuando es petición AJAX
- ✅ Mejor manejo de errores

### 2. **configuracion.php** (líneas 354-473)
- ✅ Función `actualizarTasaAutomatica()` convertida a AJAX
- ✅ Agregada función `showToast()` para notificaciones
- ✅ Actualización automática de la UI
- ✅ Eliminado formulario oculto (ya no necesario)

---

## 📊 Comparación: Antes vs Después

| Aspecto | Antes | Después |
|---------|-------|---------|
| **Método** | POST tradicional | AJAX (fetch API) |
| **Recarga página** | ✅ Sí | ❌ No |
| **Feedback visual** | Solo spinner inicial | Spinner + Toast + Actualización UI |
| **Manejo de errores** | Mensaje genérico en página | Mensaje específico en toast |
| **Experiencia de usuario** | 😐 Básica | 😊 Mejorada |
| **Actualización de datos** | Manual (recargar) | Automática |
| **Timeout** | ~30 seg (PHP) | 60+ seg (navegador) |

---

## ✅ Checklist de Verificación Final

- [ ] El botón responde al hacer click
- [ ] Aparece el mensaje "Consultando BCV..."
- [ ] Se muestra una notificación toast
- [ ] El campo de tasa se actualiza automáticamente
- [ ] La fecha de actualización cambia
- [ ] El botón se restaura después del proceso
- [ ] Los errores se manejan correctamente
- [ ] Los datos se guardan en la base de datos

---

## 📞 Soporte

Si encuentras algún problema durante las pruebas:
1. Revisa la consola del navegador (F12)
2. Verifica los logs en `logs/app.log`
3. Ejecuta el script de prueba manual: `manual_update_bcv.php`

---

**Estado:** ✅ Implementación completada
**Fecha:** 2025-11-05
**Versión:** 2.0 (AJAX)
