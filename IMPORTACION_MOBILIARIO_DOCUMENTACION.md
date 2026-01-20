# 📥 Importación de Mobiliario - Documentación

## Descripción General

El sistema de importación de mobiliario permite cargar múltiples registros desde un archivo CSV, facilitando la carga masiva de datos al sistema. Esta funcionalidad utiliza el sistema de importación de Filament v5 con procesamiento en segundo plano mediante colas.

## Características Principales

✅ **Importación desde CSV** - Sube archivos CSV con datos de mobiliario  
✅ **Validación en tiempo real** - Valida cada fila antes de importar  
✅ **Procesamiento en segundo plano** - Usa colas de Laravel para procesar grandes volúmenes  
✅ **Mapeo de columnas** - Interfaz visual para mapear columnas del CSV  
✅ **Ejemplo descargable** - Descarga un CSV de ejemplo con todas las columnas  
✅ **Reporte de errores** - Descarga CSV con filas que fallaron y sus errores  
✅ **Actualización o creación** - Opción para actualizar registros existentes  
✅ **Notificaciones** - Notifica cuando la importación se completa

## Ubicación

La acción de importación está disponible en dos lugares:

1. **Encabezado de la página de lista** - Botón "Importar Mobiliario" (verde)
2. **Acciones de la tabla** - Botón "Importar CSV" (azul) en el header de la tabla

## Estructura del CSV

### Columnas Requeridas

Las siguientes columnas son obligatorias para cada registro:

| Columna | Descripción | Ejemplo |
|---------|-------------|---------|
| `numero_control` | Número de control único | MOB-2024-001 |
| `clasificacion_bienes_id` | ID de clasificación | 1 |
| `caracteristicas` | Características del equipo | Color negro, material plástico |
| `descripcion` | Descripción del mobiliario | Silla ejecutiva ergonómica |
| `marca` | Marca del producto | HP |
| `modelo` | Modelo del producto | EliteDesk 800 |
| `precio` | Precio del producto | 15000.00 |
| `tipo_mobiliario_id` | ID del tipo de mobiliario | 1 |
| `localizacion_id` | ID de la localización | 1 |
| `tiene_folio` | Tiene folio (1 o 0) | 1 |
| `estado_mobiliario` | Estado del equipo | Nuevo, Usado, Baja, Restaurado |
| `tiene_accesorios` | Tiene accesorios (1 o 0) | 0 |

### Columnas Opcionales

| Columna | Descripción | Ejemplo |
|---------|-------------|---------|
| `numero_inventario` | Número de inventario anterior | INV-2024-001 |
| `numero_serie` | Número de serie | SN123456789 |
| `proveedor_id` | ID del proveedor | 1 |
| `metodo_adquisicion` | Método de adquisición | Compra directa |
| `numero_folio` | Número de folio | FOL-2024-001 |
| `descripcion_accesorios` | Descripción de accesorios | Mouse y teclado inalámbrico |
| `dado_de_baja` | Está dado de baja (1 o 0) | 0 |
| `fecha_baja` | Fecha de baja | 2024-12-31 |
| `motivo_baja` | Motivo de la baja | Equipo obsoleto |

## Proceso de Importación

### 1. Preparar el Archivo CSV

1. Descarga el archivo de ejemplo haciendo clic en "Descargar ejemplo" en el modal de importación
2. Abre el archivo en Excel, Google Sheets o tu editor preferido
3. Llena los datos siguiendo el formato de las columnas
4. Guarda el archivo como CSV (UTF-8)

**Consejos para preparar el CSV:**

- Asegúrate de que los IDs de relaciones existan en la base de datos
- Los valores booleanos deben ser 1 (true) o 0 (false)
- El formato de fecha debe ser: YYYY-MM-DD (ej: 2024-12-31)
- El precio debe usar punto decimal, no coma (15000.50)
- El estado_mobiliario solo acepta: Nuevo, Usado, Baja, Restaurado

### 2. Iniciar la Importación

1. Haz clic en el botón "Importar Mobiliario" o "Importar CSV"
2. En el modal, puedes:
   - **Descargar ejemplo** - Obtener un CSV con todas las columnas
   - **Seleccionar archivo** - Cargar tu archivo CSV
   - **Actualizar existentes** - Marcar si deseas actualizar registros que ya existen

### 3. Mapear Columnas

El sistema intentará mapear automáticamente las columnas de tu CSV con las columnas de la base de datos. Si alguna columna no se mapea correctamente:

1. Revisa las columnas marcadas en rojo (requeridas sin mapear)
2. Selecciona la columna correcta del CSV para cada campo
3. Las columnas opcionales pueden dejarse sin mapear

### 4. Procesar Importación

1. Haz clic en "Importar"
2. La importación se procesa en segundo plano
3. Recibirás una notificación cuando se complete
4. Si hay errores, podrás descargar un CSV con las filas que fallaron

## Opciones de Importación

### Actualizar Registros Existentes

Marca la casilla "Actualizar registros existentes" si deseas:

- **Marcado**: Actualizar registros que coincidan por `numero_control`
- **No marcado**: Solo crear nuevos registros, fallar si existe uno con el mismo `numero_control`

## Validaciones

### Validaciones de Formato

- **numero_control**: Único, máximo 255 caracteres
- **precio**: Debe ser numérico, mayor o igual a 0
- **estado_mobiliario**: Solo valores permitidos: Nuevo, Usado, Baja, Restaurado
- **fecha_baja**: Formato de fecha válido (YYYY-MM-DD)
- **tiene_folio, tiene_accesorios, dado_de_baja**: Valores booleanos (0 o 1)

### Validaciones de Relaciones

- **clasificacion_bienes_id**: Debe existir en la tabla clasificacion_bienes
- **tipo_mobiliario_id**: Debe existir en la tabla tipo_mobiliario
- **localizacion_id**: Debe existir en la tabla localizacion
- **proveedor_id**: Debe existir en la tabla proveedor (si se proporciona)

## Manejo de Errores

### Filas con Errores

Si alguna fila falla durante la validación o importación:

1. La fila NO se importará
2. El error se registrará
3. Al finalizar, podrás descargar un CSV con todas las filas que fallaron
4. El CSV de errores incluirá una columna adicional con el mensaje de error

### Errores Comunes y Soluciones

| Error | Causa | Solución |
|-------|-------|----------|
| "número de control duplicado" | Ya existe ese número | Cambia el número o activa "Actualizar existentes" |
| "clasificacion_bienes_id inválido" | ID no existe | Verifica que el ID exista en la base de datos |
| "precio inválido" | Formato incorrecto | Usa punto decimal, no coma (15000.50) |
| "estado_mobiliario inválido" | Valor no permitido | Usa solo: Nuevo, Usado, Baja, Restaurado |
| "fecha_baja inválida" | Formato incorrecto | Usa formato YYYY-MM-DD |

## Configuración del Sistema

### Límites de Importación

- **Máximo de filas**: 10,000 por importación
- **Tamaño de chunk**: 100 filas procesadas por trabajo
- **Tiempo de reintentos**: 24 horas máximo

### Requisitos

Para que la importación funcione correctamente, asegúrate de que:

1. **Cola de trabajos activa**: `php artisan queue:work`
2. **Tablas de migración**: Ejecutadas (imports, exports, failed_import_rows)
3. **Notificaciones de base de datos**: Tabla notifications creada

### Configuración de Cola

Si usas Docker/Sail, asegúrate de tener el worker corriendo:

```bash
./vendor/bin/sail artisan queue:work
```

O en producción:

```bash
php artisan queue:work --queue=default --tries=3
```

## Notificaciones

### Notificación de Inicio

Al iniciar la importación recibirás una notificación de confirmación.

### Notificación de Completado

Cuando la importación termina, recibirás una notificación con:

- ✅ Número de registros importados exitosamente
- ❌ Número de registros que fallaron (si hay)
- 📥 Enlace para descargar el CSV de filas fallidas

## Ejemplo de CSV

```csv
numero_control,numero_inventario,clasificacion_bienes_id,caracteristicas,descripcion,marca,modelo,numero_serie,precio,tipo_mobiliario_id,localizacion_id,proveedor_id,metodo_adquisicion,tiene_folio,numero_folio,estado_mobiliario,tiene_accesorios,descripcion_accesorios,dado_de_baja,fecha_baja,motivo_baja
MOB-2024-001,INV-2023-100,1,"Color negro, material plástico","Silla ejecutiva ergonómica",Herman Miller,Aeron,SN123456,15000.00,1,1,1,Compra directa,1,FOL-2024-001,Nuevo,1,"Mouse y teclado",0,,
MOB-2024-002,,2,"Pantalla 24 pulgadas","Monitor LED",Dell,P2419H,SN789012,8500.50,2,1,1,Compra directa,1,FOL-2024-002,Nuevo,0,,0,,
MOB-2024-003,INV-2023-101,1,"Material metálico","Escritorio ejecutivo",Steelcase,Series 7,SN345678,25000.00,1,2,,Donación,0,,Usado,0,,0,,
```

## Mejores Prácticas

### Antes de Importar

1. ✅ Descarga el ejemplo y úsalo como plantilla
2. ✅ Verifica que los IDs de relaciones existan
3. ✅ Revisa que los números de control sean únicos
4. ✅ Valida el formato de fechas y precios
5. ✅ Asegúrate de que la cola esté corriendo

### Durante la Importación

1. 🔄 No cierres la ventana del navegador hasta que se inicie el proceso
2. 📊 Importa en lotes si tienes muchos registros (menos de 10,000 por archivo)
3. 🕐 Las importaciones grandes pueden tardar varios minutos

### Después de Importar

1. ✅ Revisa la notificación de completado
2. ✅ Descarga el CSV de errores si hay filas fallidas
3. ✅ Corrige los errores y vuelve a importar solo las filas fallidas
4. ✅ Verifica en la tabla que los datos se importaron correctamente

## Solución de Problemas

### La importación no inicia

**Problema**: Al hacer clic en "Importar" no pasa nada  
**Solución**:
- Verifica que el archivo CSV esté en formato correcto
- Asegúrate de mapear todas las columnas requeridas
- Revisa que el tamaño del archivo no exceda el límite del servidor

### No recibo notificaciones

**Problema**: La importación se procesa pero no veo notificaciones  
**Solución**:
- Verifica que la tabla `notifications` exista
- Asegúrate de que las notificaciones de base de datos estén habilitadas en el panel
- Refresca la página y revisa el ícono de notificaciones

### La cola no procesa trabajos

**Problema**: Los trabajos se quedan pendientes  
**Solución**:
```bash
# Detener workers
./vendor/bin/sail artisan queue:restart

# Iniciar worker nuevamente
./vendor/bin/sail artisan queue:work
```

### Filas con errores

**Problema**: Muchas filas fallan al importar  
**Solución**:
1. Descarga el CSV de errores
2. Revisa la columna de error de cada fila
3. Corrige los datos según el error
4. Vuelve a importar solo las filas corregidas

## Clase Importer

La clase de importación se encuentra en:

```
app/Filament/Imports/MobiliarioImporter.php
```

### Personalización

Puedes personalizar el comportamiento modificando:

- `getColumns()`: Define las columnas y sus validaciones
- `resolveRecord()`: Lógica para crear o actualizar registros
- `getOptionsFormComponents()`: Opciones adicionales para el usuario
- `getCompletedNotificationBody()`: Mensaje de notificación de completado

## Referencias

- [Documentación oficial de Filament Import](https://filamentphp.com/docs/5.x/actions/import)
- [Job Batches de Laravel](https://laravel.com/docs/queues#job-batching)
- [Database Notifications](https://filamentphp.com/docs/5.x/notifications/database-notifications)

## Soporte

Si encuentras problemas con la importación:

1. Revisa los logs de Laravel: `storage/logs/laravel.log`
2. Verifica los trabajos fallidos: `php artisan queue:failed`
3. Consulta esta documentación para errores comunes
4. Contacta al administrador del sistema si el problema persiste

---

**Última actualización**: Enero 2026  
**Versión del sistema**: 1.0  
**Versión de Filament**: 5.x
