#!/bin/bash

# Script para mantener el worker de cola corriendo
# Este script debe ejecutarse en segundo plano

cd /home/nformatica/Activo-Fijo-HRAE-DIMP

echo "Iniciando worker de cola..."

# Función para limpiar al salir
cleanup() {
    echo "Deteniendo worker..."
    pkill -f "queue:work"
    exit 0
}

trap cleanup SIGINT SIGTERM

# Loop infinito para reiniciar el worker si se cae
while true; do
    echo "[$(date)] Worker iniciado"
    ./vendor/bin/sail artisan queue:work --tries=3 --timeout=300 --sleep=3 --max-jobs=1000
    
    # Si el worker se detiene, esperar 5 segundos antes de reiniciar
    echo "[$(date)] Worker detenido. Reiniciando en 5 segundos..."
    sleep 5
done
