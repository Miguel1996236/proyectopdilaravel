# Guía de Control de Versiones - Proyecto Laravel

## Dependencias no versionadas

Este proyecto sigue las buenas prácticas de control de versiones para Laravel. Las siguientes dependencias y archivos **no se versionan** en el repositorio:

- **`/vendor/`** — Dependencias de PHP (Composer)
- **`/node_modules/`** — Dependencias de JavaScript (npm)
- **`/public/vendor/`** — Assets de librerías frontend (Bootstrap, jQuery, etc.)
- **`/storage/*.key`** — Claves de cifrado
- **`/storage/logs/*`** — Archivos de logs
- **`/bootstrap/cache/*`** — Archivos de caché de Laravel
- **`.env`** — Variables de entorno (contiene credenciales y configuración sensible)

Esto reduce el tamaño del repositorio y evita conflictos innecesarios en las fusiones de código.

---

## Cómo instalar el proyecto

Después de clonar el repositorio, ejecuta los siguientes comandos en orden:

```bash
# 1. Instalar dependencias de PHP
composer install

# 2. Instalar dependencias de JavaScript
npm install

# 3. Generar la clave de aplicación de Laravel
php artisan key:generate

# 4. Ejecutar las migraciones de base de datos
php artisan migrate
```

### Pasos adicionales (opcionales)

- **Crear enlace simbólico para storage:** `php artisan storage:link`
- **Compilar assets:** `npm run build` o `npm run dev`
- **Copiar archivo de entorno:** `cp .env.example .env` (si no existe `.env`)

---

## Requisitos previos

- PHP 8.1 o superior
- Composer
- Node.js y npm
- Base de datos compatible (MySQL, PostgreSQL, SQLite, etc.)
