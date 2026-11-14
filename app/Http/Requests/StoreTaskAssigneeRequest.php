<?php

namespace App\Http\Requests;

use App\Models\Task;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaskAssigneeRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $task = $this->route('task');

        return $user instanceof User
            && $task instanceof Task
            && $user->can('manageAssignees', $task);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $task = $this->route('task');

        if (! $task instanceof Task) {
            return [];
        }

        return [
            'user_id' => [
                'bail',
                'required',
                'integer',
                'exists:users,id',
                Rule::unique('task_user', 'user_id')
                    ->where('task_id', $task->getKey()),
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
            'user_id.required' => 'Pilih pengguna yang akan ditambahkan.',
            'user_id.integer' => 'Pengguna yang dipilih tidak valid.',
            'user_id.exists' => 'Pengguna yang dipilih tidak ditemukan.',
            'user_id.unique' => 'Pengguna tersebut sudah menjadi penanggung jawab.',
        ];
    }
}
