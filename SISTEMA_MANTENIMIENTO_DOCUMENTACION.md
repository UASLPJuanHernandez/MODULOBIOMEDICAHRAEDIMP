# Sistema de Mantenimiento - Manual de Uso

## 📋 Funcionalidades Implementadas

### ✅ **1. Botón "Mantenimiento" en el Inventario**
- **Ubicación**: Vista principal del inventario (tabla de mobiliario)
- **Función**: Permite solicitar mantenimiento para cualquier equipo
- **Acceso**: Todos los usuarios autenticados pueden crear solicitudes

### ✅ **2. Formulario de Solicitud de Mantenimiento**
Cuando haces clic en "Mantenimiento", se abre un formulario modal con:

#### **Campos Automáticos:**
- **Fecha y hora actual del sistema**: Se toma automáticamente
- **Datos del equipo**: Número de control, descripción, ubicación, responsable

#### **Campos Requeridos:**
- **Fecha y hora programada**: Cuándo se realizará el mantenimiento
- **Motivo**: Descripción detallada del problema o necesidad de mantenimiento
- **Tipo de mantenimiento**: 
  - `Mantenimiento Interno`: Se realizará por personal del hospital
  - `Enviar con Proveedor Externo`: Se enviará a proveedor externo
- **Nombre del proveedor** (solo si seleccionas "Proveedor Externo")

### ✅ **3. Vista "Órdenes de Servicio"**
- **Nueva sección en el menú**: "Órdenes de Servicio" 
- **Funcionalidad**: Muestra todas las solicitudes de mantenimiento
- **Estados disponibles**: Pendiente, Aceptado, Completado, Rechazado

### ✅ **4. Gestión de Estados**

#### **Para Personal de Mantenimiento:**
- **Aceptar solicitud**: Genera automáticamente un folio de vale
- **Completar mantenimiento**: Marca como terminado con observaciones
- **Rechazar solicitud**: Con motivo del rechazo

#### **Estados y Flujo:**
1. **Pendiente**: Solicitud creada, esperando aceptación
2. **Aceptado**: Personal de mantenimiento acepta y se genera vale
3. **Completado**: Mantenimiento terminado
4. **Rechazado**: Solicitud rechazada con motivo

### ✅ **5. Generación Automática de Vales**
- **Cuándo**: Al aceptar una solicitud de mantenimiento
- **Folio automático**: Formato `MTO-AAAAMMDD-XXXX`
- **Contenido del vale**:
  - Información completa del equipo
  - Ubicación actual y responsable
  - Fechas (programada, aceptación)
  - Tipo de mantenimiento y motivo
  - Personal involucrado
  - Espacios para firmas

### ✅ **6. Impresión de Vales PDF**
- **Botón**: "Imprimir Vale" (disponible solo para mantenimientos aceptados)
- **Formato**: PDF profesional con diseño hospitalario
- **Descarga automática**: Se descarga directamente al hacer clic

### ✅ **7. Sistema de Notificaciones**
- **Notificación automática**: Los administradores reciben alertas cuando:
  - Se crea una nueva solicitud de mantenimiento
  - Se acepta una solicitud (con número de vale generado)
- **Notificaciones en tiempo real**: A través del widget de notificaciones

### ✅ **8. Control de Permisos**
#### **Roles creados:**
- **Personal de Mantenimiento**: Puede aceptar, completar y rechazar solicitudes
- **Administrador**: Control total del sistema

#### **Permisos específicos:**
- `view_mantenimientos`: Ver órdenes de servicio
- `aceptar_mantenimientos`: Aceptar solicitudes pendientes
- `completar_mantenimientos`: Marcar como completado
- `rechazar_mantenimientos`: Rechazar solicitudes
- `generar_vales_mantenimiento`: Generar e imprimir vales

---

## 🚀 **Cómo Usar el Sistema**

### **Paso 1: Solicitar Mantenimiento**
1. Ve al **Inventario** 
2. Busca el equipo que necesita mantenimiento
3. Haz clic en **"Mantenimiento"** en las acciones
4. Completa el formulario:
   - Selecciona fecha y hora programada
   - Describe el motivo detalladamente
   - Elige tipo de mantenimiento
   - Si es proveedor externo, indica el nombre
5. Haz clic en **"Crear Solicitud"**

### **Paso 2: Ver Órdenes de Servicio**
1. Ve al menú **"Órdenes de Servicio"**
2. Verás todas las solicitudes con sus estados
3. Usa los filtros para buscar por estado, tipo o asignado

### **Paso 3: Aceptar Solicitud (Personal de Mantenimiento)**
1. En "Órdenes de Servicio", busca solicitudes **Pendientes**
2. Haz clic en **"Aceptar"** 
3. Confirma la acción
4. Se generará automáticamente el vale con folio

### **Paso 4: Imprimir Vale**
1. Para solicitudes **Aceptadas**, aparecerá el botón **"Imprimir Vale"**
2. Haz clic para descargar el PDF
3. El vale incluye toda la información necesaria y espacios para firmas

### **Paso 5: Completar o Rechazar**
- **Completar**: Agrega observaciones del trabajo realizado
- **Rechazar**: Solo para solicitudes pendientes, requiere motivo

---

## 🔧 **Información Técnica**

### **Tablas de Base de Datos:**
- **mantenimientos**: Almacena todas las solicitudes
- **Campos principales**: mobiliario_id, fecha_programada, motivo, tipo_mantenimiento, estado, folios, fechas

### **Archivos Principales:**
- **Modelo**: `app/Models/Mantenimiento.php`
- **Resource**: `app/Filament/Resources/MantenimientoResource.php`
- **Controlador**: `app/Http/Controllers/MantenimientoController.php`
- **Vista PDF**: `resources/views/pdf/vale-mantenimiento.blade.php`
- **Migración**: `database/migrations/2025_11_20_161516_create_mantenimientos_table.php`

### **Rutas:**
- **Vista órdenes**: `/admin/mantenimientos` (automática de Filament)
- **Imprimir vale**: `/mantenimiento/{id}/vale-pdf`

---

## ✨ **Características Destacadas**

- ✅ **Integración completa** con el sistema existente
- ✅ **Flujo de trabajo intuitivo** y fácil de usar
- ✅ **Notificaciones automáticas** para administradores
- ✅ **Vales profesionales** con formato hospitalario
- ✅ **Control de permisos** por roles
- ✅ **Trazabilidad completa** de todo el proceso
- ✅ **Información contextual** (ubicación, responsable automático)
- ✅ **Estados claros** del proceso de mantenimiento
- ✅ **Generación automática de folios** únicos

¡El sistema está completamente funcional y listo para usar! 🎉