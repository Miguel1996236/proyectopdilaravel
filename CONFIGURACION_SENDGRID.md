# Configuración de SendGrid para Restablecimiento de Contraseña

## ⚠️ IMPORTANTE: Verificar un remitente primero

**NO puedes usar el dominio de SendGrid directamente.** Debes verificar al menos un remitente individual (Single Sender) antes de poder enviar emails.

## Pasos para configurar

### Paso 1: Verificar un remitente en SendGrid

1. Ve a tu panel de SendGrid: https://app.sendgrid.com
2. Ve a **Settings** → **Sender Authentication** → **Single Sender Verification**
3. Haz clic en **Create New Sender**
4. Completa el formulario:
   - **From Email Address**: Tu email (puede ser tu email personal, ej: `tucorreo@gmail.com`)
   - **From Name**: El nombre que quieres que aparezca (ej: "Sistema de Encuestas")
   - **Reply To**: El mismo email o uno diferente
   - **Company Address**: Tu dirección (puede ser tu dirección personal)
   - **City, State, Zip, Country**: Tu información
5. Haz clic en **Create**
6. **IMPORTANTE**: SendGrid te enviará un email de verificación a ese correo
7. **Abre ese email y haz clic en el enlace de verificación**
8. Una vez verificado, el remitente aparecerá como "Verified" ✅

### Paso 2: Obtener tu API Key

1. Ve a **Settings** → **API Keys** en SendGrid
2. Haz clic en **Create API Key**
3. Dale un nombre (ej: "Laravel Sistema Encuestas")
4. Selecciona permisos: **Full Access** o solo **Mail Send**
5. Haz clic en **Create & View**
6. **IMPORTANTE**: Copia la API Key inmediatamente (solo se muestra una vez)
   - Formato: `SG.xxxxxxxxxxxxxxxxxxxxx.xxxxxxxxxxxxxxxxxxxxx`

### Paso 3: Agregar variables al archivo `.env`

Abre tu archivo `.env` y agrega o actualiza las siguientes líneas con tu API Key de SendGrid:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=TU_API_KEY_DE_SENDGRID_AQUI
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=el-email-que-verificaste@ejemplo.com
MAIL_FROM_NAME="Sistema de Encuestas"
```

**Nota importante:** 
- Reemplaza `TU_API_KEY_DE_SENDGRID_AQUI` con tu API Key completa de SendGrid
- Reemplaza `el-email-que-verificaste@ejemplo.com` con el **email exacto** que verificaste en SendGrid (el que aparece en "From Email Address")
- Debe ser el mismo email que verificaste, letra por letra
- El `MAIL_USERNAME` debe ser literalmente `apikey` (no tu email)
- El `MAIL_FROM_NAME` puede ser el nombre que quieras que aparezca como remitente

### Paso 4: Limpiar caché de configuración

Después de actualizar el `.env`, ejecuta:

```bash
php artisan config:clear
php artisan cache:clear
```

### Paso 5: Probar el restablecimiento

1. Ve a `/forgot-password`
2. Ingresa un email de un usuario existente
3. Revisa el email (y la carpeta de spam si no aparece)
4. Haz clic en el enlace para restablecer la contraseña

### Paso 6: Monitorear envíos

Puedes ver los emails enviados en SendGrid:
- Activity → Email Activity

## Solución de problemas

### Si no recibes emails:

1. **Verifica el remitente:** 
   - Asegúrate de que el email en `MAIL_FROM_ADDRESS` sea **exactamente** el mismo que verificaste en SendGrid
   - Ve a SendGrid → Settings → Sender Authentication y verifica que el remitente esté marcado como "Verified" ✅
2. **Revisa los logs:** `storage/logs/laravel.log` para ver errores
3. **Revisa spam:** Los emails pueden ir a la carpeta de spam
4. **Verifica la API Key:** Asegúrate de que la API Key tenga permisos de "Mail Send"
5. **Revisa Activity en SendGrid:** Ve a Activity → Email Activity para ver si el email se intentó enviar y qué pasó

### Si el remitente no está verificado:

- **Error común**: "The from address does not match a verified Sender Identity"
- **Solución**: Debes verificar el remitente en SendGrid primero (Paso 1)
- No puedes usar emails que no hayas verificado

### Si hay errores de autenticación:

- Verifica que `MAIL_USERNAME=apikey` (literalmente la palabra "apikey")
- Verifica que la API Key esté correcta y activa
- Asegúrate de que el puerto sea 587 y el encryption sea tls

## ⚠️ Seguridad

**NUNCA subas tu API Key a Git/GitHub.** 
- Las API Keys son secretos y deben estar solo en tu archivo `.env`
- El archivo `.env` debe estar en `.gitignore`
- Si accidentalmente subiste una API Key, revísala inmediatamente en SendGrid y créala de nuevo
