<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExhibitionRequest extends FormRequest
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
            // 商品名: 入力必須
            'name' => ['required', 'string', 'max:255'],

            // 商品説明: 入力必須、最大文字数255
            'description' => ['required', 'string', 'max:255'],

            // 商品画像: アップロード必須、拡張子がjpegもしくはpng (image/mimesルールはfileを含意)
            // mimesルールは小文字で指定することが推奨されます。
            'image_path' => ['required', 'file', 'mimes:jpeg,png', 'max:5120'], // max:5120 (5MB)を実用的なサイズとして追加

            // 商品のカテゴリー: 選択必須、categoriesテーブルに存在するIDであること
            'category_id' => ['required', 'integer', 'exists:categories,id'],

            // 商品の状態: 選択必須、conditionsテーブルに存在するIDであること
            'condition_id' => ['required', 'integer', 'exists:conditions,id'],

            // 商品価格: 入力必須、数値型、0円以上
            // 価格は整数または小数を許容するnumericを使用し、min:0で0円以上を強制します。
            'price' => ['required', 'numeric', 'min:0'],
        ];
    }

    /**
     * ルール属性の表示名を定義します。
     * (バリデーションエラーメッセージ内で属性名を日本語化するために使用します)
     *
     * @return array
     */
    public function attributes(): array
    {
        return [
            'name' => '商品名',
            'description' => '商品説明',
            'image_path' => '商品画像',
            'category_id' => '商品のカテゴリー',
            'condition_id' => '商品の状態',
            'price' => '商品価格',
        ];
    }

    /**
     * バリデーションエラーメッセージを定義します。
     *
     * @return array
     */
    public function messages()
    {
        return [
            'name.required' => '商品名を入力してください。',
            'name.string' => '商品名は文字列で入力してください。',
            'name.max' => '商品名は255文字以内で入力してください。',

            'description.required' => '商品説明は必ず入力してください。',
            'description.string' => '商品説明は文字列で入力してください。',
            'description.max' => '商品説明は255文字以内で入力してください。',

            'image_path.required' => '商品画像をアップロードしてください。',
            'image_path.mimes' => '商品画像の拡張子はJPEGまたはPNG形式を選択してください。',

            'category_id.required' => '商品のカテゴリーを選択してください。',

            'condition_id.required' => '商品の状態を選択してください。',

            'price.required' => '商品価格を入力してください。',
            'price.numeric' => '商品価格は数値で入力してください。',
            'price.min' => '商品価格は0円以上の値を入力してください。',
        ];
    }
}
