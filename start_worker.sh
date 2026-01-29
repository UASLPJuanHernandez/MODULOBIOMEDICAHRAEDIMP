#!/bin/bash
cd /home/nformatica/Activo-Fijo-HRAE-DIMP
./vendor/bin/sail artisan queue:work --tries=3 --timeout=300
