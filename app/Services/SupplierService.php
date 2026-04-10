<?php

namespace App\Services;

use App\Models\Supplier;
use App\Repositories\LayupRepository;
use App\Repositories\LayerRepository;
use App\Repositories\SupplierRepository;
use Illuminate\Database\Eloquent\Collection;

class SupplierService
{
    public function __construct(
        private SupplierRepository $supplierRepository,
        private LayupRepository $layupRepository,
        private LayerRepository $layerRepository,
    ) {}

    public function all(): Collection
    {
        return $this->supplierRepository->all();
    }

    public function find(int $id): Supplier
    {
        return $this->supplierRepository->find($id);
    }

    public function create(array $data): Supplier
    {
        return $this->supplierRepository->create($data);
    }

    public function update(Supplier $supplier, array $data): Supplier
    {
        return $this->supplierRepository->update($supplier, $data);
    }

    public function delete(Supplier $supplier): void
    {
        $this->supplierRepository->delete($supplier);
    }

    public function export(Supplier $supplier): array
    {
        $supplier->load('layups.layers');
        return [
            'supplier' => [
                'id' => $supplier->id,
                'name' => $supplier->name,
                'email' => $supplier->email,
                'phone' => $supplier->phone,
                'address' => $supplier->address,
                'layups' => $supplier->layups->map(function ($layup) {
                    return [
                        'id' => $layup->id,
                        'name' => $layup->name,
                        'description' => $layup->description,
                        'layers' => $layup->layers->map(function ($layer) {
                            return [
                                'layer_order' => $layer->layer_order,
                                'thickness' => (float) $layer->thickness,
                                'width' => (float) $layer->width,
                                'angle' => (float) $layer->angle,
                            ];
                        })->toArray(),
                    ];
                })->toArray(),
            ],
        ];
    }

    public function import(Supplier $supplier, array $data, string $conflictStrategy): array
    {
        $conflicts = [];
        $imported = [];

        foreach ($data['layups'] as $layupData) {
            $existingLayup = $this->layupRepository->findByNameAndSupplier(
                $layupData['name'],
                $supplier->id
            );

            if (!$existingLayup) {
                $newLayup = $this->layupRepository->create([
                    'supplier_id' => $supplier->id,
                    'name' => $layupData['name'],
                    'description' => $layupData['description'] ?? null,
                ]);

                foreach ($layupData['layers'] as $layerData) {
                    $this->layerRepository->create([
                        'layup_id' => $newLayup->id,
                        'layer_order' => $layerData['layer_order'],
                        'thickness' => $layerData['thickness'],
                        'width' => $layerData['width'],
                        'angle' => $layerData['angle'],
                    ]);
                }

                $imported[] = $layupData['name'];
                continue;
            }

            foreach ($layupData['layers'] as $layerData) {
                $existingLayer = $this->layerRepository->findByOrderAndLayup(
                    $layerData['layer_order'],
                    $existingLayup->id
                );

                if (!$existingLayer) {
                    $this->layerRepository->create([
                        'layup_id' => $existingLayup->id,
                        'layer_order' => $layerData['layer_order'],
                        'thickness' => $layerData['thickness'],
                        'width' => $layerData['width'],
                        'angle' => $layerData['angle'],
                    ]);
                    continue;
                }

                $hasConflict = $existingLayer->thickness != $layerData['thickness']
                    || $existingLayer->width != $layerData['width']
                    || $existingLayer->angle != $layerData['angle'];

                if ($hasConflict) {
                    $conflictRecord = [
                        'layup' => $layupData['name'],
                        'layer_order' => $layerData['layer_order'],
                        'existing' => [
                            'thickness' => (float) $existingLayer->thickness,
                            'width' => (float) $existingLayer->width,
                            'angle' => (float) $existingLayer->angle,
                        ],
                        'incoming' => [
                            'thickness' => (float) $layerData['thickness'],
                            'width' => (float) $layerData['width'],
                            'angle' => (float) $layerData['angle'],
                        ],
                        'resolution' => $conflictStrategy,
                    ];

                    if ($conflictStrategy === 'overwrite') {
                        $this->layerRepository->update($existingLayer, [
                            'thickness' => $layerData['thickness'],
                            'width' => $layerData['width'],
                            'angle' => $layerData['angle'],
                        ]);
                    } elseif ($conflictStrategy === 'duplicate') {
                        $duplicateLayup = $this->layupRepository->findByNameAndSupplier(
                            $layupData['name'] . ' (imported)',
                            $supplier->id
                        );

                        if (!$duplicateLayup) {
                            $duplicateLayup = $this->layupRepository->create([
                                'supplier_id' => $supplier->id,
                                'name' => $layupData['name'] . ' (imported)',
                                'description' => $layupData['description'] ?? null,
                            ]);
                        }

                        $this->layerRepository->create([
                            'layup_id' => $duplicateLayup->id,
                            'layer_order' => $layerData['layer_order'],
                            'thickness' => $layerData['thickness'],
                            'width' => $layerData['width'],
                            'angle' => $layerData['angle'],
                        ]);
                    } elseif ($conflictStrategy === 'reject') {
                        return [
                            'success' => false,
                            'message' => 'Import rejected due to conflicts.',
                            'conflicts' => [$conflictRecord],
                        ];
                    }

                    $conflicts[] = $conflictRecord;
                }
            }

            $imported[] = $layupData['name'];
        }

        return [
            'success' => true,
            'message' => 'Import completed.',
            'imported' => $imported,
            'conflicts' => $conflicts,
        ];
    }
}
