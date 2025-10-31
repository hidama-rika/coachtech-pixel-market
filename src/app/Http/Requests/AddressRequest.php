<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddressRequest extends FormRequest
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
        // shipping_post_code: 必須、郵便番号形式（ハイフンあり8文字）
        'shipping_post_code' => [
            'required',
            'regex:/^\d{3}-\d{4}$/', // XXX-YYYY 形式
        ],

        // shipping_address: 必須、文字列、最大255文字（purchasesテーブル定義より）
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
            'shipping_post_code' => '郵便番号',
            'shipping_address' => '住所',
            'shipping_building_name' => '建物名',
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
            // 郵便番号
            'shipping_post_code.required' => '郵便番号を入力してください',
            'shipping_post_code.regex' => '郵便番号はハイフンを含め、XXX-YYYYの形式で入力してください',

            // 住所
            'shipping_address.required' => '住所を入力してください',
            'shipping_address.max' => '住所は255文字以内で入力してください',
        ];
    }
}
