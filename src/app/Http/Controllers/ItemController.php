<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Like;
use App\Http\Requests\ExhibitionRequest;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\Condition;
use Illuminate\Support\Facades\Log; // デバッグ用

class ItemController extends Controller
{
    /**
     * コンストラクタ
     * このコントローラー内の全てのアクションは認証済みユーザーのみアクセス可能とする
     */
    public function __construct()
    {
        // 'auth' ミドルウェアを適用し、indexとshowメソッドを除外する
        $this->middleware('auth')->except(['index', 'show']);
    }

    /**
     * ホーム画面 (商品一覧) を表示する
     * 未認証ユーザーもアクセス可能
     */
    public function index(Request $request) // Requestを受け取る
    {
        // 検索キーワードを取得
        $keyword = $request->input('keyword');

        // 基本クエリ: 販売ステータスが未販売（is_sold = false）の商品
        $query = Item::where('is_sold', false);

        // 検索機能の追加 (一般条件として、認証チェックの前に配置)
        if ($keyword) {
            // キーワードが入力されている場合、認証状態に関わらず、商品名で部分一致検索を行う
            $query->where('name', 'LIKE', '%' . $keyword . '%');
        }

        // 認証済みユーザーの場合、自身が出品した商品を除外する条件を追加
        if (Auth::check()) {
            // ログインユーザーのIDを取得し、そのユーザーが出品した商品（user_idが一致するもの）を除外
            $query->where('user_id', '!=', Auth::id()); // ← この行を追加
        }

        // ★★★ 修正箇所: ここでクエリを実行し、結果を $items に代入します ★★★
        // 最新の商品が上に来るように降順で取得し、クエリを実行
        $items = $query->orderBy('created_at', 'desc')
                        ->get();
        // 以前の重複するクエリブロック（Item::where('is_sold', false)->orderBy('created_at', 'desc')->get();）を削除しました。

        // 'items.index' ビューにデータを渡して表示
        return view('items.index', compact('items'));
    }

    /**
     * マイリスト（いいねした商品一覧）を表示する
     * @return \Illuminate\View\View
     */
    public function mylist()
    {
        // ログインユーザーが「いいね」したItemを取得
        // 1. ログインユーザーのIDを取得
        $userId = Auth::id();

        // 2. ユーザーが「いいね」した商品のIDリストを取得
        // Like モデルに user_id と item_id があることを想定
        $likedItemIds = Like::where('user_id', $userId)
                            ->pluck('item_id');

        // 3. 取得したIDリストに基づいて Item を取得
        $items = Item::whereIn('id', $likedItemIds)->get();

        // index.blade.php にマイリストの商品データを渡す
        // indexビュー内で /mylist の場合に 'active' が付くようになっているため、ビューは共通でOK
        return view('items.index', ['items' => $items]);
    }

    /**
     * 商品詳細画面を表示する (重複定義を修正し、いいね情報を追加)
     * 未認証ユーザーもアクセス可能
     *
     * @param Item $item ルーティングのワイルドカードから自動的に取得された商品データ
     * @return \Illuminate\View\View
     */
    public function show(Item $item) // 型ヒントは Item $item の方が推奨されます
    {
        // 💡 N+1問題対策と並び替えを同時に行います。
        // with() ではなく load() を使用して、既に取得された $item にリレーションを追加でロードします。
        $item->load([
            // コメントを新しい順（created_at の降順）で取得し、同時にユーザー情報もロード
            'comments' => function ($query) {
                $query->orderBy('created_at', 'desc')->with('user');
            },
            // いいねしたユーザー（likedUsers）のリレーションもロードしておくと効率的です
            'likedUsers',
        ]);

        // ログインユーザーがいいねしているかどうかの確認
        $isLiked = false;
        if (Auth::check()) {
            // ロード済みの likedUsers リレーションから、現在のユーザーIDが存在するかチェック
            // 既にリレーションがロードされているため、DBクエリは発生しません
            $isLiked = $item->likedUsers->contains('id', Auth::id());
        }

        // いいね合計数の取得 (リレーションの count() メソッドはロードされているコレクションに対して実行されます)
        $likeCount = $item->likedUsers->count();

        // 商品詳細ビューにデータを渡して表示
        return view('items.show', compact('item', 'isLiked', 'likeCount'));
    }

    /**
     * 商品出品フォームを表示します。
     * BadMethodCallExceptionが発生していたのは、このメソッドがなかったためです。
     *
     * @return View
     */
    public function create(): View
    {
        // Conditionモデルから全ての商品状態リストを取得
        $conditions = Condition::all();

        // ★修正: $item に加え、conditions もビューに渡します★
        $item = null;

        return view('new_items', compact('item', 'conditions'));
    }

    /**
     * 新しい商品情報を受け取り、バリデーションとデータベースへの保存を行います。（POST /sell）
     * ★brandの保存とcategoriesの多対多対応を修正しました★
     *
     * @param ExhibitionRequest $request フォームリクエスト（バリデーション済みデータを含む）
     * @return RedirectResponse
     */
    public function store(ExhibitionRequest $request): RedirectResponse
    {
        // 1. バリデーション済みのデータを取得
        $validatedData = $request->validated();

        // トランザクション開始：商品データとカテゴリ中間テーブルへの保存をセットで行う
        DB::beginTransaction();

        try {
            // 2. 画像ファイルの保存
            $imagePath = null;
            if ($request->hasFile('image_path')) {
                // 'public/items' ディスクにファイルを保存し、パスを返します。
                $imagePath = $request->file('image_path')->store('items', 'public');
            }

            // 3. データベースへの保存処理 (Item モデルの利用)
            // category_idは多対多になったため、itemsテーブル自体には保存しませんが、
            // requestから値を取得して後で中間テーブルに保存します。
            $item = Item::create([
                'user_id' => Auth::id(), // ログインユーザーのID
                'name' => $validatedData['name'],
                'description' => $validatedData['description'],
                'price' => $validatedData['price'],
                'condition_id' => $validatedData['condition_id'], // condition_idは残る
                'brand' => $validatedData['brand'] ?? null, // ★修正: brand (テキスト) を保存★
                'image_path' => $imagePath, // 保存されたファイルパス
                'is_sold' => false, // 未販売
            ]);

            // 4. カテゴリ中間テーブルへの登録 (多対多対応)
            // ★★★ 修正箇所: category_id ではなく categories (IDの配列) を使用 ★★★
            if (isset($validatedData['categories'])) {
                // $validatedData['categories'] はIDの配列なので、attach() で一度に中間テーブルに挿入
                $item->categories()->attach($validatedData['categories']);
            }

            DB::commit(); // トランザクションをコミット

            // 5. 処理成功後、新しく作成された商品詳細ページにリダイレクト
            return redirect()->route('mypage.index', ['tab' => 'listed'])
                ->with('success', '商品が正常に出品されました！');

        } catch (\Exception $e) {
            DB::rollBack(); // エラーが発生した場合、トランザクションをロールバック
            // 画像ファイルが保存されていた場合、ロールバック時に削除する
            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }

            // ログに出力し、エラーメッセージと共にリダイレクト
            \Log::error('商品出品中にエラーが発生しました: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => '商品の出品に失敗しました。時間をおいて再度お試しください。'])
                ->with('error', '商品の出品に失敗しました。');
        }
    }
}
