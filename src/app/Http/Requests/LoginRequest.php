<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator; // この行がある
use Illuminate\Support\Facades\Auth;

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
     * バリデーションが実行された後に、追加の処理（認証チェック）を実行します。
     *
     * @param \Illuminate\Validation\Validator
     * @return void
     */
    public function withValidator(Validator $validator)
    {
        // 基本的なバリデーションルール（required, email, min:8など）が通過した後に実行
        $validator->after(function ($validator) {

            // 既にエラーがある場合（例：emailの形式が不正など）は、認証チェックをスキップ
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            // ユーザー認証を試みる
            // Auth::attempt()がfalseを返した場合、認証失敗
            if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
                // 認証に失敗した場合、'password'フィールドにエラーを手動で追加
                $validator->errors()->add(
                    'password',
                    'ログイン情報が登録されていません。'
                );
            }
        });
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

            // withValidatorで追加するエラーメッセージは、ここでは定義しない。
            // （withValidator内で直接メッセージを指定）
        ];
    }
}
