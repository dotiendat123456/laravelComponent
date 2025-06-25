<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

use function Laravel\Prompts\confirm;

class RegisterUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // return false;
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'first_name' => 'required|string|max:30',
            'last_name' => 'required|string|max:30',
            'email' => 'required|email:rfc,dns|max:100|unique:users,email',
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
            ],
        ];
    }

    public function messages()
    {
        return [
            'first_name.required' => 'Vui lòng nhập họ.',
            'first_name.string' => 'Họ phải là chuỗi.',
            'first_name.max' => 'Họ không được vượt quá 30 ký tự.',

            'last_name.required' => 'Vui lòng nhập tên.',
            'last_name.string' => 'Tên phải là chuỗi.',
            'last_name.max' => 'Tên không được vượt quá 30 ký tự.',

            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không hợp lệ hoặc không tồn tại.',
            'email.max' => 'Email không được vượt quá 100 ký tự.',
            'email.unique' => 'Email đã tồn tại.',

            'password.required' => 'Vui lòng nhập mật khẩu.',
            'password.string' => 'Mật khẩu phải là chuỗi.',
            'password.min' => 'Mật khẩu phải có ít nhất 8 ký tự.',
            'password.mixed' => 'Mật khẩu phải chứa cả chữ hoa và chữ thường.',
            'password.numbers' => 'Mật khẩu phải chứa ít nhất một số.',
            'password.symbols' => 'Mật khẩu phải chứa ít nhất một ký tự đặc biệt.',
            'password.confirmed' => 'Mật khẩu xác nhận không khớp.',




            //Nếu set thông báo rồi thì nó sẽ hiển thị bằng tiếng việt, còn mặc định k set thì nó hiển thị bằng tiếng Anh
        ];
    }
}
