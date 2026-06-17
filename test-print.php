<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$order = App\Models\Order::first();
$html = app(App\Services\ReceiptService::class)->renderReceiptHtml($order);
$encoded = rawurlencode($html);

$client = app(\Native\Laravel\Client\Client::class);
$response = $client->post('system/print-to-pdf', [
    'html' => $encoded,
    'settings' => [],
]);

dump($response->status());
dump($response->body());
