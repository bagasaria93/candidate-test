<?php

namespace Tests\Feature;

use App\Models\Layer;
use App\Models\Layup;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImportExportTest extends TestCase
{
    use RefreshDatabase;

    private Supplier $supplier;
    private Layup $layup;
    private Layer $layer;

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

        $this->layer = Layer::create([
            'layup_id' => $this->layup->id,
            'layer_order' => 1,
            'thickness' => 10.5,
            'width' => 200.0,
            'angle' => 45.0,
        ]);
    }

    public function test_can_export_supplier(): void
    {
        $response = $this->getJson("/api/suppliers/{$this->supplier->id}/export");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'supplier' => [
                    'id',
                    'name',
                    'email',
                    'phone',
                    'address',
                    'layups' => [
                        '*' => [
                            'id',
                            'name',
                            'description',
                            'layers' => [
                                '*' => [
                                    'layer_order',
                                    'thickness',
                                    'width',
                                    'angle',
                                ],
                            ],
                        ],
                    ],
                ],
            ]);
    }

    public function test_can_import_new_layup(): void
    {
        $response = $this->postJson("/api/suppliers/{$this->supplier->id}/import", [
            'data' => [
                'layups' => [
                    [
                        'name' => 'Layup Import Baru',
                        'description' => 'Deskripsi import',
                        'layers' => [
                            [
                                'layer_order' => 1,
                                'thickness' => 15.0,
                                'width' => 250.0,
                                'angle' => 60.0,
                            ],
                        ],
                    ],
                ],
            ],
            'conflict_strategy' => 'overwrite',
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonPath('imported.0', 'Layup Import Baru');

        $this->assertDatabaseHas('layups', ['name' => 'Layup Import Baru']);
    }

    public function test_import_overwrite_conflict(): void
    {
        $response = $this->postJson("/api/suppliers/{$this->supplier->id}/import", [
            'data' => [
                'layups' => [
                    [
                        'name' => 'Layup Test',
                        'description' => 'Deskripsi Test',
                        'layers' => [
                            [
                                'layer_order' => 1,
                                'thickness' => 99.9,
                                'width' => 999.0,
                                'angle' => 180.0,
                            ],
                        ],
                    ],
                ],
            ],
            'conflict_strategy' => 'overwrite',
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonCount(1, 'conflicts');

        $this->assertDatabaseHas('layers', [
            'layup_id' => $this->layup->id,
            'thickness' => 99.9,
        ]);
    }

    public function test_import_skip_conflict(): void
    {
        $response = $this->postJson("/api/suppliers/{$this->supplier->id}/import", [
            'data' => [
                'layups' => [
                    [
                        'name' => 'Layup Test',
                        'description' => 'Deskripsi Test',
                        'layers' => [
                            [
                                'layer_order' => 1,
                                'thickness' => 99.9,
                                'width' => 999.0,
                                'angle' => 180.0,
                            ],
                        ],
                    ],
                ],
            ],
            'conflict_strategy' => 'skip',
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('layers', [
            'layup_id' => $this->layup->id,
            'thickness' => 10.5,
        ]);
    }

    public function test_import_reject_conflict(): void
    {
        $response = $this->postJson("/api/suppliers/{$this->supplier->id}/import", [
            'data' => [
                'layups' => [
                    [
                        'name' => 'Layup Test',
                        'description' => 'Deskripsi Test',
                        'layers' => [
                            [
                                'layer_order' => 1,
                                'thickness' => 99.9,
                                'width' => 999.0,
                                'angle' => 180.0,
                            ],
                        ],
                    ],
                ],
            ],
            'conflict_strategy' => 'reject',
        ]);

        $response->assertStatus(422)
            ->assertJson(['success' => false]);
    }

    public function test_import_duplicate_conflict(): void
    {
        $response = $this->postJson("/api/suppliers/{$this->supplier->id}/import", [
            'data' => [
                'layups' => [
                    [
                        'name' => 'Layup Test',
                        'description' => 'Deskripsi Test',
                        'layers' => [
                            [
                                'layer_order' => 1,
                                'thickness' => 99.9,
                                'width' => 999.0,
                                'angle' => 180.0,
                            ],
                        ],
                    ],
                ],
            ],
            'conflict_strategy' => 'duplicate',
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('layups', [
            'supplier_id' => $this->supplier->id,
            'name' => 'Layup Test (imported)',
        ]);
    }

    public function test_import_requires_conflict_strategy(): void
    {
        $response = $this->postJson("/api/suppliers/{$this->supplier->id}/import", [
            'data' => [
                'layups' => [],
            ],
        ]);

        $response->assertStatus(422);
    }
}
