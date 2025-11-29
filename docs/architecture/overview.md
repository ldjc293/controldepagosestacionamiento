# Arquitectura y Tecnologías

## Arquitectura
- **Patrón**: MVC (Model-View-Controller)
- **Stack**: Full Stack
- **Servidor**: XAMPP (Apache + MySQL + PHP)

## Tecnologías

### Backend
- PHP 7.4+
- MySQL 5.7+
- Apache
- PDO (PHP Data Objects) para conexión segura a BD
- PHPMailer: Envío de notificaciones por email
- DomPDF / TCPDF: Generación de recibos en PDF con códigos QR
- PHPSpreadsheet: Exportación de reportes a Excel e importación de usuarios
- PHPQRCode / chillerlan/php-qrcode: Generación de códigos QR en recibos

### Frontend
- HTML5
- CSS3 (Bootstrap 5.3 para diseño responsive)
- JavaScript (Vanilla JS + Fetch API)
- Bootstrap Icons
- jQuery 3.7 (AJAX y helpers)

### Estructura del Proyecto

```
controldepagosestacionamiento/
├── app/
│   ├── controllers/       # Lógica de negocio y manejo de peticiones
│   ├── models/            # Acceso a datos y reglas de negocio
│   ├── views/             # Interfaz de usuario (HTML/PHP)
│   └── helpers/           # Funciones auxiliares (PDF, Email, Validaciones)
├── config/                # Configuración global y de base de datos
├── public/                # Punto de entrada (index.php) y assets estáticos
├── database/              # Scripts SQL y migraciones
├── cron/                  # Scripts para tareas programadas
├── logs/                  # Archivos de registro del sistema
├── docs/                  # Documentación del proyecto
└── vendor/                # Dependencias de Composer
```

## Dependencias PHP (composer.json)
```json
{
  "require": {
    "phpmailer/phpmailer": "^6.8",
    "dompdf/dompdf": "^2.0",
    "phpoffice/phpspreadsheet": "^1.29",
    "chillerlan/php-qrcode": "^4.3",
    "vlucas/phpdotenv": "^5.5"
  }
}