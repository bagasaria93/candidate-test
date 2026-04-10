<?php

namespace App\Services;

use App\Models\Layup;
use App\Repositories\LayupRepository;
use Illuminate\Database\Eloquent\Collection;

class LayupService
{
    public function __construct(
        private LayupRepository $layupRepository,
    ) {}

    public function allBySupplier(int $supplierId): Collection
    {
        return $this->layupRepository->allBySupplier($supplierId);
    }

    public function find(int $id): Layup
    {
        return $this->layupRepository->find($id);
    }

    public function create(array $data): Layup
    {
        return $this->layupRepository->create($data);
    }

    public function update(Layup $layup, array $data): Layup
    {
        return $this->layupRepository->update($layup, $data);
    }

    public function delete(Layup $layup): void
    {
        $this->layupRepository->delete($layup);
    }
}
