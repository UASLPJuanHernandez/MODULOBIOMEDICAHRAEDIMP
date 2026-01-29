#!/bin/bash

# Script para iniciar el scheduler de Laravel en Docker/Sail
# Este script mantiene el scheduler ejecutándose en segundo plano

echo "🕐 Iniciando Laravel Scheduler..."

# Ejecutar el scheduler de Laravel
php artisan schedule:work
