<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class PurchaseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // ログイン済みのユーザーのみが購入リクエストを許可されるように変更
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
            // 商品ID
            'item_id' => 'required|exists:items,id',

            // 支払い方法選択
            // payment_method_id: 選択必須
            'payment_method_id' => [ // 修正: name="payment_method_id" に合わせる
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
            'shipping_building' => [
                'nullable', // 建物名は任意
                'string',
                'max:255',
            ],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'item_id' => '商品ID',
            'payment_method_id' => '支払い方法',
            'shipping_post_code' => '送付先郵便番号',
            'shipping_address' => '送付先住所',
            'shipping_building' => '送付先建物名',
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
            'item_id.required' => '購入対象の商品が指定されていません。',
            'item_id.exists' => '購入対象の商品が見つかりません。',

            // 支払い方法選択
            // payment_method_id: 選択必須
            'payment_method_id.required' => '支払い方法を選択してください',

            // 送付先郵便番号
            'shipping_post_code.required' => '送付先郵便番号を入力してください',
            'shipping_post_code.regex' => '送付先郵便番号はハイフンを含め、XXX-YYYYの形式で入力してください',

            // 送付先住所
            'shipping_address.required' => '送付先住所を入力してください',
            'shipping_address.max' => '送付先住所は255文字以内で入力してください',

            // 送付先建物名
            'shipping_building.max' => '送付先建物名は255文字以内で入力してください',
        ];
    }

    /**
     * バリデーション失敗時のリダイレクト先を明示的に定義する。
     * これにより、item_idが失われたことによる MethodNotAllowedHttpException を回避する。
     *
     * @return string
     */
    protected function getRedirectUrl()
    {
        // リクエストから item_id を取得し、購入画面に戻るルートを明示的に指定
        // item_id がリクエストに含まれない場合は、購入画面に戻れませんが、
        // requiredルールにより通常はここに来る前に item_id が存在します。
        $item_id = $this->input('item_id');

        if ($item_id) {
            // new_purchases ルート（GET /purchase/{item_id}）にリダイレクト
            return route('new_purchases', ['item_id' => $item_id]);
        }

        // item_idがない場合は、安全なitems.indexに戻す
        return route('items.index');
    }
}
