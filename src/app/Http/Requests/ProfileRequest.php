<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth; // Auth::check() のために追加

class ProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // 認証済みユーザーであればアクセスを許可
        // ★修正点1: true ではなく Auth::check() を使用して認証を確認
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            // ユーザー名 (User Name) のバリデーション
            'name' => [
                'required',
                'string',
                'max:20',
            ],

            // プロフィール画像 (Profile Image) のバリデーション
            'profile_image' => [
                'nullable',
                'image',
                'mimes:jpeg,png',
                // 'max:2048', // 2MBの上限を追加しておくと安全です
            ],

            // プロフィール住所用
            // post_code: 必須、郵便番号形式（ハイフンあり8文字）
            'post_code' => [
                'required',
                'regex:/^\d{3}-\d{4}$/', // XXX-YYYY 形式
            ],

            // address: 必須、文字列、最大255文字
            'address' => [
                'required',
                'string',
                'max:255',
            ],

            // building_name: 任意、文字列、最大255文字
            'building_name' => [
                'nullable', // 建物名は任意
                'string',
                'max:255',
            ],
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
            // ユーザー名に関するメッセージ
            'name.required' => 'ユーザー名を入力してください。',
            'name.string' => 'ユーザー名は文字列で入力してください。',// 👈 テーブルのデータ型に合わせて追加
            'name.max' => 'ユーザー名は20文字以内で入力してください。',

            // プロフィール画像に関するメッセージ
            'profile_image.image' => 'プロフィール画像はJPEG、またはPNG形式でアップロードしてください。',
            'profile_image.mimes' => 'プロフィール画像はJPEG、またはPNG形式でアップロードしてください。',

            // 郵便番号
            'post_code.required' => '郵便番号を入力してください',
            'post_code.regex' => '郵便番号はハイフンを含め、XXX-YYYYの形式で入力してください',

            // 住所
            'address.required' => '住所を入力してください',
            'address.string' => '住所は文字列で入力してください', // 👈 テーブルのデータ型に合わせて追加
            'address.max' => '住所は255文字以内で入力してください',

            // building_nameに関するメッセージ
            'building_name.max' => '建物名は255文字以内で入力してください。',
        ];
    }
}
