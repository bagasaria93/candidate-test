<?php

namespace Tests\Feature;

use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_all_suppliers(): void
    {
        Supplier::create([
            'name' => 'PT Test Supplier',
            'email' => 'test@supplier.com',
            'phone' => '08123456789',
            'address' => 'Jl. Test No. 1',
        ]);

        $response = $this->getJson('/api/suppliers');

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonCount(1, 'data');
    }

    public function test_can_create_supplier(): void
    {
        $response = $this->postJson('/api/suppliers', [
            'name' => 'PT Supplier Baru',
            'email' => 'baru@supplier.com',
            'phone' => '08111111111',
            'address' => 'Jl. Baru No. 1',
        ]);

        $response->assertStatus(201)
            ->assertJson(['success' => true])
            ->assertJsonPath('data.name', 'PT Supplier Baru');

        $this->assertDatabaseHas('suppliers', ['name' => 'PT Supplier Baru']);
    }

    public function test_cannot_create_supplier_without_name(): void
    {
        $response = $this->postJson('/api/suppliers', [
            'email' => 'test@supplier.com',
        ]);

        $response->assertStatus(422);
    }

    public function test_can_show_supplier(): void
    {
        $supplier = Supplier::create([
            'name' => 'PT Test Supplier',
            'email' => 'test@supplier.com',
            'phone' => '08123456789',
            'address' => 'Jl. Test No. 1',
        ]);

        $response = $this->getJson("/api/suppliers/{$supplier->id}");

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonPath('data.name', 'PT Test Supplier');
    }

    public function test_can_update_supplier(): void
    {
        $supplier = Supplier::create([
            'name' => 'PT Test Supplier',
            'email' => 'test@supplier.com',
            'phone' => '08123456789',
            'address' => 'Jl. Test No. 1',
        ]);

        $response = $this->putJson("/api/suppliers/{$supplier->id}", [
            'name' => 'PT Test Supplier Updated',
            'email' => 'updated@supplier.com',
            'phone' => '08999999999',
            'address' => 'Jl. Updated No. 1',
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonPath('data.name', 'PT Test Supplier Updated');

        $this->assertDatabaseHas('suppliers', ['name' => 'PT Test Supplier Updated']);
    }

    public function test_can_delete_supplier(): void
    {
        $supplier = Supplier::create([
            'name' => 'PT Test Supplier',
            'email' => 'test@supplier.com',
            'phone' => '08123456789',
            'address' => 'Jl. Test No. 1',
        ]);

        $response = $this->deleteJson("/api/suppliers/{$supplier->id}");

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('suppliers', ['id' => $supplier->id]);
    }

    public function test_returns_404_for_nonexistent_supplier(): void
    {
        $response = $this->getJson('/api/suppliers/999');

        $response->assertStatus(404);
    }
}
