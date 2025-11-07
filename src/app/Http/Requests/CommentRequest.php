<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CommentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            // comment: 必須 (required)、文字列 (string)、最大255文字 (max:255)
            'comment' => ['required', 'string', 'max:255'],
            // item_id: コメント対象の商品ID。実アプリケーションでは必須。
            // 今回のルール画像にはないが、コメント対象を特定するために追加しておくのが一般的らしい。
            // 'item_id' => ['required', 'integer', 'exists:items,id'],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'comment.required' => 'コメント内容を入力してください。',
            'comment.string' => 'コメント内容は文字列で入力してください。',
            'comment.max' => 'コメント内容は255文字以内で入力してください。',
        ];
    }
}
