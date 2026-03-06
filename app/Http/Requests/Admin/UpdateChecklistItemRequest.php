<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateChecklistItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'questions' => 'required|array|min:1',
            'questions.*' => 'required|string',
            'types' => 'required|array',
            'types.*' => 'required|in:pass_fail,number,text,checkbox,boolean,header',
            'units' => 'nullable|array',
            'units.*' => 'nullable|string',
        ];
    }
}
