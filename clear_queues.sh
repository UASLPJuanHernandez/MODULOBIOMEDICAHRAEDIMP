#!/bin/bash

# Script para limpiar las colas y reiniciar el worker

echo "🧹 Limpiando colas y trabajos fallidos..."

# Limpiar todas las colas
./vendor/bin/sail artisan queue:flush

# Limpiar trabajos fallidos
./vendor/bin/sail artisan queue:clear

# Reiniciar horizon si está corriendo (opcional)
# ./vendor/bin/sail artisan horizon:terminate

echo "✅ Colas limpiadas exitosamente"
echo ""
echo "📊 Estado actual de las colas:"
./vendor/bin/sail artisan queue:monitor

echo ""
echo "🔄 Para procesar los trabajos, ejecuta:"
echo "   ./vendor/bin/sail artisan queue:work --tries=3 --timeout=300"
