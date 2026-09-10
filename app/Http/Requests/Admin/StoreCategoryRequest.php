<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('categories')->where(
                fn ($query) => $query->where('type', $this->input('type'))
            )],
            'type' => ['required', Rule::in(['income', 'expense'])],
            'color' => ['required', 'string', 'max:255'],
            'icon' => ['required', 'string', 'max:255'],
        ];
    }
}
