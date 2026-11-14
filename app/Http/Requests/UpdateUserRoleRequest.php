<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $target = $this->route('user');

        return $user instanceof User
            && $target instanceof User
            && $user->can('updateRole', $target);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'role' => [
                'bail',
                'required',
                'string',
                Rule::in(['admin', 'member']),
            ],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'role.required' => 'Role pengguna wajib dipilih.',
            'role.string' => 'Role pengguna tidak valid.',
            'role.in' => 'Role hanya boleh admin atau member.',
        ];
    }
}
