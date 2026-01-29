# 📥 Importación de Mobiliario - Documentación

## Descripción General

El sistema de importación de mobiliario permite cargar múltiples registros desde un archivo CSV, facilitando la carga masiva de datos al sistema. Esta funcionalidad utiliza el sistema de importación de Filament v5 con procesamiento en segundo plano mediante colas.

## Características Principales

✅ **Importación desde CSV** - Sube archivos CSV con datos de mobiliario  
✅ **Validación en tiempo real** - Valida cada fila antes de importar  
✅ **Procesamiento en segundo plano** - Usa colas de Laravel para procesar grandes volúmenes  
✅ **Mapeo de columnas** - Interfaz visual para mapear columnas del CSV  
✅ **Auto-detección de columnas** - El sistema detecta automáticamente columnas del sistema anterior  
✅ **Creación automática de localizaciones** - Crea nuevas ubicaciones si no existen  
✅ **Reporte de errores** - Descarga CSV con filas que fallaron y sus errores  
✅ **Notificaciones** - Notifica cuando la importación se completa

## Ubicación

La acción de importación está disponible en:

1. **Encabezado de la página de lista de Mobiliario** - Botón "Importar desde Sistema Anterior" (naranja)
2. **Acciones de la tabla** - Sección de acciones masivas

## Estructura del CSV (Sistema Anterior)

El importador acepta el formato de exportación del sistema anterior. Las columnas se detectan automáticamente por nombre.

### Columnas del Sistema Anterior Soportadas

| Columna CSV | Campo en Sistema | Descripción |
|-------------|------------------|-------------|
| `Clave del Bien` | numero_inventario | Número de inventario del bien |
| `Nombre del Bien` | descripcion | Descripción del mobiliario |
| `Grupo` | clasificacion_bienes_id | Grupo de clasificación |
| `Subgrupo` | clasificacion_bienes_id | Subgrupo de clasificación |
| `Clase` | clasificacion_bienes_id | Clase de clasificación |
| `Marca` | marca | Marca del producto |
| `Modelo` | modelo | Modelo del producto |
| `Color` | caracteristicas | Se incluye en características |
| `N. de Serie` | numero_serie | Número de serie |
| `No Factura` | numero_folio | Número de folio/factura |
| `Proveedor` | proveedor_id | Proveedor (se crea si no existe) |
| `F. Adquisición` | metodo_adquisicion | Método de adquisición |
| `F. de Factura` | fecha_factura | Fecha de factura |
| `F. de Baja` | fecha_baja | Fecha de baja (si tiene, estado = Baja) |
| `Valor` | precio | Precio del bien |
| `F. Registro` | created_at | Fecha de registro original |
| `Ubicacion` | localizacion.ubicacion | Ubicación física |
| `Caracteristicas` | caracteristicas | Características del equipo |
| `Procedencia` | caracteristicas | Se incluye en características |
| `Dirección` | localizacion.direccion | Dirección administrativa |
| `División` | localizacion.division | División organizacional |
| `Departamento` | localizacion.sub_area | Departamento/Sub área |
| `Responsable` | responsable_actual | Responsable del bien |
| `Clave Emp.` | matricula_responsable | Matrícula del responsable |
| `Puesto` | puesto_responsable | Puesto del responsable |

### Campos Generados Automáticamente

| Campo | Descripción |
|-------|-------------|
| `numero_control` | Se genera automáticamente con formato IMP-{timestamp}-{random} |
| `estado_mobiliario` | "Usado" por defecto, "Baja" si tiene fecha de baja |
| `clasificacion_bienes_id` | Se busca por grupo/subgrupo/clase o usa el primero disponible |
| `tipo_mobiliario_id` | Usa el primer tipo disponible |
| `localizacion_id` | Se busca por dirección/división/departamento o se crea nueva |

## Proceso de Importación

### 1. Preparar el Archivo CSV

1. **Exporta desde el sistema anterior** el archivo CSV con los bienes
2. **Limpia el archivo** si es necesario:
   - Elimina filas de encabezados secundarios (como la fila con "F. Cal Dep.")
   - Asegúrate de que el encoding sea UTF-8
   - Elimina caracteres especiales problemáticos

**Consejos para preparar el CSV:**

- El separador debe ser coma (,)
- Los valores con comas deben estar entre comillas dobles
- El formato de fecha debe ser DD/MM/YYYY (ej: 31/12/2024)
- El precio puede tener formato con comas (1,393.00) que se convierte automáticamente

### 2. Iniciar la Importación

1. Navega a **Mobiliario** > **Lista de Mobiliario**
2. Haz clic en el botón **"Importar desde Sistema Anterior"** (naranja)
3. Selecciona tu archivo CSV
4. Haz clic en **"Continuar"**

### 3. Mapear Columnas

El sistema detectará automáticamente las columnas del sistema anterior. Verifica que:

1. Las columnas marcadas como **requeridas** estén mapeadas (estrella roja)
2. Las columnas opcionales estén mapeadas si los datos existen en el CSV
3. Puedes ajustar el mapeo manualmente si es necesario

### 4. Procesar Importación

1. Haz clic en **"Importar"**
2. La importación se procesa en segundo plano
3. Verás una notificación cuando se complete
4. Si hay errores, podrás descargar un CSV con las filas que fallaron

## Ejemplo de CSV del Sistema Anterior

```csv
Clave del Bien,Nombre del Bien,Grupo,Subgrupo,Clase,Marca,Modelo,Color,N. de Serie,No Factura,Proveedor,R. Social,Cod. Salud,F. Adquisición,F. de Factura,F. de  Baja,Valor,F. Registro,...,Caracteristicas,Procedencia,Dirección,División,Departamento,Responsable,Clave Emp.,Puesto,...
1,ESCRITORIO,5,1,1,,,,,,0,,,COMPRA,31/12/2014,,"1,393.00",31/12/2014,...,CON 6 CAJONES-COLOR CAF,HC,DIRECCION ADMINISTRATIVA,DIVISION DE RECURSOS MATERIALES,ACTIVO FIJO,L.D. ANA ROSA JUAREZ CONTRERAS,,,...
```

## Campos que se Importan

Según los requerimientos, los campos que se importan son:

| Campo Requerido | Columna CSV | Estado |
|-----------------|-------------|--------|
| Núm. Inventario | Clave del Bien | ✅ |
| Número de serie | N. de Serie | ✅ |
| Dirección | Dirección | ✅ |
| Sub área | Departamento | ✅ |
| Estado de ubicación | Ubicacion | ✅ |
| Proveedor | Proveedor | ✅ (se crea si no existe) |
| Estado | Basado en F. de Baja | ✅ |
| Tipo de vale | No Factura | ✅ |
| Responsable de vale | Responsable | ✅ |
| Fecha de vale | F. de Factura | ✅ |
| Responsable actual | Responsable | ✅ |
| Matrícula del responsable | Clave Emp. | ✅ |
| Puesto del responsable | Puesto | ✅ |
| Registrado (alta) | F. Registro | ✅ |
| Última modificación | Auto (created_at) | ✅ |
| Quién lo modificó | Auto (created_by=1) | ✅ |

## Creación Automática de Registros

### Localizaciones

El importador **crea automáticamente** localizaciones si no encuentra una existente con la combinación de:
- Dirección
- División
- Departamento (Sub área)

Esto permite importar sin necesidad de precargar todas las ubicaciones.

### Proveedores

El importador **crea automáticamente** proveedores si no encuentra uno existente con el nombre proporcionado en la columna "Proveedor".

## Validaciones

### Validaciones de Formato

- **precio**: Se convierte automáticamente (1,393.00 → 1393.00)
- **estado_mobiliario**: Solo valores permitidos: Nuevo, Usado, Baja, Restaurado
- **fecha_baja**: Si tiene valor, el estado cambia a "Baja" automáticamente
- **fechas**: Formato DD/MM/YYYY (del sistema anterior)

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

La clase de importación del sistema anterior se encuentra en:

```
app/Filament/Imports/MobiliarioLegacyImporter.php
```

La clase de importación estándar (formato nuevo) se encuentra en:

```
app/Filament/Imports/MobiliarioImporter.php
```

### Comando de Prueba

Para probar la importación desde la línea de comandos:

```bash
./vendor/bin/sail artisan test:importacion-mobiliario
```

Este comando:
- Leerá 5 registros de prueba del CSV
- Mostrará el progreso en consola
- Creará registros de mobiliario
- Mostrará errores si los hay

### Personalización

Puedes personalizar el comportamiento modificando:

- `getColumns()`: Define las columnas y sus mappings
- `resolveRecord()`: Lógica para crear o actualizar registros
- `limpiarTexto()`: Función para limpiar encoding del sistema anterior
- `buscarLocalizacion()`: Lógica para buscar/crear localizaciones

## Preparación del CSV (Sistema Anterior)

Si el CSV exportado del sistema anterior tiene problemas de encoding:

```bash
# Ver los primeros bytes del archivo (verificar BOM)
head -c 10 archivo.csv | xxd

# Convertir encoding Windows a UTF-8
iconv -f WINDOWS-1252 -t UTF-8 archivo.csv > archivo_limpio.csv

# Eliminar BOM si existe
sed -i '1s/^\xef\xbb\xbf//' archivo_limpio.csv
```

## Referencias

- [Documentación oficial de Filament Import](https://filamentphp.com/docs/5.x/actions/import)
- [Job Batches de Laravel](https://laravel.com/docs/queues#job-batching)
- [Database Notifications](https://filamentphp.com/docs/5.x/notifications/database-notifications)

## Soporte

Si encuentras problemas con la importación:

1. Revisa los logs de Laravel: `storage/logs/laravel.log`
2. Verifica los trabajos fallidos: `php artisan queue:failed`
3. Ejecuta el comando de prueba: `sail artisan test:importacion-mobiliario`
4. Consulta esta documentación para errores comunes
5. Contacta al administrador del sistema si el problema persiste

---

**Última actualización**: Junio 2025  
**Versión del sistema**: 1.0  
**Versión de Filament**: 5.x
