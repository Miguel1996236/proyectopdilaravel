# EduQuiz – Plataforma de Encuestas Educativas

Aplicación construida con **Laravel** y la plantilla **SB Admin 2**, para gestión de encuestas educativas, invitaciones y análisis con OpenAI.

---

## Requisitos previos

- **PHP 8.2+** (con extensiones: `zip`, `openssl`, `pdo`, `mbstring`, `tokenizer`, `xml`, `ctype`, `json`, `fileinfo`, `curl`)
- **Composer 2.x**
- **Node.js 18+** y **npm**
- **MySQL 5.7+** o **MariaDB** (por ejemplo con XAMPP)

---

## Instalación (clonar repo y ejecutar en tu máquina)

### 1. Clonar el repositorio

```bash
git clone https://github.com/TU_USUARIO/TU_REPO.git proyectopdilaravel
cd proyectopdilaravel
```

(Sustituye `TU_USUARIO` y `TU_REPO` por la URL real de tu repositorio en GitHub.)

### 2. Instalar dependencias PHP y Node

```bash
composer install
npm install
```

> **Mismo entorno para todos:** el repo incluye `composer.lock` y `package-lock.json`. Así, `composer install` y `npm install` instalan **las mismas versiones** de dependencias en todas las máquinas. No borres ni ignores esos archivos en el repo.

### 3. Configurar entorno (.env)

**Copia el archivo de ejemplo y genera la clave de aplicación (obligatorio):**

```bash
cp .env.example .env
php artisan key:generate
```

> **Muy importante:** sin `php artisan key:generate`, la aplicación fallará por falta de `APP_KEY`.

### 4. Crear la base de datos en MySQL

Abre **phpMyAdmin** (XAMPP) o tu cliente MySQL y crea una base de datos, por ejemplo:

- Nombre: `proyectopdi`  
- Cotejamiento: `utf8mb4_unicode_ci`

O desde consola:

```bash
mysql -u root -e "CREATE DATABASE proyectopdi CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### 5. Configurar el archivo `.env`

Abre el archivo **`.env`** en la raíz del proyecto y revisa al menos:

| Variable | Descripción | Ejemplo |
|----------|-------------|---------|
| `APP_URL` | URL donde vas a abrir la app | `http://localhost:8000` |
| `DB_DATABASE` | Nombre de la base de datos | `proyectopdi` |
| `DB_USERNAME` | Usuario MySQL | `root` |
| `DB_PASSWORD` | Contraseña MySQL (vacío en XAMPP por defecto) | `` |
| `OPENAI_API_KEY` | Clave de OpenAI (para análisis IA) | `sk-...` (opcional si no usas IA) |

- Si usas **XAMPP** con MySQL por defecto: suele bastar con dejar `DB_USERNAME=root` y `DB_PASSWORD=` (vacío).
- **OpenAI:** si no tienes clave, deja `OPENAI_API_KEY=` vacío; el resto de la app funciona, pero el análisis por IA no.

### 6. Migraciones y datos de prueba

```bash
php artisan migrate --force
php artisan db:seed
```

`db:seed` crea usuarios de ejemplo (admin, docente, estudiante) para poder entrar.

### 7. Compilar assets y arrancar el servidor

```bash
npm run build
php artisan serve
```

Abre el navegador en: **http://localhost:8000**

---

## Resumen de comandos (copiar y pegar)

Orden recomendado para quien acaba de clonar el repo:

```bash
git clone https://github.com/TU_USUARIO/TU_REPO.git proyectopdilaravel
cd proyectopdilaravel
composer install
npm install
cp .env.example .env
php artisan key:generate
# Crear BD "proyectopdi" en MySQL (phpMyAdmin o comando mysql)
# Editar .env: DB_* y opcionalmente OPENAI_API_KEY
php artisan migrate --force
php artisan db:seed
npm run build
php artisan serve
```

Luego abrir **http://localhost:8000** en el navegador.

---

## Acceso a la aplicación

- **URL:** http://localhost:8000 (si usas `php artisan serve`).
- **Login:** usa cualquiera de los usuarios creados por el seeder:

| Rol        | Email                 | Contraseña |
|-----------|------------------------|------------|
| Administrador | admin@example.com     | password   |
| Docente   | docente@example.com   | password   |
| Estudiante| estudiante@example.com| password   |

---

## Configuración del `.env` – qué tocar

- **Base de datos:**  
  `DB_CONNECTION=mysql`, `DB_DATABASE=proyectopdi`, `DB_USERNAME`, `DB_PASSWORD`. Deben coincidir con la base que creaste.

- **Aplicación:**  
  `APP_URL=http://localhost:8000` (o la URL que uses). `APP_DEBUG=true` en local está bien.

- **OpenAI (opcional):**  
  `OPENAI_API_KEY=sk-...` para análisis de encuestas y comparaciones. Si no la pones, esas funciones no usarán IA.

- **Correo:**  
  Por defecto `MAIL_MAILER=log` (los correos se escriben en `storage/logs`). Para enviar correos reales, cambia a `smtp` y configura host, usuario y contraseña.

---

## Desarrollo

- Recargar assets al cambiar CSS/JS:  
  `npm run dev` (en otra terminal mientras `php artisan serve` está activo).
- Reiniciar base de datos con datos de prueba:  
  `php artisan migrate:fresh --seed`
- Colas (si en el futuro usas `queue` en lugar de síncrono):  
  `php artisan queue:work`

---

## Estructura destacada

- `resources/views/layouts/` — Layouts (app y guest).
- `resources/views/quizzes/` — Vistas de encuestas y análisis.
- `app/Services/OpenAIService.php` — Integración OpenAI.
- `database/seeders/AdminUserSeeder.php` — Usuarios demo.

---

## Características principales

- Autenticación con roles: **administrador**, **docente**, **estudiante**.
- Dashboard, encuestas (quizzes), preguntas, opciones, invitaciones por código.
- Intentos y respuestas; análisis con OpenAI (resumen, recomendaciones, exportar informe PDF).
- Grupos de estudiantes, recordatorios por correo, comparación entre encuestas (IA).
- Reportes (resumen, estudiantes, encuestas).

---

Si algo falla, revisa que la base de datos exista, que `.env` tenga `APP_KEY` (tras `php artisan key:generate`) y que las variables `DB_*` coincidan con tu MySQL. Para más detalle, consulta `storage/logs/laravel.log`.
