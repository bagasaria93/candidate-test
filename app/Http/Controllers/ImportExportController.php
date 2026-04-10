<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImportSupplierRequest;
use App\Models\Supplier;
use App\Services\SupplierService;
use Illuminate\Http\JsonResponse;

class ImportExportController extends Controller
{
    public function __construct(
        private SupplierService $supplierService,
    ) {}

    public function export(Supplier $supplier): JsonResponse
    {
        return response()->json(
            $this->supplierService->export($supplier)
        );
    }

    public function import(ImportSupplierRequest $request, Supplier $supplier): JsonResponse
    {
        $result = $this->supplierService->import(
            $supplier,
            $request->validated()['data'],
            $request->validated()['conflict_strategy']
        );

        $statusCode = $result['success'] ? 200 : 422;

        return response()->json($result, $statusCode);
    }
}
