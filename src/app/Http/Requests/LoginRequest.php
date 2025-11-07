<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
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
            // メールアドレス: 必須、メール形式、usersテーブル内でユニークであること
            'email' => ['required', 'string', 'email', 'max:255'],

            // パスワード: 必須、8文字以上、確認用パスワードと一致すること
            'password' => ['required', 'string', 'min:8'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     * （以前のやり取りで定義した日本語属性を反映）
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'email' => 'メールアドレス',
            'password' => 'パスワード',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     * （以前のやり取りで定義したカスタムメッセージを反映）
     *
     * @return array
     */
    public function messages()
    {
        return [
            // email
            'email.required' => 'メールアドレスを入力してください',
            'email.email' => 'メールアドレスは「ユーザー名@ドメイン」形式で入力してください',

            // password
            'password.required' => 'パスワードを入力してください',
            'password.min' => 'パスワードは8文字以上で入力してください',
        ];
    }
}
