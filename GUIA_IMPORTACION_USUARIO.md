# 📋 Guía Rápida: Importación de Mobiliario desde Filament

## ⚠️ IMPORTANTE: Requisito Previo

Para que la importación desde la interfaz web funcione, el **worker de cola** debe estar corriendo.

### ¿Cómo verifico si está corriendo?

Ejecuta en la terminal de WSL:
```bash
cd /home/nformatica/Activo-Fijo-HRAE-DIMP
ps aux | grep "queue:work" | grep -v grep
```

Si ves procesos listados = ✅ Está corriendo
Si no ves nada = ❌ No está corriendo

### ¿Cómo lo inicio?

**Opción 1 - Automático (RECOMENDADO):**
```bash
cd /home/nformatica/Activo-Fijo-HRAE-DIMP
nohup ./worker_daemon.sh > worker.log 2>&1 &
```
✅ Puedes cerrar la terminal
✅ Se reinicia automáticamente
✅ Registra todo en worker.log

**Opción 2 - Manual:**
```bash
cd /home/nformatica/Activo-Fijo-HRAE-DIMP
./vendor/bin/sail artisan queue:work --tries=3 --timeout=300
```
⚠️ Debes mantener la terminal abierta

## 📥 Pasos para Importar desde Filament

### 1. Asegúrate que el worker esté corriendo (ver arriba)

### 2. En el navegador:
1. Ve a **Mobiliario** en el menú lateral
2. Haz clic en **"Importar desde Sistema Anterior"** (botón naranja en la parte superior)
3. Selecciona tu archivo CSV
4. Las columnas se mapearán automáticamente
5. Haz clic en **"Importar"**

### 3. Espera el resultado:
- Verás un mensaje: "La importación se ha iniciado..."
- El proceso continúa en segundo plano
- Recibirás una notificación cuando termine (campana en la esquina superior derecha)
- Si hay errores, podrás descargar un CSV con los detalles

## 🔍 Monitorear el Progreso

```bash
# Ver el log del worker
tail -f worker.log

# Ver el log de Laravel
tail -f storage/logs/laravel.log
```

## ❌ Solución de Problemas

### "La importación continúa pero no pasa nada"
**Causa:** El worker no está corriendo
**Solución:** Inicia el worker (ver sección de arriba)

### "Los jobs fallan constantemente"
**Solución:**
```bash
cd /home/nformatica/Activo-Fijo-HRAE-DIMP
./vendor/bin/sail artisan queue:clear
./vendor/bin/sail artisan queue:flush
pkill -f "queue:work"
nohup ./worker_daemon.sh > worker.log 2>&1 &
```

### Detener el worker
```bash
pkill -f "queue:work"
```

## 📊 Formato del CSV

El CSV del sistema anterior debe tener estas columnas principales:
- Clave del Bien
- Nombre del Bien
- Grupo, Subgrupo, Clase
- Marca, Modelo
- Valor
- F. de Baja (opcional)
- Proveedor (opcional)

Ver `bienes_importar.csv` como ejemplo.

## ✅ Verificar Importación Exitosa

```bash
cd /home/nformatica/Activo-Fijo-HRAE-DIMP
./vendor/bin/sail php verificar_importacion.php
```

---

**💡 Consejo:** Mantén siempre el worker corriendo mientras uses el sistema para que todas las funciones que requieren cola (importaciones, notificaciones, etc.) funcionen correctamente.
