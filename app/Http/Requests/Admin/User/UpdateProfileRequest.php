<?php

namespace App\Http\Requests\Admin\User;

use App\Enums\UserStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Chỉ admin mới được phép
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name'  => ['required', 'string', 'max:255'],
            'address'    => ['nullable', 'string', 'max:255'],
            'status'     => ['required',  Rule::enum(UserStatus::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required' => 'Vui lòng nhập tên.',
            'last_name.required'  => 'Vui lòng nhập họ.',
            'status.required'     => 'Vui lòng chọn trạng thái.',
            'status.enum'           => 'Trạng thái không hợp lệ.',
        ];
    }
}
