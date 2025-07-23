<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCommentRequest extends FormRequest
{
    /**
     * Xác định user có được phép gửi request này không.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Định nghĩa các rule để validate dữ liệu gửi lên.
     */
    public function rules(): array
    {
        return [
            'content' => 'required|string|max:5000',
            'parent_id' => 'nullable|exists:post_comments,id',
        ];
    }

    /**
     * Thông báo lỗi tuỳ chỉnh (optional nhưng nên có).
     */
    public function messages(): array
    {
        return [
            'content.required' => 'Nội dung bình luận không được để trống.',
            'content.string' => 'Nội dung bình luận phải là chuỗi.',
            'content.max' => 'Nội dung bình luận không được vượt quá 5000 ký tự.',
            'parent_id.exists' => 'Bình luận cha không tồn tại.',
        ];
    }
}
