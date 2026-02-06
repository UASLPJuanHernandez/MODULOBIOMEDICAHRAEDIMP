#!/bin/bash

echo "======================================"
echo "SCRIPT DE VERIFICACIÓN Y CORRECCIÓN"
echo "======================================"
echo ""

# Colores
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# 1. Verificar estructura actual
echo "1. Verificando estructura de la base de datos..."
php test_verificacion_sistema.php

echo ""
echo "======================================"
echo ""

# 2. Preguntar si desea aplicar la corrección
echo -e "${YELLOW}¿Desea crear y aplicar la migración para corregir el problema? (s/n)${NC}"
read -r respuesta

if [[ $respuesta == "s" || $respuesta == "S" ]]; then
    echo ""
    echo "2. Creando migración..."
    php artisan make:migration make_movimiento_id_nullable_in_vales_table --table=vales
    
    echo ""
    echo -e "${YELLOW}Se ha creado la migración. Ahora debe editarla.${NC}"
    echo ""
    echo "Archivo creado en: database/migrations/*_make_movimiento_id_nullable_in_vales_table.php"
    echo ""
    echo "Agregue este código en el método up():"
    echo ""
    echo -e "${GREEN}"
    cat << 'EOF'
    Schema::table('vales', function (Blueprint $table) {
        $table->unsignedBigInteger('movimiento_id')->nullable()->change();
    });
EOF
    echo -e "${NC}"
    echo ""
    echo "3. Después de editar, ejecute:"
    echo "   php artisan migrate"
    echo ""
    echo "4. Finalmente, ejecute las pruebas:"
    echo "   php test_funcionalidad_vales.php"
    
else
    echo ""
    echo -e "${YELLOW}Operación cancelada.${NC}"
    echo ""
    echo "Para corregir manualmente:"
    echo "1. php artisan make:migration make_movimiento_id_nullable_in_vales_table"
    echo "2. Edite la migración"
    echo "3. php artisan migrate"
fi

echo ""
echo "======================================"
