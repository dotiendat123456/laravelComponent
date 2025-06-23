<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => 'required|string|max:30',
            'last_name' => 'required|string|max:30',
            'email' => 'required|email:rfc,dns|max:100|unique:users,email',
            'password' => [
                'required',
                'string',
                'confirmed',
                Password::min(8)->mixedCase()->numbers()->symbols()
            ],
        ];
    }

    public function attributes()
    {
        return [
            'first_name' => 'Họ',
            'last_name' => 'Tên',
            'email' => 'Email',
            'password' => 'Mật khẩu',
            'password_confirmation' => 'Xác nhận mật khẩu',
        ];
    }

    public function messages()
    {
        return [
            // Họ
            'first_name.required' => 'Vui lòng nhập họ.',
            'first_name.string' => 'Họ phải là chuỗi ký tự.',
            'first_name.max' => 'Họ không được vượt quá 30 ký tự.',

            // Tên
            'last_name.required' => 'Vui lòng nhập tên.',
            'last_name.string' => 'Tên phải là chuỗi ký tự.',
            'last_name.max' => 'Tên không được vượt quá 30 ký tự.',

            // Email
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không đúng định dạng.',
            'email.max' => 'Email không được vượt quá 100 ký tự.',
            'email.unique' => 'Email đã được sử dụng.',

            // Mật khẩu
            'password.required' => 'Vui lòng nhập mật khẩu.',
            'password.string' => 'Mật khẩu phải là chuỗi.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
            'password.min' => 'Mật khẩu phải chứa ít nhất :min ký tự.',
            'password.mixed' => 'Mật khẩu phải bao gồm cả chữ hoa và chữ thường.',
            'password.numbers' => 'Mật khẩu phải chứa ít nhất một số.',
            'password.symbols' => 'Mật khẩu phải chứa ít nhất một ký tự đặc biệt.',
        ];
    }
}
