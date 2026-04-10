<?php

namespace App\Services;

use App\Models\Layer;
use App\Repositories\LayerRepository;
use Illuminate\Database\Eloquent\Collection;

class LayerService
{
    public function __construct(
        private LayerRepository $layerRepository,
    ) {}

    public function allByLayup(int $layupId): Collection
    {
        return $this->layerRepository->allByLayup($layupId);
    }

    public function find(int $id): Layer
    {
        return $this->layerRepository->find($id);
    }

    public function create(array $data): Layer
    {
        return $this->layerRepository->create($data);
    }

    public function update(Layer $layer, array $data): Layer
    {
        return $this->layerRepository->update($layer, $data);
    }

    public function delete(Layer $layer): void
    {
        $this->layerRepository->delete($layer);
    }
}
