<?php

namespace App\Http\Requests;

use App\Rules\ValidateCategoryUuid;
use Illuminate\Foundation\Http\FormRequest;

class ProductStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'category_uuid' => ['required', new ValidateCategoryUuid($this->category_uuid ?? "")],
            'title' => 'required',
            'price' => ['required', 'numeric', 'min:0'],
            'description' => 'required',
            'metadata.*.image' => 'required',
            'metadata.*.brand' => 'required',
        ];
    }
}
