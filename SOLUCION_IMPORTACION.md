# 🔧 Solución al Problema de Importación de CSV del Sistema Legacy

## Problema Identificado

La importación de CSV del sistema anterior estaba fallando debido a varios problemas:

1. **Campo `attempts` en tabla `jobs`** - Limitado a 255 (TINYINT), causaba errores cuando los jobs fallaban repetidamente
2. **Columna `codigo` inexistente** - El importer buscaba una columna que no existe en `clasificacion_bienes`
3. **Manejo de errores insuficiente** - Los métodos `resolver*()` no tenían caché ni manejo robusto de errores
4. **Falta de valores por defecto** - Algunos campos obligatorios no tenían fallbacks

## Cambios Realizados

### 1. Corregida Columna `attempts` en Tabla `jobs`

Cambió de `TINYINT UNSIGNED` (máx 255) a `SMALLINT UNSIGNED` (máx 65,535):

```sql
ALTER TABLE jobs MODIFY attempts SMALLINT UNSIGNED NOT NULL;
```

### 2. Corregido `resolverClasificacionBien()`

**Antes:**
```php
$clasificacion = ClasificacionBien::where('codigo', $codigo)->first();
```

**Ahora:**
```php
$clasificacion = ClasificacionBien::where('grupo', intval($this->data['grupo']))
    ->where('subgrupo', intval($this->data['subgrupo']))
    ->where('clase', intval($this->data['clase']))
    ->first();
```

### 3. Agregado Caché Estático

Todos los métodos `resolver*()` ahora usan caché estático para evitar consultas repetidas:
- `resolverClasificacionBien()`
- `resolverTipoMobiliario()`
- `resolverLocalizacion()`
- `resolverProveedor()`

### 4. Manejo Robusto de Errores

Todos los métodos tienen try-catch y fallbacks a ID 1 si no se puede crear registros.

## Pasos para Usar la Importación

### 1. Verificar que el Worker Esté Corriendo

**CRÍTICO:** El worker de colas DEBE estar corriendo para procesar las importaciones desde Filament.

**Verificar si está corriendo:**
```bash
cd /home/nformatica/Activo-Fijo-HRAE-DIMP
ps aux | grep "queue:work" | grep -v grep
```

**Iniciar el worker automático (RECOMENDADO):**
```bash
cd /home/nformatica/Activo-Fijo-HRAE-DIMP
nohup ./worker_daemon.sh > worker.log 2>&1 &
```

Este script:
- ✅ Se ejecuta en segundo plano (no necesitas mantener la terminal abierta)
- ✅ Reinicia automáticamente si se cae
- ✅ Registra todo en `worker.log`

**Detener el worker:**
```bash
pkill -f "queue:work"
```

**Ver el log del worker:**
```bash
tail -f worker.log
```

### 2. Limpiar Cola (Si es Necesario)

Si hay jobs atrapados o fallidos:

```bash
cd /home/nformatica/Activo-Fijo-HRAE-DIMP
./vendor/bin/sail artisan queue:clear
./vendor/bin/sail artisan queue:flush
./vendor/bin/sail artisan queue:restart
```

### 3. Importar el CSV

1. Ve a **Mobiliario** en el panel de Filament
2. Haz clic en **"Importar desde Sistema Anterior"** (botón naranja/warning)
3. Selecciona tu archivo CSV
4. El sistema mapeará automáticamente las columnas
5. Haz clic en **"Importar"**

### 4. Monitorear el Progreso

```bash
# Ver logs en tiempo real
tail -f storage/logs/laravel.log

# Ver estado del worker
./vendor/bin/sail artisan queue:monitor
```

## Formato del CSV (Sistema Legacy)

El CSV del sistema anterior debe tener estas columnas:

### Mapeo de Columnas

| Columna CSV | Campo en BD |
|-------------|-------------|
| Clave del Bien | numero_inventario |
| Nombre del Bien | descripcion |
| Grupo | Parte de clasificacion_bienes_id |
| Subgrupo | Parte de clasificacion_bienes_id |
| Clase | Parte de clasificacion_bienes_id |
| Marca | marca |
| Modelo | modelo |
| N. de Serie | numero_serie |
| No Factura | numero_folio |
| Proveedor | proveedor_id (resuelto por nombre) |
| F. Adquisición | metodo_adquisicion |
| F. de Baja | fecha_baja |
| Valor | precio |
| Dirección | localizacion_id (resuelto por nombre) |
| Responsable | responsable_actual |
| Clave Emp. | matricula_responsable |
| Puesto | puesto_responsable |

## Scripts de Utilidad Creados

### 1. `fix_jobs_table.php`
Corrige la columna `attempts` en la tabla `jobs`:
```bash
./vendor/bin/sail php fix_jobs_table.php
```

### 2. `test_import_legacy.php`
Prueba la importación de una fila sin afectar la BD:
```bash
./vendor/bin/sail php test_import_legacy.php
```

### 3. `clear_queues.sh`
Limpia todas las colas:
```bash
./clear_queues.sh
```

## Solución de Problemas

### Los jobs siguen fallando

1. **Verificar el worker:**
   ```bash
   ps aux | grep queue:work
   ```

2. **Ver errores específicos:**
   ```bash
   tail -100 storage/logs/laravel.log | grep ERROR
   ```

3. **Limpiar y reiniciar:**
   ```bash
   ./vendor/bin/sail artisan queue:clear
   ./vendor/bin/sail artisan queue:flush
   ./vendor/bin/sail artisan queue:restart
   ```

### La importación no inicia

- **Causa:** No hay worker corriendo
- **Solución:** Inicia el worker en una terminal separada y mantenla abierta

### Errores de relaciones (clasificación, tipo, localización)

- **Causa:** IDs no existen en las tablas relacionadas
- **Solución:** El importer ahora crea registros por defecto automáticamente

### Error "Column not found: codigo"

- **Causa:** Versión antigua del código cargada en memoria
- **Solución:**
  ```bash
  ./vendor/bin/sail artisan queue:restart
  ./vendor/bin/sail artisan cache:clear
  ./vendor/bin/sail artisan config:clear
  ```

## Verificación Post-Importación

1. En Filament, revisa los registros importados en **Mobiliario**
2. Si hay errores, descarga el CSV de errores
3. Corrige los datos y vuelve a importar solo las filas fallidas

¡La importación ahora debería funcionar correctamente! 🎉
