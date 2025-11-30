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
            // name
            'name.required' => 'ユーザー名を入力してください',
            'name.string' => 'ユーザー名は文字列で入力してください。',
            'name.max' => 'ユーザー名は20文字以下で入力してください',

            // email
            'email.required' => 'メールアドレスを入力してください',
            'email.email' => 'メールアドレスは「ユーザー名@ドメイン」形式で入力してください',

            // password
            'password.required' => 'パスワードを入力してください',
            'password.min' => 'パスワードは8文字以上で入力してください',

            // password_confirmation
            'password.confirmed' => 'パスワードと確認用パスワードが一致しません',
        ];
    }
}
