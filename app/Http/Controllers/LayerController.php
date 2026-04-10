<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLayerRequest;
use App\Http\Requests\UpdateLayerRequest;
use App\Models\Layer;
use App\Models\Layup;
use App\Models\Supplier;
use App\Services\LayerService;
use Illuminate\Http\JsonResponse;

class LayerController extends Controller
{
    public function __construct(
        private LayerService $layerService,
    ) {}

    public function index(Supplier $supplier, Layup $layup): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->layerService->allByLayup($layup->id),
        ]);
    }

    public function store(StoreLayerRequest $request, Supplier $supplier, Layup $layup): JsonResponse
    {
        $layer = $this->layerService->create(array_merge(
            $request->validated(),
            ['layup_id' => $layup->id]
        ));
        return response()->json([
            'success' => true,
            'data' => $layer,
        ], 201);
    }

    public function show(Supplier $supplier, Layup $layup, Layer $layer): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->layerService->find($layer->id),
        ]);
    }

    public function update(UpdateLayerRequest $request, Supplier $supplier, Layup $layup, Layer $layer): JsonResponse
    {
        $layer = $this->layerService->update($layer, $request->validated());
        return response()->json([
            'success' => true,
            'data' => $layer,
        ]);
    }

    public function destroy(Supplier $supplier, Layup $layup, Layer $layer): JsonResponse
    {
        $this->layerService->delete($layer);
        return response()->json([
            'success' => true,
            'message' => 'Layer deleted.',
        ]);
    }
}
