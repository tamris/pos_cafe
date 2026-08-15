<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cetak Barcode</title>
    <style>
        /* Style untuk Kertas Label standard (Contoh: 108 / A4) */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }
        
        .grid-container {
            display: grid;
            /* Grid 3 Kolom (Sesuaikan dengan kertas label kamu) */
            grid-template-columns: repeat(3, 1fr); 
            gap: 15px;
        }

        .label-item {
            border: 1px dashed #ccc; /* Hapus border ini kalau mau print beneran */
            padding: 10px;
            text-align: center;
            height: 120px; /* Tinggi per stiker */
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            page-break-inside: avoid; /* Biar gak kepotong */
        }

        .price {
            font-size: 14px;
            font-weight: bold;
            margin-top: 5px;
        }
        
        .name {
            font-size: 12px;
            margin-bottom: 5px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 100%;
        }

        @media print {
            @page { margin: 0; }
            body { padding: 10px; }
            .label-item { border: none; } /* Hilangkan garis bantu pas print */
        }
    </style>
</head>
<body>
    
    <div class="grid-container">
        @foreach($products as $item)
            {{-- Loop sebanyak Quantity yang diminta --}}
            @for($i = 0; $i < $item['quantity']; $i++)
                <div class="label-item">
                    <div class="name">{{ $item['name'] }}</div>
                    
                    <div style="margin: 5px 0;">
                        {!! (new \Picqer\Barcode\BarcodeGeneratorSVG())->getBarcode($item['barcode'], (new \Picqer\Barcode\BarcodeGeneratorSVG())::TYPE_CODE_128, 1.5, 40) !!}
                    </div>
                    
                    <div style="font-size: 10px; letter-spacing: 2px;">{{ $item['barcode'] }}</div>
                    
                    {{-- HANYA TAMPILKAN JIKA $showPrice TRUE --}}
                    @if($showPrice)
                        <div class="price">Rp {{ number_format($item['price'], 0, ',', '.') }}</div>
                    @endif
                </div>
            @endfor
        @endforeach
    </div>

    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>