<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePostRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'title' => 'required|string|max:100',
            'description' => 'nullable|string|max:200',
            'content' => 'nullable|string',
            'publish_date' => 'nullable|date',
            'thumbnail' => 'nullable|image|max:2048',
        ];
        if ($this->user()->isAdmin()) {
            $rules['status'] = ['required', 'in:0,1,2'];
        }

        return $rules;
    }


    public function messages(): array
    {
        return [
            'title.required' => 'Vui lòng nhập tiêu đề.',
            'title.string' => 'Tiêu đề phải là chuỗi.',
            'title.max' => 'Tiêu đề không được vượt quá 100 ký tự.',

            'description.string' => 'Mô tả phải là chuỗi.',
            'description.max' => 'Mô tả không được vượt quá 200 ký tự.',

            'content.string' => 'Nội dung phải là chuỗi.',

            'publish_date.date' => 'Ngày đăng không hợp lệ.',

            'thumbnail.image' => 'Thumbnail phải là định dạng ảnh.',
            'thumbnail.max' => 'Thumbnail không được vượt quá 2MB.',

            'status.in' => 'Trạng thái bài viết không hợp lệ.',
        ];
    }
}
