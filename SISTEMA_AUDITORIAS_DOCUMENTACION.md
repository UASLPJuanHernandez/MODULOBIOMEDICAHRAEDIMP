# SISTEMA DE AUDITORÍAS DE MOBILIARIO

## Descripción General

El Sistema de Auditorías permite al encargado de Activo Fijo realizar verificaciones periódicas del mobiliario en cada ubicación del hospital, documentando la presencia o ausencia de cada equipo, generando vales para equipos faltantes y produciendo reportes oficiales con firmas.

## Características Principales

### 1. Creación de Auditorías
- Selección de ubicación a auditar
- Captura del nombre del responsable del área
- Registro automático del usuario auditor
- Fecha y hora de inicio
- Observaciones generales opcionales

### 2. Carga Automática de Mobiliario
Al crear una auditoría:
- El sistema obtiene automáticamente todo el mobiliario asignado a la ubicación seleccionada
- Excluye equipos dados de baja
- Crea items de verificación para cada mobiliario encontrado
- Calcula estadísticas iniciales

### 3. Ejecución de Auditoría

La página de ejecución ofrece:

#### Panel de Estadísticas en Tiempo Real
- **Total**: Mobiliarios a verificar
- **Presentes**: Equipos localizados (en verde)
- **Ausentes**: Equipos no encontrados (en rojo)
- **Vales**: Vales generados para faltantes (en amarillo)

#### Verificación de Mobiliario
Para cada equipo se muestra:
- **Información completa**: No. Control, No. Inventario, Descripción, Marca, Modelo
- **Estado visual**: Ícono indicando si fue verificado, presente o ausente
- **Campo de comentarios**: Permite agregar observaciones específicas

#### Acciones por Mobiliario
- **Botón "Presente"** (verde): Marca el equipo como localizado
- **Botón "Ausente"** (rojo): Marca el equipo como no encontrado
  - Activa automáticamente la opción de generar vale
- **Botón "Generar Vale"** (amarillo): Crea vale oficial para mobiliario ausente
  - Solo aparece para equipos marcados como ausentes
  - Genera folio único automático
  - Descarga PDF inmediatamente

#### Colores de Fondo
- **Blanco**: Sin verificar
- **Verde claro**: Presente
- **Rojo claro**: Ausente

### 4. Generación de Vales para Ausentes

Cuando un mobiliario no se localiza:

#### Folio Automático
- Formato: `VALE-AUD-YYYY-####`
- Ejemplo: `VALE-AUD-2025-0001`
- Numeración secuencial por año

#### Contenido del Vale PDF
- **Encabezado institucional**
- **Alerta visual** de mobiliario no localizado
- **Información de auditoría**: Fecha, ubicación, auditor, responsable
- **Datos del equipo faltante**: Completos con número de control, inventario, descripción
- **Detalles de verificación**: Fecha, comentarios
- **Acciones a realizar**: Checklist de pasos a seguir
- **Firmas**: Auditor y Responsable del Área

### 5. Completar Auditoría

#### Validaciones
- Verifica que todos los mobiliarios hayan sido revisados
- Impide completar si quedan items sin verificar
- Muestra contador de pendientes

#### Al Completar
- Cambia estado a "completada"
- Registra fecha y hora de finalización
- Actualiza estadísticas finales
- Redirige a vista de detalles

### 6. Reporte Final de Auditoría

El reporte PDF oficial incluye:

#### Información General
- Ubicación auditada
- Responsable del área
- Auditor (nombre completo)
- Fechas de inicio y fin
- Duración total de la auditoría
- Observaciones generales

#### Resumen Estadístico
- Cuadro visual con 4 indicadores:
  - Total verificado
  - Presentes
  - Ausentes
  - Vales generados
- **Porcentaje de cumplimiento**
  - Verde si ≥ 95%
  - Rojo si < 95%

#### Tabla de Mobiliario Presente
- No. Control, No. Inventario
- Descripción completa
- Marca y modelo
- Comentarios de la verificación

#### Tabla de Mobiliario No Localizado
- Mismos datos que presentes
- **Folio de vale** generado
- Comentarios específicos
- Alerta destacada para seguimiento

#### Conclusiones y Recomendaciones
- Si todo está bien: Mensaje de satisfacción
- Si hay ausentes: Lista de acciones recomendadas
  - Búsqueda exhaustiva
  - Verificar últimos movimientos
  - Entrevistar personal
  - Proceso administrativo
  - Actualización de registros

#### Firmas Oficiales
- **Auditor**: Encargado de Activo Fijo con fecha
- **Responsable del Área**: Con ubicación y fecha
- Espacios amplios para firma manuscrita

### 7. Historial de Auditorías

La tabla principal muestra:
- **ID** de auditoría
- **Ubicación** auditada
- **Auditor** que la realizó
- **Responsable** del área
- **Estado**: Badge (En Progreso/Completada)
- **Estadísticas**: Total, Presentes, Ausentes, Vales
- **Fechas**: Inicio y fin

#### Filtros Disponibles
- Por estado (En Progreso / Completada)
- Por ubicación (búsqueda con autocompletado)

#### Acciones por Registro
- **Ejecutar** (solo en progreso): Abre página de verificación
- **Reporte PDF** (solo completadas): Descarga reporte oficial
- **Ver**: Vista de solo lectura
- **Editar** (solo en progreso): Modificar datos generales
- **Eliminar** (solo en progreso): Borrar auditoría

## Flujo de Trabajo Completo

### Paso 1: Crear Nueva Auditoría
```
Admin → Auditorías → Nueva Auditoría
→ Seleccionar ubicación
→ Ingresar nombre del responsable
→ Agregar observaciones (opcional)
→ Guardar
```

### Paso 2: Ejecutar Auditoría
```
Sistema redirige automáticamente a Ejecutar
→ Revisar lista de mobiliarios
→ Para cada equipo:
   - Localizarlo físicamente
   - Marcar como Presente o Ausente
   - Agregar comentarios si es necesario
   - Si está ausente: Generar Vale PDF
→ Repetir hasta verificar todos
```

### Paso 3: Finalizar
```
Botón "Completar Auditoría"
→ Sistema valida que todo esté verificado
→ Cambia estado a Completada
→ Muestra vista de resumen
```

### Paso 4: Generar Reporte
```
Auditorías → Ver registro completado
→ Botón "Reporte PDF"
→ Descarga reporte oficial
→ Imprimir y obtener firmas físicas
→ Archivar según normativa
```

## Archivos Creados

### Migraciones
- `2025_11_27_200046_create_auditorias_table.php`
- `2025_11_27_200056_create_auditoria_items_table.php`

### Modelos
- `app/Models/Auditoria.php`
- `app/Models/AuditoriaItem.php`

### Controlador
- `app/Http/Controllers/AuditoriaController.php`

### Resource de Filament
- `app/Filament/Resources/AuditoriaResource.php`

### Páginas de Filament
- `app/Filament/Resources/AuditoriaResource/Pages/ListAuditorias.php`
- `app/Filament/Resources/AuditoriaResource/Pages/CreateAuditoria.php`
- `app/Filament/Resources/AuditoriaResource/Pages/ViewAuditoria.php`
- `app/Filament/Resources/AuditoriaResource/Pages/EditAuditoria.php`
- `app/Filament/Resources/AuditoriaResource/Pages/EjecutarAuditoria.php`

### Vistas Blade
- `resources/views/filament/pages/ejecutar-auditoria.blade.php` (Vista principal de ejecución)
- `resources/views/pdf/vale-auditoria.blade.php` (PDF vale de mobiliario ausente)
- `resources/views/pdf/reporte-auditoria.blade.php` (PDF reporte final oficial)

### Rutas
```php
// Ejecución
Route::get('/auditoria/{auditoria}/ejecutar')

// Vale PDF
Route::get('/auditoria/{auditoria}/vale/{item}/pdf')

// Reporte PDF
Route::get('/auditoria/{auditoria}/reporte-pdf')
```

## Base de Datos

### Tabla: auditorias
| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | bigint | ID único |
| ubicacion_id | bigint | FK a localizacion |
| usuario_id | bigint | FK a users (auditor) |
| responsable_nombre | string | Nombre del responsable del área |
| fecha_inicio | timestamp | Inicio de auditoría |
| fecha_fin | timestamp | Finalización (nullable) |
| estado | enum | en_progreso, completada |
| observaciones_generales | text | Observaciones opcionales |
| total_mobiliarios | integer | Contador automático |
| mobiliarios_presentes | integer | Contador automático |
| mobiliarios_ausentes | integer | Contador automático |
| vales_generados | integer | Contador automático |

### Tabla: auditoria_items
| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | bigint | ID único |
| auditoria_id | bigint | FK a auditorias |
| mobiliario_id | bigint | FK a mobiliario |
| presente | boolean | true=encontrado, false=ausente |
| comentarios | text | Observaciones del item |
| requiere_vale | boolean | Si necesita vale |
| folio_vale | string | Folio generado (nullable) |
| fecha_verificacion | timestamp | Cuándo se verificó |

## Métodos Importantes del Modelo

### Auditoria
```php
estaCompletada()          // Verifica si estado es completada
estaEnProgreso()          // Verifica si estado es en_progreso
calcularEstadisticas()    // Recalcula contadores
items()                   // Todos los items
itemsPresentes()          // Solo presentes
itemsAusentes()           // Solo ausentes
itemsConVale()            // Items que tienen vale
```

## Notificaciones

El sistema muestra notificaciones en tiempo real:
- ✅ Mobiliario marcado como presente
- ⚠️ Mobiliario marcado como ausente (requiere vale)
- ✅ Comentario guardado
- ✅ Vale generado con folio
- ⚠️ Auditoría incompleta (al intentar completar)
- ✅ Auditoría completada exitosamente

## Permisos y Seguridad

- Solo usuarios autenticados pueden acceder
- Auditorías en progreso: Editables y eliminables
- Auditorías completadas: Solo lectura, no eliminables
- Vales PDF: Requieren autenticación
- Reportes PDF: Solo para auditorías completadas

## Consideraciones Especiales

1. **Vales inmediatos**: Se generan en el momento que se detecta la ausencia
2. **No se puede completar** si quedan items sin verificar
3. **Estadísticas automáticas**: Se recalculan en cada acción
4. **Folios únicos**: Numeración secuencial por año calendario
5. **PDFs profesionales**: Formato oficial del hospital con firmas
6. **Historial permanente**: Las auditorías completadas se conservan

## Uso Recomendado

### Frecuencia
- Auditorías trimestrales por ubicación
- Auditorías extraordinarias cuando hay cambios de responsable
- Auditorías especiales ante reportes de faltantes

### Mejores Prácticas
1. Coordinar previamente con el responsable del área
2. Llevar laptop o tablet para registro en tiempo real
3. Generar vales inmediatamente al detectar ausencias
4. Agregar comentarios detallados para cualquier observación
5. Obtener firmas físicas del reporte final el mismo día
6. Archivar reportes según lineamientos institucionales

## Beneficios del Sistema

✅ **Trazabilidad completa** de verificaciones
✅ **Documentación oficial** con firmas
✅ **Vales automáticos** para faltantes
✅ **Reportes profesionales** listos para autoridades
✅ **Historial permanente** de todas las auditorías
✅ **Estadísticas en tiempo real** durante ejecución
✅ **Proceso estandarizado** y repetible
✅ **Cumplimiento normativo** documentado

## Próximos Pasos (Mejoras Futuras)

- [ ] Dashboard con calendario de auditorías programadas
- [ ] Widget de auditorías pendientes
- [ ] Exportación a Excel del reporte
- [ ] Notificaciones automáticas de auditorías vencidas
- [ ] Comparación entre auditorías (evolución temporal)
- [ ] Gráficas de tendencias por ubicación
