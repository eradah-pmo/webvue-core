<?php

namespace App\Modules\Users\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreUsersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->can('users.create') || 
               auth()->user()->can('users.edit');
    }

    public function rules(): array
    {
        $userId = $this->route('user') ? $this->route('user')->id : null;
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');
        
        return [
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100', 
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email:rfc,dns',
                'max:255',
                Rule::unique('users')->ignore($userId)
            ],
            'password' => [
                $isUpdate ? 'nullable' : 'required',
                'string',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
            ],
            'password_confirmation' => 'same:password',
            'phone' => 'nullable|string|max:20|regex:/^[\+]?[0-9\-\(\)\s]+$/',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg|max:10240', // 10MB max
            'locale' => 'nullable|string|in:en,ar',
            'timezone' => 'nullable|string|timezone',
            'department_id' => 'nullable|integer|exists:departments,id',
            'active' => 'boolean',
            'roles' => 'nullable|array',
            'roles.*' => 'string|exists:roles,name',
            'preferences' => 'nullable|array',
            'preferences.theme' => 'nullable|string|in:light,dark,auto',
            'preferences.notifications' => 'nullable|boolean',
            'preferences.language' => 'nullable|string|in:en,ar',
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required' => __('users:validation.first_name_required'),
            'last_name.required' => __('users:validation.last_name_required'),
            'name.required' => __('users:validation.name_required'),
            'email.required' => __('users:validation.email_required'),
            'email.email' => __('users:validation.email_invalid'),
            'email.unique' => __('users:validation.email_exists'),
            'password.required' => __('users:validation.password_required'),
            'password.min' => __('users:validation.password_min'),
            'phone.regex' => __('users:validation.phone_invalid'),
            'avatar.image' => __('users:validation.avatar_invalid'),
            'avatar.max' => __('users:validation.avatar_too_large'),
            'department_id.exists' => __('users:validation.department_not_found'),
            'roles.*.exists' => __('users:validation.role_not_found'),
        ];
    }

    protected function prepareForValidation(): void
    {
        // إنشاء اسم كامل من الاسم الأول والأخير
        if ($this->filled(['first_name', 'last_name'])) {
            $this->merge([
                'name' => trim($this->first_name . ' ' . $this->last_name)
            ]);
        }

        // تعيين القيم الافتراضية
        $this->merge([
            'active' => $this->boolean('active', true),
            'locale' => $this->input('locale', 'en'),
            'timezone' => $this->input('timezone', 'UTC'),
        ]);
    }
}