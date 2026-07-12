<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk #{{ $order['number'] }}</title>
    <style>
        @page { margin: 0; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html, body {
            width: {{ $paperSize == '58mm' ? '44mm' : '70mm' }};
        }
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: {{ $paperSize == '58mm' ? '9.5px' : '11px' }};
            font-weight: 700;
            -webkit-font-smoothing: none;
            text-rendering: optimizeSpeed;
            line-height: 1.15;
            color: #000;
            padding: 0.5mm 1mm;
            background: #fff;
        }
        .text-center { text-align: center; }
        .fw-bold { font-weight: 700; }
        .text-muted { color: #777; }
        .logo {
            width: 40px;
            height: 40px;
            object-fit: contain;
            margin: 3px auto 2px;
            display: block;
        }
        .hr {
            border: none;
            border-top: 1px dashed #999;
            margin: 2px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        td {
            vertical-align: top;
        }
        td.amount {
            text-align: right;
            white-space: nowrap;
        }
        .item td {
            padding: 0;
        }
        .total td {
            padding: 0;
        }
        .spacer { height: 1px; }
    </style>
</head>
<body>

    @if(!empty($store['logo_url']))
        <div class="text-center">
            <img src="{{ $store['logo_url'] }}" alt="Logo" class="logo">
        </div>
    @endif
    <div class="text-center fw-bold" style="font-size: 1.1em;">{{ $store['name'] }}</div>
    @if(!empty($store['address']))
        <div class="text-center text-muted">{{ $store['address'] }}</div>
    @endif
    @if(!empty($store['phone']))
        <div class="text-center text-muted">Telp: {{ $store['phone'] }}</div>
    @endif

    <div class="hr"></div>

    <table>
        <tr><td>No: {{ $order['number'] }}</td></tr>
        <tr><td>Kasir: {{ $order['cashier'] }}</td></tr>
        <tr><td>Tgl: {{ $order['date'] }}</td></tr>
        @if(!empty($order['customer_name']))
            <tr><td>Pemesan: {{ $order['customer_name'] }}</td></tr>
        @endif
        @if(!empty($order['table_number']))
            <tr><td>Meja: {{ $order['table_number'] }}</td></tr>
        @endif
    </table>

    <div class="hr"></div>

    <table>
        @foreach($items as $item)
            @php
                $discountedPrice = $item['price'] - ($item['discount_amount'] ?? 0);
            @endphp
            <tr class="item">
                <td>
                    {{ $item['name'] }}@if(!empty($item['variant'])) ({{ $item['variant'] }})@endif
                    <br>
                    <span class="text-muted">
                        {{ $item['quantity'] }} x
                        @if(!empty($item['discount_amount']) && $item['discount_amount'] > 0)
                            <s style="font-size: 0.85em;">Rp {{ number_format($item['price'], 0, ',', '.') }}</s> Rp {{ number_format($discountedPrice, 0, ',', '.') }}
                        @else
                            Rp {{ number_format($item['price'], 0, ',', '.') }}
                        @endif
                    </span>
                </td>
                <td class="amount">Rp {{ number_format($item['subtotal'], 0, ',', '.') }}</td>
            </tr>
        @endforeach
    </table>

    @if(!empty($order['notes']))
        <div class="hr"></div>
        <table>
            <tr><td class="fw-bold">Catatan Pesanan:</td></tr>
            <tr><td class="text-muted">{{ $order['notes'] }}</td></tr>
        </table>
    @endif

    <div class="hr"></div>

    <table class="total">
        <tr>
            <td>Subtotal</td>
            <td class="amount">Rp {{ number_format($order['subtotal'], 0, ',', '.') }}</td>
        </tr>
        @if(!empty($order['discount_amount']) && $order['discount_amount'] > 0)
        <tr>
            <td>Diskon Trx @if(!empty($order['discount_note']))<br><span class="text-muted">{{ $order['discount_note'] }}</span>@endif</td>
            <td class="amount">- Rp {{ number_format($order['discount_amount'], 0, ',', '.') }}</td>
        </tr>
        @endif
        @if($order['tax_amount'] > 0)
        <tr>
            <td>Pajak @if($order['tax_type'] == 'percentage')<br><span class="text-muted">{{ rtrim(rtrim(number_format($order['tax_rate'], 2, ',', '.'), '0'), ',') }}%</span>@endif</td>
            <td class="amount">Rp {{ number_format($order['tax_amount'], 0, ',', '.') }}</td>
        </tr>
        @endif
        @if($order['service_charge_amount'] > 0)
        <tr>
            <td>Layanan @if($order['service_charge_type'] == 'percentage')<br><span class="text-muted">{{ rtrim(rtrim(number_format($order['service_charge_rate'], 2, ',', '.'), '0'), ',') }}%</span>@endif</td>
            <td class="amount">Rp {{ number_format($order['service_charge_amount'], 0, ',', '.') }}</td>
        </tr>
        @endif
        <tr>
            <td class="fw-bold" style="padding-top: 2px;">Total:</td>
            <td class="amount fw-bold" style="padding-top: 2px;">Rp {{ number_format($order['total'], 0, ',', '.') }}</td>
        </tr>
        @if(strtolower($order['payment_method']) === 'cash' || strtolower($order['payment_method']) === 'tunai')
        <tr>
            <td class="text-muted">Bayar ({{ $order['payment_method'] }})</td>
            <td class="amount text-muted">Rp {{ number_format($order['cash_received'] ?? 0, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="text-muted">Kembali</td>
            <td class="amount text-muted">Rp {{ number_format($order['change'] ?? 0, 0, ',', '.') }}</td>
        </tr>
        @else
        <tr>
            <td class="text-muted">Bayar ({{ $order['payment_method'] }})</td>
            <td class="amount text-muted">Rp {{ number_format($order['total'], 0, ',', '.') }}</td>
        </tr>
        @endif
    </table>

    <div class="hr"></div>

    @if(!empty($store['receipt_footer']))
        <div class="text-center text-muted">{!! nl2br(e($store['receipt_footer'])) !!}</div>
    @endif

    <div class="hr"></div>

</body>
</html>
