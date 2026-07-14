{{-- resources/views/admin/reportes/top-productos-pdf.blade.php --}}
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
            color: #2B2B2B;
        }

        h1 {
            font-size: 18px;
            color: #D81B60;
            margin-bottom: 0;
        }

        p.subtitle {
            color: #666;
            margin-top: 4px;
            font-size: 11px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 16px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 6px 8px;
            text-align: left;
        }

        th {
            background: #F8F5F2;
            color: #D81B60;
        }

        tfoot td {
            font-weight: bold;
            background: #FAFAFA;
        }

        .text-right {
            text-align: right;
        }
    </style>
</head>

<body>
    <h1>Top 20 Prendas Más Vendidas</h1>
    <p class="subtitle">Generado el {{ $fecha }}</p>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Prenda</th>
                <th>Región</th>
                <th>Artesano</th>
                <th class="text-right">Precio Unitario</th>
                <th class="text-right">Cantidad Vendida</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($productos as $i => $producto)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $producto['nombre'] }}</td>
                <td>{{ $producto['region'] }}</td>
                <td>{{ $producto['artesano'] }}</td>
                <td class="text-right">${{ number_format($producto['precio_unitario'], 2) }}</td>
                <td class="text-right">{{ $producto['cantidad_vendida'] }}</td>
                <td class="text-right">${{ number_format($producto['total_vendido'], 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6" class="text-right">Total General</td>
                <td class="text-right">${{ number_format($totalGeneral, 2) }}</td>
            </tr>
        </tfoot>
    </table>
</body>

</html>