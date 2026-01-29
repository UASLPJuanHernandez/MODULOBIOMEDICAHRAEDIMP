# Sistema de Respaldos (Laravel Backup)

## Descripción General

Este sistema utiliza el paquete **spatie/laravel-backup** para gestionar respaldos automáticos de la aplicación y base de datos. Los respaldos se almacenan localmente en el contenedor Docker y pueden ser programados para ejecutarse automáticamente.

## Características

- ✅ Respaldo completo de archivos de la aplicación
- ✅ Respaldo completo de la base de datos MySQL
- ✅ Compresión Gzip de dumps de base de datos
- ✅ Almacenamiento en disco dedicado
- ✅ Notificaciones por email
- ✅ Limpieza automática de respaldos antiguos
- ✅ Monitoreo de salud de respaldos
- ✅ Compatible con Docker/Laravel Sail

## Instalación

El paquete ya está instalado. Se instaló con:

```bash
./vendor/bin/sail composer require spatie/laravel-backup
./vendor/bin/sail artisan vendor:publish --provider="Spatie\Backup\BackupServiceProvider"
```

## Configuración

### 1. Disco de Almacenamiento

Se creó un disco dedicado en `config/filesystems.php`:

```php
'backups' => [
    'driver' => 'local',
    'root' => storage_path('app/backups'),
    'throw' => false,
    'report' => false,
],
```

### 2. Configuración de Respaldos

Archivo: `config/backup.php`

**Directorios incluidos:**
- Todo el proyecto base (`base_path()`)

**Directorios excluidos:**
- `vendor/`
- `node_modules/`
- `storage/framework/cache`
- `storage/framework/sessions`
- `storage/framework/views`
- `storage/logs/`
- `.git/`
- `docker/`

**Base de datos:**
- Se respalda automáticamente la conexión configurada en `DB_CONNECTION`
- Compresión Gzip habilitada para reducir espacio

### 3. Variables de Entorno

Agregar al archivo `.env`:

```env
# Configuración de Respaldos
BACKUP_MAIL_TO=admin@activo-fijo.local
BACKUP_ARCHIVE_PASSWORD=null
```

**Opcional:** Para proteger los respaldos con contraseña:
```env
BACKUP_ARCHIVE_PASSWORD=tu_contraseña_segura
```

## Comandos Disponibles

### Crear un Respaldo

```bash
./vendor/bin/sail artisan backup:run
```

**Opciones:**
```bash
# Solo base de datos
./vendor/bin/sail artisan backup:run --only-db

# Solo archivos
./vendor/bin/sail artisan backup:run --only-files

# Con notificaciones deshabilitadas
./vendor/bin/sail artisan backup:run --disable-notifications
```

### Limpiar Respaldos Antiguos

```bash
./vendor/bin/sail artisan backup:clean
```

Este comando elimina respaldos antiguos según la estrategia configurada:
- Mantiene todos los respaldos de los últimos 7 días
- Mantiene respaldos diarios de los últimos 16 días
- Mantiene respaldos semanales de las últimas 8 semanas
- Mantiene respaldos mensuales de los últimos 4 meses
- Mantiene respaldos anuales de los últimos 2 años
- Elimina respaldos cuando se exceden 5000 MB

### Monitorear Estado de Respaldos

```bash
./vendor/bin/sail artisan backup:monitor
```

Verifica:
- Antigüedad máxima de respaldos (1 día)
- Espacio utilizado (máximo 5000 MB)

### Listar Respaldos

```bash
./vendor/bin/sail artisan backup:list
```

## Programación de Respaldos Automáticos

### 1. Editar el Scheduler

Agregar al archivo `app/Console/Kernel.php` en el método `schedule()`:

```php
protected function schedule(Schedule $schedule): void
{
    // Respaldo diario a las 2:00 AM
    $schedule->command('backup:run --only-db')
        ->daily()
        ->at('02:00');
    
    // Respaldo completo semanal (domingos a las 3:00 AM)
    $schedule->command('backup:run')
        ->weekly()
        ->sundays()
        ->at('03:00');
    
    // Limpieza de respaldos antiguos (diario a las 4:00 AM)
    $schedule->command('backup:clean')
        ->daily()
        ->at('04:00');
    
    // Monitoreo de respaldos (diario a las 5:00 AM)
    $schedule->command('backup:monitor')
        ->daily()
        ->at('05:00');
}
```

### 2. Activar el Scheduler

El scheduler de Laravel debe estar ejecutándose para que los respaldos automáticos funcionen.

**Opción 1: Ejecutar en una terminal separada (Desarrollo)**

```bash
./vendor/bin/sail artisan schedule:work
```

O usar el script incluido:

```bash
./vendor/bin/sail shell
./start-scheduler.sh
```

**Opción 2: Ejecutar en segundo plano (Producción)**

```bash
./vendor/bin/sail shell
nohup php artisan schedule:work > /dev/null 2>&1 &
```

**Opción 3: Modificar docker-compose.yml (Recomendado para producción)**

Editar el archivo `docker-compose.yml` y agregar un servicio para el scheduler:

```yaml
services:
  laravel.test:
    # ... configuración existente ...
  
  scheduler:
    build:
      context: ./vendor/laravel/sail/runtimes/8.3
      dockerfile: Dockerfile
      args:
        WWWGROUP: '${WWWGROUP}'
    image: sail-8.3/app
    extra_hosts:
      - 'host.docker.internal:host-gateway'
    environment:
      WWWUSER: '${WWWUSER}'
      LARAVEL_SAIL: 1
      XDEBUG_MODE: '${SAIL_XDEBUG_MODE:-off}'
      XDEBUG_CONFIG: '${SAIL_XDEBUG_CONFIG:-client_host=host.docker.internal}'
      IGNITION_LOCAL_SITES_PATH: '${PWD}'
    volumes:
      - '.:/var/www/html'
    networks:
      - sail
    depends_on:
      - mysql
    command: php artisan schedule:work
```

Luego reiniciar los contenedores:

```bash
./vendor/bin/sail down
./vendor/bin/sail up -d
```

**Verificar que el scheduler está ejecutándose:**

```bash
# Ver tareas programadas
./vendor/bin/sail artisan schedule:list

# Ver logs
./vendor/bin/sail artisan log:show
```

## Ubicación de los Respaldos

Los respaldos se almacenan en:

```
storage/app/backups/
└── [APP_NAME]/
    ├── 2026-01-20-14-30-45.zip
    ├── 2026-01-21-14-30-45.zip
    └── ...
```

## Acceder a los Respaldos desde el Host

Desde Windows (fuera del contenedor):

```powershell
# Ver respaldos
ls \\wsl.localhost\Ubuntu\home\nformatica\Activo-Fijo-HRAE-DIMP\storage\app\backups

# Copiar respaldo al escritorio
cp "\\wsl.localhost\Ubuntu\home\nformatica\Activo-Fijo-HRAE-DIMP\storage\app\backups\Laravel\*.zip" ~\Desktop\
```

Desde WSL/Linux:

```bash
# Ver respaldos
ls -lh /home/nformatica/Activo-Fijo-HRAE-DIMP/storage/app/backups/

# Copiar a otra ubicación
cp /home/nformatica/Activo-Fijo-HRAE-DIMP/storage/app/backups/Laravel/*.zip /mnt/c/respaldos/
```

## Restauración de Respaldos

### Restaurar Base de Datos

1. Extraer el archivo ZIP del respaldo
2. Localizar el archivo `.sql.gz`
3. Descomprimir y restaurar:

```bash
# Dentro del contenedor
./vendor/bin/sail shell

# Descomprimir
gunzip db-dumps/mysql-inventario_hospitalario.sql.gz

# Restaurar
mysql -u sail -ppassword inventario_hospitalario < db-dumps/mysql-inventario_hospitalario.sql
```

### Restaurar Archivos

1. Extraer el archivo ZIP del respaldo
2. Copiar los archivos necesarios a su ubicación original
3. Ajustar permisos si es necesario:

```bash
./vendor/bin/sail shell
chmod -R 775 storage/
chown -R sail:sail storage/
```

## Notificaciones

Las notificaciones se envían por email cuando:
- ✅ Un respaldo se completa exitosamente
- ❌ Un respaldo falla
- ⚠️ Se detecta un respaldo no saludable
- 🧹 La limpieza se completa exitosamente
- ❌ La limpieza falla

Para configurar las notificaciones, asegúrate de configurar correctamente el email en `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=tu_usuario
MAIL_PASSWORD=tu_contraseña
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@activo-fijo.local"
MAIL_FROM_NAME="${APP_NAME}"

BACKUP_MAIL_TO=admin@activo-fijo.local
```

## Monitoreo y Mantenimiento

### Verificar Espacio en Disco

```bash
./vendor/bin/sail shell
du -sh storage/app/backups/
```

### Ver Logs de Respaldo

```bash
./vendor/bin/sail artisan log:show
# o
tail -f storage/logs/laravel.log
```

### Respaldo Manual de Emergencia

```bash
# Base de datos directamente
./vendor/bin/sail mysql -e "mysqldump inventario_hospitalario > /tmp/emergency_backup.sql"

# Archivos importantes
./vendor/bin/sail shell
tar -czf /tmp/storage_backup.tar.gz storage/app/public storage/app/private
```

## Mejores Prácticas

1. **Programar respaldos en horas de baja actividad** (madrugada)
2. **Mantener respaldos fuera del servidor** (copiar a almacenamiento externo)
3. **Probar restauraciones periódicamente**
4. **Monitorear el espacio en disco**
5. **Revisar logs de respaldos regularmente**
6. **Usar contraseñas para respaldos en producción**
7. **Documentar el proceso de restauración**

## Respaldo Externo (Opcional)

Para respaldos en la nube (AWS S3, Google Cloud, etc.), configurar en `config/backup.php`:

```php
'destination' => [
    'disks' => [
        'backups',  // Local
        's3',       // AWS S3
    ],
],
```

Y configurar las credenciales en `.env`:

```env
AWS_ACCESS_KEY_ID=your-key
AWS_SECRET_ACCESS_KEY=your-secret
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your-bucket-name
```

## Solución de Problemas

### Error: "mysqldump not found"

Asegurarse de que mysql-client esté instalado en el contenedor:

```bash
./vendor/bin/sail shell
apt-get update && apt-get install -y mysql-client
```

### Error: Permisos insuficientes

```bash
./vendor/bin/sail shell
chmod -R 775 storage/app/backups
chown -R sail:sail storage/app/backups
```

### Respaldos muy grandes

Habilitar compresión en `config/backup.php`:

```php
'database_dump_compressor' => \Spatie\DbDumper\Compressors\GzipCompressor::class,
'compression_method' => ZipArchive::CM_DEFLATE,
'compression_level' => 9,
```

## Referencias

- [Documentación Oficial Spatie Laravel Backup](https://spatie.be/docs/laravel-backup)
- [Repositorio GitHub](https://github.com/spatie/laravel-backup)
- [Laravel Task Scheduling](https://laravel.com/docs/scheduling)
