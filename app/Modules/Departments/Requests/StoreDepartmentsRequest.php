<?php

namespace App\Modules\Departments\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDepartmentsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->can('departments.create') || 
               auth()->user()->can('departments.edit');
    }

    public function rules(): array
    {
        $departmentId = $this->route('department') ? $this->route('department')->id : null;
        
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'code' => [
                'required',
                'string',
                'max:50',
                'alpha_dash',
                Rule::unique('departments')->ignore($departmentId)
            ],
            'parent_id' => [
                'nullable',
                'integer',
                'exists:departments,id',
                Rule::notIn([$departmentId]) // منع القسم من أن يكون أب لنفسه
            ],
            'manager_id' => 'nullable|integer|exists:users,id',
            'email' => 'nullable|email:rfc,dns|max:255',
            'phone' => 'nullable|string|max:20|regex:/^[\+]?[0-9\-\(\)\s]+$/',
            'address' => 'nullable|string|max:500',
            'budget' => 'nullable|numeric|min:0|max:999999999.99',
            'color' => 'nullable|string|regex:/^#[a-fA-F0-9]{6}$/',
            'active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => __('departments:validation.name_required'),
            'name.max' => __('departments:validation.name_max'),
            'code.required' => __('departments:validation.code_required'),
            'code.unique' => __('departments:validation.code_exists'),
            'code.alpha_dash' => __('departments:validation.code_format'),
            'parent_id.exists' => __('departments:validation.parent_not_found'),
            'parent_id.not_in' => __('departments:validation.parent_self'),
            'manager_id.exists' => __('departments:validation.manager_not_found'),
            'email.email' => __('departments:validation.email_invalid'),
            'phone.regex' => __('departments:validation.phone_invalid'),
            'budget.numeric' => __('departments:validation.budget_numeric'),
            'budget.min' => __('departments:validation.budget_min'),
            'color.regex' => __('departments:validation.color_format'),
        ];
    }

    protected function prepareForValidation(): void
    {
        // تنظيف كود القسم
        if ($this->filled('code')) {
            $this->merge([
                'code' => strtoupper(trim($this->code))
            ]);
        }

        // تعيين القيم الافتراضية
        $this->merge([
            'active' => $this->boolean('active', true),
            'sort_order' => $this->input('sort_order', 0),
        ]);
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // التحقق من عدم وجود دورة في التسلسل الهرمي
            if ($this->filled('parent_id') && $this->route('department')) {
                if ($this->wouldCreateCircularReference()) {
                    $validator->errors()->add('parent_id', __('departments:validation.circular_reference'));
                }
            }
        });
    }

    private function wouldCreateCircularReference(): bool
    {
        $departmentId = $this->route('department')->id;
        $parentId = $this->input('parent_id');
        
        // فحص الأسلاف للقسم الأب المقترح
        $currentParent = \App\Modules\Departments\Models\Department::find($parentId);
        
        while ($currentParent) {
            if ($currentParent->id === $departmentId) {
                return true;
            }
            $currentParent = $currentParent->parent;
        }
        
        return false;
    }
}