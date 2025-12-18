<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:20'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'password' => $this->passwordRules(),
        ])->validate();

        return User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),

            // ★★★ 必須カラムに初期値を設定 ★★★
            // プロフィール設定が必須であるため、NOT NULLのデータベース制約を満たす「空文字列」(nullじゃないよ)を設定
            'post_code' => '',
            'address' => '',
            'building_name' => '',
            'profile_image' => '',
            // email_verified_at は設定しないことで自動的に NULL となり、「メール認証」を強制するトリガーとなる。
        ]);
    }
}
