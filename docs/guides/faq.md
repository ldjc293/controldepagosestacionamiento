# Preguntas Frecuentes y Solución de Problemas

## FAQ - Preguntas Frecuentes

**P: ¿Qué pasa si se va la luz durante la generación de mensualidades?**
R: El script usa transacciones. Si falla, se revierte todo y se puede volver a ejecutar sin duplicar.

**P: ¿Pueden dos usuarios tener el mismo email?**
R: No, el email es único por usuario. Si comparten apartamento, pueden tener emails diferentes y compartir el acceso.

**P: ¿Se pueden eliminar usuarios?**
R: Sí, pero se recomienda "desactivar" en lugar de eliminar para mantener el historial de pagos.

**P: ¿Cómo funcionan los receptores A y B?**
R: Cada posición (1-250) puede tener 2 controles: uno en receptor A y otro en receptor B. Por ejemplo, la posición 15 puede tener los controles 15A y 15B asignados a diferentes usuarios o al mismo usuario.

**P: ¿Cómo se calcula la mensualidad si un usuario tiene controles en A y B?**
R: Se suman todos los controles del usuario sin importar si son A o B. Si tiene 15A, 15B y 20A = 3 controles × $1 USD = $3 USD mensuales.

**P: ¿Qué pasa si un control está vacío?**
R: Los controles vacíos no generan mensualidades. Solo cuando se asignan a un usuario comienzan a facturarse.

**P: ¿Cómo se actualiza la tasa BCV?**
R: El administrador la actualiza manualmente desde el panel. En el futuro se puede automatizar con API del BCV.

**P: ¿El sistema funciona sin internet?**
R: Sí, funciona en red local. Solo necesita internet para envío de emails y sincronización con Google Sheets.

**P: ¿Se puede usar en varios edificios?**
R: Sí, agregando una tabla "edificios" y modificando las relaciones. Es escalable.

**P: ¿Cómo encuentro controles vacíos para asignar a nuevos residentes?**
R: El administrador tiene acceso a "Mapa de controles" y "Posiciones vacías" donde puede ver en tiempo real qué controles están disponibles, filtrar por receptor A/B y asignarlos directamente.

## Troubleshooting

### Error: "No se puede conectar a la base de datos"
- Verificar credenciales en `.env`.
- Verificar que MySQL esté corriendo.
- Verificar que la base de datos exista.

### Error: "No se pueden enviar emails"
- Verificar configuración SMTP en Admin > Configuración.
- Probar con "Enviar Email de Prueba".
- Verificar que el servidor SMTP permita la conexión.

### Error: "No se pueden subir archivos"
- Verificar permisos de carpeta `uploads/`.
- Verificar `upload_max_filesize` en php.ini.
- Verificar `post_max_size` en php.ini.

### Los CRON no se ejecutan
- Verificar que estén configurados correctamente.
- Verificar permisos de ejecución de los scripts.
- Revisar logs del sistema para errores.