<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$owner = App\Models\User::where('role', 'owner')->first();
$cashier = App\Models\User::where('role', 'cashier')->first();

if (!$cashier) {
    echo "No cashier found.\n";
    exit;
}

echo "Before: " . ($cashier->is_active ? 'true' : 'false') . "\n";

$request = Illuminate\Http\Request::create("/settings/cashiers/{$cashier->id}/toggle", 'PATCH');
$request->setUserResolver(function () use ($owner) {
    return $owner;
});

// Since we bypass the web middleware group, the route might not match correctly without the router handling the full lifecycle,
// Let's just dispatch via the Kernel.
$httpKernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $httpKernel->handle($request);

echo "Status: " . $response->getStatusCode() . "\n";
echo "After: " . ($cashier->fresh()->is_active ? 'true' : 'false') . "\n";
