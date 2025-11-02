<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PurchaseRequest extends FormRequest
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
        // 支払い方法選択
        // payment_method: 選択必須
        'payment_method' => [
            'required'
        ],

        // 送付先住所用
        // shipping_post_code: 必須、郵便番号形式（ハイフンあり8文字）
        'shipping_post_code' => [
            'required',
            'regex:/^\d{3}-\d{4}$/', // XXX-YYYY 形式
        ],

        // shipping_address: 必須、文字列、最大255文字
        'shipping_address' => [
            'required',
            'string',
            'max:255',
        ],

        // shipping_building_name: 任意、文字列、最大255文字
        'shipping_building_name' => [
            'nullable', // 建物名は任意
            'string',
            'max:255',
        ],
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'payment_method' => '支払い方法',
            'shipping_post_code' => '送付先郵便番号',
            'shipping_address' => '送付先住所',
            'shipping_building_name' => '送付先建物名',
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
            // 支払い方法選択
            // payment_method: 選択必須
            'payment_method.required' => '支払い方法を選択してください',

            // 送付先郵便番号
            'shipping_post_code.required' => '送付先郵便番号を入力してください',
            'shipping_post_code.regex' => '送付先郵便番号はハイフンを含め、XXX-YYYYの形式で入力してください',

            // 送付先住所
            'shipping_address.required' => '送付先住所を入力してください',
            'shipping_address.max' => '送付先住所は255文字以内で入力してください',
        ];
    }
}
