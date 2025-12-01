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
        // 1. URLのキーワードを取得 (コンテンツのフィルタリングに使用)
        $keywordForFiltering = $request->input('keyword');

        // 2. 検索状態の保持（Persistence Management）
        //    URLに 'keyword' パラメータがある場合（検索実行またはタブによる復元・クリア）はセッションを更新
        if ($request->has('keyword')) {
            // キーワードが空（""）の場合もセッションを空で上書きし、検索状態をクリアする
            session(['last_search_keyword' => $keywordForFiltering ?? '']);
        }
        // URLにキーワードがない場合、セッションは更新しない
        // （他のタブから戻る時の復元用として、前回の検索キーワードを保持し続けます）

        // 3. 基本クエリの組み立て: 「非表示にする」という要件をなくし、「すべての商品を表示する（ただし、自分の出品した商品は除外）」というロジックに変更
        // 【修正後】 is_sold の条件を削除し、すべてのアイテムを対象とする
        $query = Item::query(); // Itemモデルのクエリビルダを開始

        // 4. 検索機能の追加 (コンテンツのフィルタリング)
        //    URLにキーワードがある場合のみ、クエリに検索条件を追加
        if (!empty($keywordForFiltering)) {
            $query->where('name', 'LIKE', '%' . $keywordForFiltering . '%');
        }

        // 5. 認証済みユーザーの場合、自身が出品した商品を除外する条件を追加
        if (Auth::check()) {
            // ログインユーザーのIDを取得し、そのユーザーが出品した商品（user_idが一致するもの）を除外
            $query->where('user_id', '!=', Auth::id());
        }
        // ----------------------------------------------------------------------------------
        // ⚠️ 注意: 未認証ユーザーは is_sold=true/false にかかわらずすべての商品が見えるようになります。
        //   もし未認証ユーザーには is_sold=true の商品も見せたくない場合は、
        //   is_sold = false の条件をここに含める必要があります。
        //   今回は SOLDOUT 表示が目的なので、このまま進めます。
        // ----------------------------------------------------------------------------------

        // 6. クエリの実行
        $items = $query->orderBy('created_at', 'desc')->get();

        // 7. Viewへ渡す
        //    フォームのvalueやタブリンクのパラメータとして、セッションに保持された最新のキーワードを渡す。
        $lastKeywordForView = session('last_search_keyword') ?? '';

        // 'index' ビューにデータを渡して表示
        return view('items.index', [
            'items' => $items,
            // フォームとタブリンクの復元に使用
            'lastKeyword' => $lastKeywordForView
        ]);
    }

    /**
     * マイリスト（いいねした商品一覧）を表示する
     * @return \Illuminate\View\View
     */
    public function mylist(Request $request)
    {
        // ログインユーザーが「いいね」したItemを取得
        // 1. ログインユーザーのIDを取得
        $userId = Auth::id();

        // ★★★ 検索状態の保持ロジックを追加 ★★★
        // 2. indexと同じく、URLのキーワードを取得
        $keywordForFiltering = $request->input('keyword');

        // 3. indexと同じく、URLに 'keyword' があればセッションを更新
        if ($request->has('keyword')) {
            session(['last_search_keyword' => $keywordForFiltering ?? '']);
        }
        // ★★★ 検索状態の保持ロジックはここまで ★★★

        // 4. 基本クエリ: ログインユーザーがいいねした Item の ID リストを取得
        // Like モデルに user_id と item_id があることを想定
        $likedItemIds = Like::where('user_id', $userId)
                            ->pluck('item_id');

        // ★★★ 修正ポイント: クエリビルダを $query に代入し、販売済みを除外 ★★★
        $query = Item::whereIn('id', $likedItemIds)
                    ->where('is_sold', false);

        // 5. 検索機能の追加 (コンテンツのフィルタリング)
        //    URLにキーワードがある場合のみ、クエリに検索条件を追加
        if (!empty($keywordForFiltering)) {
            // ★ $query に対して where 条件を追加 ★
            $query->where('name', 'LIKE', '%' . $keywordForFiltering . '%');
        }

        // 6. クエリの実行
        // ★ 最後に $query に対して orderBy と get() を適用して結果を取得 ★
        $items = $query->orderBy('created_at', 'desc')->get();

        // 7. Viewへ渡す
        $lastKeywordForView = session('last_search_keyword') ?? '';

        // index.blade.php にマイリストの商品データとキーワードデータを渡す
        return view('items.index', [
            'items' => $items,
            // ★ 変数名を $lastKeywordForView に統一（$lastKeyword のままでも動作するが、可読性のため） ★
            'lastKeyword' => $lastKeywordForView
        ]);
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

        $lastKeywordForView = session('last_search_keyword') ?? '';

        // 商品詳細ビューにデータを渡して表示
        return view('items.show', [
            'item' => $item,
            'isLiked' => $isLiked,
            'likeCount' => $likeCount,
            'lastKeyword' => $lastKeywordForView
]);
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

        $lastKeywordForView = session('last_search_keyword') ?? '';

        return view('new_items', [
            'item' => $item,
            'conditions' => $conditions,
            'lastKeyword' => $lastKeywordForView
        ]);
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
            return redirect()->route('mypage', ['tab' => 'listed'])
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
