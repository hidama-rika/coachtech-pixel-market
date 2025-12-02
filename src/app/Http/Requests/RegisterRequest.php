<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
            // ユーザー名
            'name' => ['required', 'string', 'max:20'],

            // メールアドレス: 必須、メール形式、usersテーブル内でユニークであること
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],

            // パスワード: 必須、8文字以上、確認用パスワードと一致すること
            'password' => ['required', 'string', 'min:8', 'confirmed'],

            // 確認用パスワード: 厳密にルールを記述する場合は 'password_confirmation' も定義。
            'password_confirmation' => ['required', 'string', 'min:8'],
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
            'name' => 'ユーザー名',
            'email' => 'メールアドレス',
            'password' => 'パスワード',
            'password_confirmation' => '確認用パスワード',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     *
     * @return array
     */
    public function messages()
{
    return [
        // name (仕様書 FN004 - 1. お名前を入力してください)
        'name.required' => 'お名前を入力してください',
        'name.string' => 'ユーザー名は文字列で入力してください。',
        'name.max' => 'ユーザー名は20文字以下で入力してください',

        // email (仕様書 FN004 - 2. メールアドレスを入力してください / 3. メール形式)
        'email.required' => 'メールアドレスを入力してください',
        'email.email' => 'メールアドレスはメール形式で入力してください',

        // password (仕様書 FN004 - 4. パスワードを入力してください / 2.1. パスワードは8文字以上で入力してください)
        'password.required' => 'パスワードを入力してください',
        'password.min' => 'パスワードは8文字以上で入力してください',

        // password_confirmation (messagesでpassword_confirmationを参照するには、rulesに明記が必要)
        'password_confirmation.required' => 'パスワードを入力してください', // FN004 4.と合わせて「未入力」のメッセージを統一
        'password_confirmation.min' => 'パスワードは8文字以上で入力してください', // FN004 2.1. と合わせて「8文字未満」のメッセージを統一

        // password.confirmed (仕様書 FN004 - 3.1. パスワードと一致しません)
        'password.confirmed' => 'パスワードと一致しません',
    ];
}
}
