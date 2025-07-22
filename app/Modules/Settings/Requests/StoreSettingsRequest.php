<?php

namespace App\Modules\Settings\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $settingId = $this->route('setting') ? $this->route('setting')->id : null;
        
        return [
            'key' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9_\.]+$/', // Only lowercase, numbers, underscore, dot
                Rule::unique('settings', 'key')->ignore($settingId),
            ],
            'category' => 'required|string|max:100',
            'value' => 'nullable',
            'type' => [
                'required',
                'string',
                Rule::in(['string', 'number', 'boolean', 'json', 'file']),
            ],
            'description' => 'nullable|string|max:1000',
            'validation_rules' => 'nullable|array',
            'options' => 'nullable|array',
            'is_public' => 'boolean',
            'is_encrypted' => 'boolean',
            'sort_order' => 'integer|min:0',
            'active' => 'boolean',
            'file' => 'nullable|file|max:10240', // 10MB max
        ];
    }

    public function messages(): array
    {
        return [
            'key.required' => __('settings.key_required'),
            'key.unique' => __('settings.key_unique'),
            'key.regex' => __('settings.key_format'),
            'category.required' => __('settings.category_required'),
            'type.required' => __('settings.type_required'),
            'type.in' => __('settings.type_invalid'),
            'file.max' => __('settings.file_too_large'),
        ];
    }

    public function attributes(): array
    {
        return [
            'key' => __('settings.key'),
            'category' => __('settings.category'),
            'value' => __('settings.value'),
            'type' => __('settings.type'),
            'description' => __('settings.description'),
            'is_public' => __('settings.is_public'),
            'is_encrypted' => __('settings.is_encrypted'),
            'sort_order' => __('settings.sort_order'),
            'active' => __('settings.active'),
        ];
    }

    protected function prepareForValidation(): void
    {
        // Convert boolean strings to actual booleans
        $this->merge([
            'is_public' => $this->boolean('is_public'),
            'is_encrypted' => $this->boolean('is_encrypted'),
            'active' => $this->boolean('active', true), // Default to true
        ]);
        
        // Set default sort_order if not provided
        if (!$this->has('sort_order')) {
            $this->merge(['sort_order' => 0]);
        }
    }
}