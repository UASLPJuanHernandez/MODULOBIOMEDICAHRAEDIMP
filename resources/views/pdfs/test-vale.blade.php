<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Test Vale</title>
</head>
<body>
    <h1>Vale de Prueba</h1>
    <p>Fecha: {{ $fecha }}</p>
    <p>ID del Vale: {{ $vale->id ?? 'N/A' }}</p>
    <p>Tipo: {{ $vale->tipo_vale ?? 'N/A' }}</p>
</body>
</html>
