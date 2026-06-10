<?php
namespace Tests\Feature;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
class ToggleCashierTest extends TestCase {
    public function test_toggle() {
        $owner = User::where('role', 'owner')->first();
        $cashier = User::where('role', 'cashier')->first();
        $this->actingAs($owner);
        $response = $this->patch("/settings/cashiers/{$cashier->id}/toggle");
        $response->assertStatus(302);
        dump($cashier->fresh()->is_active);
    }
}
