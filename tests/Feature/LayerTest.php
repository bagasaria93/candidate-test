<?php

namespace Tests\Feature;

use App\Models\Layer;
use App\Models\Layup;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LayerTest extends TestCase
{
    use RefreshDatabase;

    private Supplier $supplier;
    private Layup $layup;

    protected function setUp(): void
    {
        parent::setUp();

        $this->supplier = Supplier::create([
            'name' => 'PT Test Supplier',
            'email' => 'test@supplier.com',
            'phone' => '08123456789',
            'address' => 'Jl. Test No. 1',
        ]);

        $this->layup = Layup::create([
            'supplier_id' => $this->supplier->id,
            'name' => 'Layup Test',
            'description' => 'Deskripsi Test',
        ]);
    }

    public function test_can_get_all_layers_by_layup(): void
    {
        Layer::create([
            'layup_id' => $this->layup->id,
            'layer_order' => 1,
            'thickness' => 10.5,
            'width' => 200.0,
            'angle' => 45.0,
        ]);

        $response = $this->getJson("/api/suppliers/{$this->supplier->id}/layups/{$this->layup->id}/layers");

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonCount(1, 'data');
    }

    public function test_can_create_layer(): void
    {
        $response = $this->postJson("/api/suppliers/{$this->supplier->id}/layups/{$this->layup->id}/layers", [
            'layer_order' => 1,
            'thickness' => 10.5,
            'width' => 200.0,
            'angle' => 45.0,
        ]);

        $response->assertStatus(201)
            ->assertJson(['success' => true])
            ->assertJsonPath('data.layer_order', 1);

        $this->assertDatabaseHas('layers', [
            'layup_id' => $this->layup->id,
            'layer_order' => 1,
        ]);
    }

    public function test_cannot_create_layer_without_required_fields(): void
    {
        $response = $this->postJson("/api/suppliers/{$this->supplier->id}/layups/{$this->layup->id}/layers", [
            'layer_order' => 1,
        ]);

        $response->assertStatus(422);
    }

    public function test_can_update_layer(): void
    {
        $layer = Layer::create([
            'layup_id' => $this->layup->id,
            'layer_order' => 1,
            'thickness' => 10.5,
            'width' => 200.0,
            'angle' => 45.0,
        ]);

        $response = $this->putJson("/api/suppliers/{$this->supplier->id}/layups/{$this->layup->id}/layers/{$layer->id}", [
            'layer_order' => 1,
            'thickness' => 20.0,
            'width' => 300.0,
            'angle' => 90.0,
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('layers', [
            'id' => $layer->id,
            'thickness' => 20.0,
        ]);
    }

    public function test_can_delete_layer(): void
    {
        $layer = Layer::create([
            'layup_id' => $this->layup->id,
            'layer_order' => 1,
            'thickness' => 10.5,
            'width' => 200.0,
            'angle' => 45.0,
        ]);

        $response = $this->deleteJson("/api/suppliers/{$this->supplier->id}/layups/{$this->layup->id}/layers/{$layer->id}");

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('layers', ['id' => $layer->id]);
    }
}
