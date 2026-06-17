<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk #{{ $order['number'] }}</title>
    <style>
        @page {
            size: {{ $paperSize == '58mm' ? '58mm' : '80mm' }} auto;
            margin: 0mm;
        }
        * {
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            box-sizing: border-box;
        }
        html, body {
            width: {{ $paperSize == '58mm' ? '58mm' : '80mm' }};
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: {{ $paperSize == '58mm' ? '10px' : '12px' }};
            line-height: 1.3;
            color: #000;
            padding: 4mm 3mm;
            background: #fff;
            word-break: break-word;
            overflow-wrap: break-word;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }
        .mb-1 { margin-bottom: 4px; }
        .mb-2 { margin-bottom: 8px; }
        .mt-2 { margin-top: 8px; }
        .border-top { border-top: 1px dashed #000; padding-top: 4px; margin-top: 4px; }
        .border-bottom { border-bottom: 1px dashed #000; padding-bottom: 4px; margin-bottom: 4px; }

        .logo {
            max-width: 70%;
            height: auto;
            margin: 0 auto 4px;
            display: block;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        table td {
            vertical-align: top;
            word-break: break-word;
            overflow-wrap: break-word;
        }

        .item-row td {
            padding-bottom: 2px;
        }

        .totals td {
            padding-top: 2px;
        }

        @media print {
            html, body {
                width: {{ $paperSize == '58mm' ? '58mm' : '80mm' }};
            }
            @page {
                size: {{ $paperSize == '58mm' ? '58mm' : '80mm' }} auto;
                margin: 0mm;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="text-center mb-2 border-bottom">
        @if(!empty($store['logo_url']))
            <img src="{{ $store['logo_url'] }}" alt="Logo" class="logo">
        @endif
        <div class="font-bold mb-1">{{ $store['name'] }}</div>
        @if(!empty($store['address']))
            <div>{{ $store['address'] }}</div>
        @endif
        @if(!empty($store['phone']))
            <div>Telp: {{ $store['phone'] }}</div>
        @endif
    </div>

    <!-- Order Info -->
    <div class="mb-2 border-bottom">
        <table>
            <tr>
                <td class="text-left">No: {{ $order['number'] }}</td>
            </tr>
            <tr>
                <td class="text-left">Tgl: {{ $order['date'] }}</td>
            </tr>
            <tr>
                <td class="text-left">Kasir: {{ $order['cashier'] }}</td>
            </tr>
        </table>
    </div>

    <!-- Items -->
    <div class="mb-2 border-bottom">
        <table>
            @foreach($items as $item)
            <tr class="item-row">
                <td colspan="3" class="text-left">
                    {{ $item['name'] }}
                    @if(!empty($item['variant']))
                        ({{ $item['variant'] }})
                    @endif
                </td>
            </tr>
            <tr class="item-row">
                <td class="text-left">{{ $item['quantity'] }}x</td>
                <td class="text-left">Rp {{ number_format($item['price'], 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($item['subtotal'], 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </table>
    </div>

    <!-- Totals -->
    <div class="mb-2 border-bottom totals">
        <table>
            <tr>
                <td class="text-left font-bold">Total</td>
                <td class="text-right font-bold">Rp {{ number_format($order['total'], 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="text-left">Bayar ({{ $order['payment_method'] }})</td>
                <td class="text-right">Rp {{ number_format($order['cash_received'], 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="text-left">Kembali</td>
                <td class="text-right">Rp {{ number_format($order['change'], 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    <!-- Footer -->
    <div class="text-center mt-2">
        @if(!empty($store['receipt_footer']))
            <div class="mb-1">{!! nl2br(e($store['receipt_footer'])) !!}</div>
        @endif
        <div>Terima Kasih</div>
    </div>
</body>
</html>
