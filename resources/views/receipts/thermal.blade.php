<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk #{{ $order['number'] }}</title>
    <style>
        @page {
            margin: 0;
        }
        * {
            box-sizing: border-box;
        }
        html, body {
            /* Lebar area cetak fisik (printable area) printer 58mm biasanya hanya 46-48mm */
            width: {{ $paperSize == '58mm' ? '44mm' : '70mm' }};
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Courier New', Courier, monospace;
            /* Zoom out sedikit dengan mengecilkan font */
            font-size: {{ $paperSize == '58mm' ? '9.5px' : '11px' }};
            font-weight: bold; /* Memastikan semua teks tebal agar tidak pudar di thermal */
            -webkit-font-smoothing: none; /* Mematikan efek abu-abu pada pinggiran huruf */
            text-rendering: optimizeSpeed;
            line-height: 1.25;
            color: #000 !important;
            padding: 2mm 0mm; /* Kurangi padding samping agar tidak memakan tempat */
            background: transparent;
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
            @page {
                margin: 0;
            }
            body {
                margin: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="text-center mb-2 border-bottom">
        <!-- @if(!empty($store['logo_url']))
            <img src="{{ $store['logo_url'] }}" alt="Logo" class="logo">
        @endif -->
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
            @if(!empty($order['customer_name']))
            <tr>
                <td class="text-left">Pemesan: {{ $order['customer_name'] }}</td>
            </tr>
            @endif
            @if(!empty($order['table_number']))
            <tr>
                <td class="text-left">Meja: {{ $order['table_number'] }}</td>
            </tr>
            @endif
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
                <td class="text-left">
                    Rp {{ number_format($item['price'], 0, ',', '.') }}
                    @if(!empty($item['discount_amount']) && $item['discount_amount'] > 0)
                        <br><small>- Rp {{ number_format($item['discount_amount'], 0, ',', '.') }}</small>
                    @endif
                </td>
                <td class="text-right">Rp {{ number_format($item['subtotal'], 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </table>
    </div>

    @if(!empty($order['notes']))
    <!-- Order Notes -->
    <div class="mb-2 border-bottom">
        <div class="text-left font-bold mb-1">Catatan Pesanan:</div>
        <div class="text-left mb-1">{{ $order['notes'] }}</div>
    </div>
    @endif

    <!-- Totals -->
    <div class="mb-2 border-bottom totals">
        <table>
            <tr>
                <td class="text-left">Subtotal</td>
                <td class="text-right">Rp {{ number_format($order['subtotal'], 0, ',', '.') }}</td>
            </tr>
            @if(!empty($order['discount_amount']) && $order['discount_amount'] > 0)
            <tr>
                <td class="text-left">Diskon Trx @if(!empty($order['discount_note']))<br><small>{{ $order['discount_note'] }}</small>@endif</td>
                <td class="text-right">- Rp {{ number_format($order['discount_amount'], 0, ',', '.') }}</td>
            </tr>
            @endif
            @if($order['tax_amount'] > 0)
            <tr>
                <td class="text-left">Pajak @if($order['tax_type'] == 'percentage') ({{ rtrim(rtrim(number_format($order['tax_rate'], 2, ',', '.'), '0'), ',') }}%) @endif</td>
                <td class="text-right">Rp {{ number_format($order['tax_amount'], 0, ',', '.') }}</td>
            </tr>
            @endif
            @if($order['service_charge_amount'] > 0)
            <tr>
                <td class="text-left">Layanan @if($order['service_charge_type'] == 'percentage') ({{ rtrim(rtrim(number_format($order['service_charge_rate'], 2, ',', '.'), '0'), ',') }}%) @endif</td>
                <td class="text-right">Rp {{ number_format($order['service_charge_amount'], 0, ',', '.') }}</td>
            </tr>
            @endif
            <tr>
                <td class="text-left font-bold">Total</td>
                <td class="text-right font-bold">Rp {{ number_format($order['total'], 0, ',', '.') }}</td>
            </tr>
            @if(strtolower($order['payment_method']) === 'cash' || strtolower($order['payment_method']) === 'tunai')
            <tr>
                <td class="text-left">Bayar ({{ $order['payment_method'] }})</td>
                <td class="text-right">Rp {{ number_format($order['cash_received'] ?? 0, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="text-left">Kembali</td>
                <td class="text-right">Rp {{ number_format($order['change'] ?? 0, 0, ',', '.') }}</td>
            </tr>
            @else
            <tr>
                <td class="text-left">Bayar ({{ $order['payment_method'] }})</td>
                <td class="text-right">Rp {{ number_format($order['total'], 0, ',', '.') }}</td>
            </tr>
            @endif
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
