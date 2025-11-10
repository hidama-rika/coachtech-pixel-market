<?php

namespace App\Http\Controllers;

use App\Models\User; // Userモデルをインポート
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; //追加
use Illuminate\Support\Facades\Storage; // ファイル操作（画像アップロード）に必要
use Illuminate\Support\Facades\DB; // トランザクション処理に必要
use App\Http\Requests\ProfileRequest; // バリデーションにフォームリクエストを使用

class ProfileController extends Controller
{
    /**
     * プロフィール編集画面を表示し、ユーザー情報を反映する
     * 新規登録フロー（profile_edit）と通常の編集画面を兼ねる
     */
    public function edit(Request $request)
    {
        // 認証済みユーザー情報を取得
        $user = Auth::user();

        // ビューに渡す
        return view('profile_edit', [
            'user' => $user,
        ]);
    }

    /**
     * プロフィール更新処理を実行する
     * 新規登録フローの profile_edit 完了処理を兼ねる
     */
    public function update(ProfileRequest $request)
    {
        // 認証済みユーザー情報を取得
        $user = Auth::user();

        // 更新前のプロフィール未登録状態をチェック (新規登録フローからの遷移かを判定するため)
        $wasUnregistered = $user->isProfileUnregistered();

        // バリデーション済みのデータを取得
        $validated = $request->validated();

        // データベースとファイルシステムを跨ぐためトランザクションを開始
        DB::beginTransaction();

        try {
            // ------------------------------------
            // 1. 画像ファイルの処理
            // ------------------------------------
            if ($request->hasFile('profile_image')) {
                // 古い画像ファイルがあれば削除
                if ($user->profile_image) {
                    Storage::delete('public/' . $user->profile_image);
                }

                // 新しい画像を保存し、保存先パスを取得
                // ファイル名はハッシュ化され、'public/profile_images' ディレクトリに保存
                $path = $request->file('profile_image')->store('profile_images', 'public');
                $user->profile_image = $path; // storage/app/publicからの相対パスを保存
            }

            // ------------------------------------
            // 2. ユーザーデータの更新
            // ------------------------------------

            // Bladeで定義された input name に合わせてデータを処理
            $user->name = $validated['name'];
            $user->post_code = $validated['post_code'];
            $user->address = $validated['address'];
            // 建物名は任意項目なので、データがなければnullを許容
            $user->building_name = $validated['building_name'] ?? null;

            // ユーザーモデルを保存（更新）
            $user->save();

            // トランザクションをコミット
            DB::commit();

            // ------------------------------------
            // 3. リダイレクト先の決定
            // ------------------------------------
            if ($wasUnregistered) {
                // 新規登録フローからの完了時 (profile_edit完了)
                $message = 'プロフィール登録が完了しました！サービスを始めましょう！';
                // 通常はmypageのメイン画面へリダイレクト
                return redirect()->route('mypage.index')->with('success', $message);
            } else {
                // 通常のプロフィール更新時
                $message = 'プロフィールが正常に更新されました。';
                // マイページ（ルート名 'mypage.index' を想定）へリダイレクト
                return redirect()->route('mypage.index')->with('success', $message);
            }

        } catch (\Exception $exception) {
            // エラーが発生した場合、トランザクションをロールバック
            DB::rollBack();
            // エラーをログに出力
            \Log::error('プロフィール更新エラー: ' . $exception->getMessage());

            // ユーザーをフォームに戻し、エラーメッセージを表示
            return redirect()->back()->withInput()->withErrors(['update_error' => 'プロフィールの更新に失敗しました。時間をおいて再度お試しください。']);
        }
    }
}
