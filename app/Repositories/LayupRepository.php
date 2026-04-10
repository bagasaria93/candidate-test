<?php

namespace App\Repositories;

use App\Models\Layup;
use Illuminate\Database\Eloquent\Collection;

class LayupRepository
{
    public function allBySupplier(int $supplierId): Collection
    {
        return Layup::with('layers')->where('supplier_id', $supplierId)->get();
    }

    public function find(int $id): Layup
    {
        return Layup::with('layers')->findOrFail($id);
    }

    public function create(array $data): Layup
    {
        return Layup::create($data);
    }

    public function update(Layup $layup, array $data): Layup
    {
        $layup->update($data);
        return $layup;
    }

    public function delete(Layup $layup): void
    {
        $layup->delete();
    }

    public function findByNameAndSupplier(string $name, int $supplierId): ?Layup
    {
        return Layup::with('layers')
            ->where('supplier_id', $supplierId)
            ->where('name', $name)
            ->first();
    }
}
