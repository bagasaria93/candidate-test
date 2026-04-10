<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportSupplierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'data' => ['required', 'array'],
            'data.layups' => ['required', 'array'],
            'data.layups.*.name' => ['required', 'string'],
            'data.layups.*.description' => ['nullable', 'string'],
            'data.layups.*.layers' => ['required', 'array'],
            'data.layups.*.layers.*.layer_order' => ['required', 'integer'],
            'data.layups.*.layers.*.thickness' => ['required', 'numeric'],
            'data.layups.*.layers.*.width' => ['required', 'numeric'],
            'data.layups.*.layers.*.angle' => ['required', 'numeric'],
            'conflict_strategy' => ['required', 'in:overwrite,skip,duplicate,reject'],
        ];
    }
}
