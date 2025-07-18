<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AdminStorePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:100',
            'description' => 'required|string|max:200',
            'content' => 'required|string',
            'publish_date' => 'required|date|after:today',
            'thumbnail' => 'required|image|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Tiêu đề không được để trống.',
            'title.string' => 'Tiêu đề phải là chuỗi.',
            'title.max' => 'Tiêu đề không được vượt quá 100 ký tự.',

            'description.required' => 'Mô tả không được để trống.',
            'description.string' => 'Mô tả phải là chuỗi.',
            'description.max' => 'Mô tả không được vượt quá 200 ký tự.',

            'content.required' => 'Nội dung không được để trống.',
            'content.string' => 'Nội dung phải là chuỗi.',

            'publish_date.required' => 'Ngày đăng không được để trống.',
            'publish_date.date' => 'Ngày đăng phải đúng định dạng ngày.',
            'publish_date.after' => 'Ngày đăng phải lớn hơn ngày hiện tại.',

            'thumbnail.required' => 'Vui lòng tải lên thumbnail.',
            'thumbnail.image' => 'Tệp tải lên phải là hình ảnh.',
            'thumbnail.max' => 'Ảnh không được lớn hơn 2MB.',
        ];
    }
}
