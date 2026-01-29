#!/bin/bash

# Script para gestionar el worker de cola

cd /home/nformatica/Activo-Fijo-HRAE-DIMP

case "$1" in
    start)
        echo "🚀 Iniciando worker de cola..."
        
        # Verificar si ya está corriendo
        if pgrep -f "queue:work" > /dev/null; then
            echo "⚠️  El worker ya está corriendo"
            echo "   Usa: $0 status para ver detalles"
            exit 1
        fi
        
        # Iniciar el worker
        nohup ./worker_daemon.sh > worker.log 2>&1 &
        sleep 2
        
        if pgrep -f "queue:work" > /dev/null; then
            echo "✅ Worker iniciado correctamente"
            echo "   Log: tail -f worker.log"
        else
            echo "❌ Error al iniciar el worker"
            exit 1
        fi
        ;;
        
    stop)
        echo "🛑 Deteniendo worker de cola..."
        pkill -f "queue:work"
        sleep 2
        
        if pgrep -f "queue:work" > /dev/null; then
            echo "⚠️  El worker aún está corriendo, forzando..."
            pkill -9 -f "queue:work"
        fi
        
        echo "✅ Worker detenido"
        ;;
        
    restart)
        echo "🔄 Reiniciando worker de cola..."
        $0 stop
        sleep 2
        $0 start
        ;;
        
    status)
        echo "📊 Estado del worker:"
        echo ""
        
        if pgrep -f "queue:work" > /dev/null; then
            echo "✅ Worker está CORRIENDO"
            echo ""
            echo "Procesos activos:"
            ps aux | grep "queue:work" | grep -v grep | head -2
            echo ""
            echo "Últimas líneas del log:"
            tail -5 worker.log 2>/dev/null || echo "   (No hay log disponible)"
        else
            echo "❌ Worker NO está corriendo"
            echo ""
            echo "Para iniciarlo: $0 start"
        fi
        ;;
        
    log)
        echo "📋 Últimas 20 líneas del log:"
        echo ""
        tail -20 worker.log 2>/dev/null || echo "❌ No hay log disponible"
        ;;
        
    *)
        echo "🔧 Gestor del Worker de Cola"
        echo ""
        echo "Uso: $0 {start|stop|restart|status|log}"
        echo ""
        echo "Comandos:"
        echo "  start   - Inicia el worker"
        echo "  stop    - Detiene el worker"
        echo "  restart - Reinicia el worker"
        echo "  status  - Muestra el estado actual"
        echo "  log     - Muestra el log del worker"
        echo ""
        exit 1
        ;;
esac
