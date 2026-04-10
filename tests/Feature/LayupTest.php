<?php

namespace Tests\Feature;

use App\Models\Layup;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LayupTest extends TestCase
{
    use RefreshDatabase;

    private Supplier $supplier;

    protected function setUp(): void
    {
        parent::setUp();

        $this->supplier = Supplier::create([
            'name' => 'PT Test Supplier',
            'email' => 'test@supplier.com',
            'phone' => '08123456789',
            'address' => 'Jl. Test No. 1',
        ]);
    }

    public function test_can_get_all_layups_by_supplier(): void
    {
        Layup::create([
            'supplier_id' => $this->supplier->id,
            'name' => 'Layup Test A',
            'description' => 'Deskripsi A',
        ]);

        $response = $this->getJson("/api/suppliers/{$this->supplier->id}/layups");

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonCount(1, 'data');
    }

    public function test_can_create_layup(): void
    {
        $response = $this->postJson("/api/suppliers/{$this->supplier->id}/layups", [
            'name' => 'Layup Test B',
            'description' => 'Deskripsi B',
        ]);

        $response->assertStatus(201)
            ->assertJson(['success' => true])
            ->assertJsonPath('data.name', 'Layup Test B');

        $this->assertDatabaseHas('layups', [
            'name' => 'Layup Test B',
            'supplier_id' => $this->supplier->id,
        ]);
    }

    public function test_cannot_create_layup_without_name(): void
    {
        $response = $this->postJson("/api/suppliers/{$this->supplier->id}/layups", [
            'description' => 'Deskripsi tanpa nama',
        ]);

        $response->assertStatus(422);
    }

    public function test_can_update_layup(): void
    {
        $layup = Layup::create([
            'supplier_id' => $this->supplier->id,
            'name' => 'Layup Test C',
            'description' => 'Deskripsi C',
        ]);

        $response = $this->putJson("/api/suppliers/{$this->supplier->id}/layups/{$layup->id}", [
            'name' => 'Layup Test C Updated',
            'description' => 'Deskripsi C Updated',
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonPath('data.name', 'Layup Test C Updated');

        $this->assertDatabaseHas('layups', ['name' => 'Layup Test C Updated']);
    }

    public function test_can_delete_layup(): void
    {
        $layup = Layup::create([
            'supplier_id' => $this->supplier->id,
            'name' => 'Layup Test D',
            'description' => 'Deskripsi D',
        ]);

        $response = $this->deleteJson("/api/suppliers/{$this->supplier->id}/layups/{$layup->id}");

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('layups', ['id' => $layup->id]);
    }
}
