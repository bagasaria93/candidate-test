<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLayupRequest;
use App\Http\Requests\UpdateLayupRequest;
use App\Models\Layup;
use App\Models\Supplier;
use App\Services\LayupService;
use Illuminate\Http\JsonResponse;

class LayupController extends Controller
{
    public function __construct(
        private LayupService $layupService,
    ) {}

    public function index(Supplier $supplier): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->layupService->allBySupplier($supplier->id),
        ]);
    }

    public function store(StoreLayupRequest $request, Supplier $supplier): JsonResponse
    {
        $layup = $this->layupService->create(array_merge(
            $request->validated(),
            ['supplier_id' => $supplier->id]
        ));
        return response()->json([
            'success' => true,
            'data' => $layup,
        ], 201);
    }

    public function show(Supplier $supplier, Layup $layup): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->layupService->find($layup->id),
        ]);
    }

    public function update(UpdateLayupRequest $request, Supplier $supplier, Layup $layup): JsonResponse
    {
        $layup = $this->layupService->update($layup, $request->validated());
        return response()->json([
            'success' => true,
            'data' => $layup,
        ]);
    }

    public function destroy(Supplier $supplier, Layup $layup): JsonResponse
    {
        $this->layupService->delete($layup);
        return response()->json([
            'success' => true,
            'message' => 'Layup deleted.',
        ]);
    }
}
