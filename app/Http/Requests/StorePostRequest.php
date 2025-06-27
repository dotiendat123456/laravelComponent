<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:100',
            'description' => 'nullable|string|max:200',
            'content' => 'nullable|string',
            'publish_date' => 'nullable|date',
            'thumbnail' => 'nullable|image|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Tiêu đề không được để trống.',
            'title.string' => 'Tiêu đề phải là chuỗi.',
            'title.max' => 'Tiêu đề không được vượt quá 100 ký tự.',

            'description.string' => 'Mô tả phải là chuỗi.',
            'description.max' => 'Mô tả không được vượt quá 200 ký tự.',

            'content.string' => 'Nội dung phải là chuỗi.',

            'publish_date.date' => 'Ngày đăng phải đúng định dạng ngày.',

            'thumbnail.image' => 'Tệp tải lên phải là hình ảnh.',
            'thumbnail.max' => 'Ảnh không được lớn hơn 2MB.',
        ];
    }
}
