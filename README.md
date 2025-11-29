# Sistema de Control de Pagos de Estacionamiento

> Sistema completo de gestión de pagos mensuales para el estacionamiento residencial de los bloques 27 al 32, con soporte multi-moneda (USD/Bs), gestión de 250 controles con receptores A/B, y generación automática de recibos con código QR.

## 📋 Información General

**Proyecto:** Sistema de Control de Pagos de Estacionamiento
**Ubicación:** Caricuao Unidad 5, Bloques 27-32
**Tecnologías:** PHP 7.4+, MySQL 5.7+, Bootstrap 5.3, Apache
**Arquitectura:** MVC (Model-View-Controller)

## 🚀 Inicio Rápido

1. **Instalación**: Sigue la guía en [`docs/guides/INSTALACION.md`](docs/guides/INSTALACION.md)
2. **Configuración**: Revisa [`docs/architecture/overview.md`](docs/architecture/overview.md) para entender la estructura
3. **Uso**: Consulta [`docs/guides/user_manual.md`](docs/guides/user_manual.md) para aprender a usar el sistema

## 📚 Documentación Completa

Toda la documentación está organizada en [`docs/`](docs/README.md):

- **🏗️ Arquitectura**: Tecnologías, base de datos, seguridad
- **💼 Negocio**: Reglas, roles, flujos de pago
- **📖 Guías**: Instalación, manual de usuario, FAQ
- **🧪 Pruebas**: Casos de prueba y reportes de calidad
- **📊 Estado**: Resumen y métricas del proyecto

## 🎯 Funcionalidades Principales

- **4 roles de usuario** con permisos diferenciados (Cliente, Operador, Consultor, Administrador)
- **Pagos multi-moneda** (USD efectivo, Bs transferencia, Bs efectivo)
- **Generación automática** de mensualidades el día 5 de cada mes
- **Gestión de controles** de estacionamiento por apartamento
- **Sistema de alertas** para morosidad (3+ meses) y bloqueos automáticos (4+ meses)
- **Recibos oficiales en PDF** con código QR y numeración única
- **Sincronización con Google Sheets** para registro contable
- **Notificaciones por email** automatizadas
- **Importación masiva** de usuarios desde Excel
- **Tasa de cambio BCV** actualizable para conversión USD/Bs
- **Responsive design** compatible con móviles y tablets

## 📞 Soporte

Si encuentras problemas no cubiertos en la documentación, consulta la sección de [Troubleshooting](docs/guides/faq.md#troubleshooting).