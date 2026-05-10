# Guía: Cómo Usar la Colección de Postman para WPPConnect API

## 📋 Requisitos previos

1. Tener **Postman** instalado (descárgalo desde https://www.postman.com/downloads/)
2. El servidor WPPConnect debe estar corriendo: `npm run dev` en la carpeta `wppconnect-api`
3. El servidor debe estar en el puerto `21465`

## 🚀 Pasos para configurar y probar

### 1. Importar la colección en Postman

1. Abre Postman
2. Haz clic en el botón **Import** (arriba a la izquierda)
3. Selecciona **Upload Files**
4. Busca y selecciona el archivo `WPPConnect-API.postman_collection.json`
5. Haz clic en **Import**

### 2. Configurar las Variables

Después de importar, debes actualizar las variables para tu entorno:

1. En Postman, ve a la pestaña **Collections** (lado izquierdo)
2. Busca **WPPConnect API** y haz clic en el ícono `...` junto a su nombre
3. Selecciona **Edit**
4. Ve a la pestaña **Variables**

**Variables a configurar:**

| Variable | Valor Actual | Descripción |
|----------|--------------|-------------|
| `baseUrl` | `http://localhost:21465` | URL del servidor (deja como está si corres localmente) |
| `session` | `victor` | Nombre de tu sesión (puedes cambiar por otro nombre) |
| `secretKey` | `THISISMYSECURETOKEN` | La clave secreta del config.ts del servidor |
| `token` | *(vacío)* | Se llena después de ejecutar "Generate Token" |
| `phone` | `5511999999999` | Número de teléfono para pruebas (incluir código país) |
| `groupId` | `120363000000000000@g.us` | ID de grupo (se obtiene al crear un grupo) |

### 3. Flujo de Uso Correcto

#### **Paso A: Generar Token (PRIMERO)**

1. En la colección, abre la carpeta **Auth**
2. Selecciona **Generate Token**
3. Haz clic en **Send**
4. Verás una respuesta con un campo `full` que contiene un token largo así:
   ```json
   {
     "status": "Success",
     "session": "victor",
     "token": "$2b$10$...",
     "full": "victor:$2b$10$..."
   }
   ```
5. **IMPORTANTE**: Copia el valor del campo `full`
6. Vuelve a **Variables** de la colección y pega ese valor en `token`
7. Haz clic en **Save**

#### **Paso B: Iniciar Sesión y Obtener QR**

1. Abre la carpeta **Sessions**
2. Selecciona **Start Session**
3. Haz clic en **Send**
4. Si todo va bien, recibirás una respuesta con un QR en base64 así:
   ```json
   {
     "status": "QRCODE",
     "qrcode": "iVBORw0KGgoAAAANSUhEUgAA...",
     "urlcode": "https://..."
   }
   ```
5. El QR también debería aparecer en la consola del servidor

#### **Paso C: Escanear el QR**

1. Toma tu teléfono con WhatsApp abierto
2. Ve a **Configuración > Dispositivos vinculados**
3. Haz clic en **Vincular un dispositivo**
4. **IMPORTANTE**: Para ver el QR en Postman, ve a la pestaña **Visualize** en la respuesta (si Postman tiene activada esa opción)
5. Escanea el código QR con tu teléfono
6. Espera a que se conecte (verás cambios en el estado de la sesión)

#### **Paso D: Verificar Conexión**

1. En **Sessions**, selecciona **Get Session State**
2. Haz clic en **Send**
3. Debería devolver:
   ```json
   {
     "status": "CONNECTED",
     "qrcode": null
   }
   ```

### 4. Probar Envío de Mensajes (Una Vez Conectado)

#### **Enviar Mensaje de Texto**

1. Abre la carpeta **Messages**
2. Selecciona **Send Message**
3. En el **Body**, asegúrate de que el número sea válido (ejemplo: `5521999999999`)
4. Haz clic en **Send**
5. Verás la confirmación en la respuesta

#### **Enviar Imagen**

1. En **Messages**, selecciona **Send Image**
2. Debes proporcionar una imagen en base64 (o una ruta local)
3. Haz clic en **Send**

### 5. Otros Endpoints Útiles

#### **Obtener Todos los Chats**
- **Path**: Chats > All Chats
- Devuelve lista de conversaciones

#### **Crear un Grupo**
- **Path**: Groups > Create Group
- Parámetros: nombre del grupo y lista de teléfonos

#### **Obtener Mensajes No Leídos**
- **Path**: Chats > Unread Messages
- Devuelve todos los mensajes sin leer

---

## ⚠️ Problemas Comunes

### "Invalid or expired token"
- Vuelve a ejecutar **Generate Token**
- Copia el valor `full` nuevamente en la variable `token`
- Guarda los cambios

### "Session not found"
- Asegúrate de que la variable `{{session}}` sea `victor` (o el nombre que configuraste)
- Verifica que en el servidor se ejecutó `startAllSession` o iniciaste manualmente la sesión

### "Address already in use :::21465"
- El puerto está ocupado. Ejecuta en PowerShell:
  ```powershell
  Get-NetTCPConnection -LocalPort 21465 -State Listen
  ```
  Luego detén el proceso con:
  ```powershell
  Stop-Process -Id <PID> -Force
  ```

### El QR no aparece en la respuesta
- El servidor está arrancando pero aún no tiene sesión activa
- Espera unos segundos y vuelve a llamar a `/start-session`
- Verifica en la consola del servidor que aparezca el QR en ASCII

---

## 📚 Documentación Completa

Para ver todos los endpoints disponibles, una vez que el servidor esté corriendo, accede a:

```
http://localhost:21465/api-docs
```

Aquí verás el Swagger con la documentación completa de todos los endpoints.

---

## 🎯 Resumen de Flujo Rápido

```
1. Ejecutar "Generate Token" → Copiar "full" → Pegar en variable {{token}}
2. Ejecutar "Start Session" → Obtener QR → Escanear con teléfono
3. Verificar "Get Session State" → Debe decir "CONNECTED"
4. Enviar mensajes con "Send Message"
```

¡Listo! Ahora puedes probar toda la API desde Postman.
