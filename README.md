<p align="center">
    <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="280" alt="Laravel Logo">
</p>

# Plataforma de Encuestas Educativas

Aplicación construida con **Laravel 12** y la plantilla **Start Bootstrap SB Admin 2**, enfocada en la gestión de encuestas, invitaciones y análisis apoyados por OpenAI.

## 🚀 Características principales

- Autenticación Breeze (Blade) con roles `administrador`, `docente` y `estudiante`.
- Dashboard responsive con tarjetas, gráficos (Chart.js) y sidebar dinámico.
- Integración con OpenAI centralizada en `App\Services\OpenAIService`.
- Frontend basado íntegramente en SB Admin 2: login/registro, sidebar, botones, tablas y estilos.

## 📦 Requisitos

- PHP 8.2+
- Composer 2.5+
- Node.js 18+ y npm
- MySQL/MariaDB (XAMPP recomendado)
- Extensiones PHP: `zip`, `openssl`, `pdo`, `mbstring`, `tokenizer`, `xml`, `ctype`, `json`, `fileinfo`, `curl`

## ⚙️ Instalación rápida

```bash
git clone <repo> proyectopdilaravel
cd proyectopdilaravel
composer install
npm install
cp .env.example .env        # o copiar manualmente
php artisan key:generate
```

Edita `.env` para configurar:

```env
DB_DATABASE=proyectopdi
DB_USERNAME=root
DB_PASSWORD=

OPENAI_API_KEY=tu_clave
OPENAI_MODEL_1=gpt-4o-mini
OPENAI_TEMP_1=0.7
OPENAI_MAXTOKENS_1=800
```

Luego ejecuta:

```bash
php artisan migrate --force
php artisan db:seed
npm run dev    # o npm run build
php artisan serve
```

## 👥 Accesos de ejemplo

| Rol            | Email                  | Contraseña |
|----------------|------------------------|------------|
| Administrador  | admin@example.com      | password   |
| Docente demo   | docente@example.com    | password   |
| Estudiante demo| estudiante@example.com | password   |

## 🗂️ Estructura destacada

- `resources/views/layouts/` — Layouts SB Admin 2 personalizados.
- `public/vendor`, `public/js`, `public/assets/css` — Assets originales del template.
- `app/Services/OpenAIService.php` — Servicio para consumir OpenAI con perfiles configurables.
- `database/seeders/AdminUserSeeder.php` — Creación de usuarios demo con roles.

## 🛠️ Scripts útiles

```bash
php artisan migrate:fresh --seed   # Reinicia la BD con datos demo
php artisan make:controller ...    # Generar controladores adicionales
npm run dev                        # Recarga assets durante el desarrollo
```

## ✅ Pendientes sugeridos

- Migrar el esquema completo de encuestas e invitaciones desde el proyecto legacy.
- Reemplazar enlaces del sidebar por rutas reales.
- Añadir dashboards específicos para cada rol.
- Conectar flujos de análisis con OpenAI usando el servicio centralizado.

---
Desarrollado con ❤️ para apoyar procesos educativos basados en encuestas y análisis inteligente. Ajusta libremente esta base para tus necesidades. Si tienes dudas, revisa el código o contacta al equipo. ¡Éxitos! 🎓

