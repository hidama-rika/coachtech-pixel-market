<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COACHTECH-商品出品画面</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/new_items.css')}}">
</head>

<body>

    <header class="header-shadow header">
        <div class="header-content">

            <a class="logo" href="/">
                <img src="{{ asset('storage/img/Vector (3).png') }}" alt="logoアイコン" class="icon logo-icon-img">
                <img src="{{ asset('storage/img/Group (2).png') }}" alt="logoテキスト" class="icon logo-text-img">
            </a>

            <div class="search-form-container">
                <input type="text" class="search-input" placeholder="なにをお探しですか？">
            </div>

            <nav class="nav-menu">

                <form method="POST" action="/logout">
                    @csrf
                    <button type="submit" class="nav-button logout-button">
                        <span class="nav-text">ログアウト</span>
                    </button>
                </form>

                <a href="/mypage" class="nav-button mypage-button">
                    <span class="nav-text">マイページ</span>
                </a>

                <a href="/sell" class="nav-button sell-button">
                    <span class="sell-text">出品</span>
                </a>

            </nav>

        </div>
    </header>

    <main class="new_items-container">
        <div class="new_items-form-container">

            <div class="form-title">商品の出品</div>

            <form class="form" action="/sell" enctype="multipart/form-data" method="post" novalidate>
                @csrf

                {{-- 商品画像アップロードのセクション --}}
                <div class="form-group image-upload-group">
                    <label for="image_path">商品画像</label>

                    <div class="image-upload-area">
                        <div class="image-preview-area">
                            <!-- プレビュー画像要素 -->
                            <img
                                id="item-image-preview"
                                src="{{ $item?->image_path ?? '' }}"
                                alt="商品画像プレビュー"
                                class="item-image-preview"
                            >
                        </div>

                        <div class="image-upload-button-area">
                            <!-- ボタンとファイルインプットの連携 -->
                            <button type="button" class="image-select-button" id="image-select-btn-item">
                                画像を選択する
                            </button>

                            <!-- name="item_image" の input: 常に非表示 -->
                            <!-- 商品出品用として name を変更 -->
                            <input type="file" id="image-upload-item" name="image_path" accept="image/*" style="display: none;">
                        </div>
                    </div>
                    <p class="new_items-form__error-message">
                        @error('image_path')
                        {{ $message }}
                        @enderror
                    </p>
                </div>


                {{-- 商品の詳細 --}}
                <div class="section-title">商品の詳細</div>

                {{-- カテゴリー選択のセクションを追加 --}}
                <div class="category-section">
                    <label class="category-label">カテゴリー</label>
                    <div class="category-tags-container">
                        {{-- 【重要】本来は $categories のような変数を使ってDBのカテゴリーをループで表示します。 --}}

                        @php
                            // カテゴリーリストの例 (実際はCategoryモデル::all()などで取得してください)
                            $categoryList = [
                                1 => 'ファッション', 2 => '家電', 3 => 'インテリア',
                                4 => 'レディース', 5 => 'メンズ', 6 => 'コスメ',
                                7 => '本', 8 => 'ゲーム', 9 => 'スポーツ',
                                10 => 'キッチン', 11 => 'ハンドメイド', 12 => 'アクセサリー',
                                13 => 'おもちゃ', 14 => 'ベビー・キッズ',
                            ];
                        @endphp

                        @foreach ($categoryList as $id => $name)
                            <label class="tag-checkbox-label">
                                {{-- name="categories[]" と value="{{ $id }}" を使用して複数IDを送信 --}}
                                {{-- old() 関数で以前選択した値を保持 --}}
                                <input type="checkbox" name="categories[]" value="{{ $id }}" class="tag-checkbox"
                                    {{ in_array($id, old('categories', [])) ? 'checked' : '' }}
                                >
                                <span class="tag-text">{{ $name }}</span>
                            </label>
                        @endforeach
                    </div>
                    <p class="new_items-form__error-message">
                        @error('categories')
                        {{ $message }}
                        @enderror
                    </p>
                </div>

                {{-- 商品の状態 --}}
                <div class="form-group">
                    <label for="condition">商品の状態</label>

                    {{-- ❗ フォーム送信用の隠し入力フィールド ❗ --}}
                    {{-- ✅ 修正済み: $selectedConditionId ?? '' で未定義エラーを回避 --}}
                    <input type="hidden" id="condition_input" name="condition_id" value="{{ old('condition_id', $selectedConditionId ?? '') }}" />

                    {{-- ❗ カスタムドロップダウンの表示エリア ❗ --}}
                    <div id="custom-condition-select" class="form-control custom-select-control">
                        <span id="selected-condition-name">
                            {{-- 初期表示テキスト: IDがあればその名前、なければ「選択してください」を表示 --}}
                            @php
                                // $selectedConditionIdが未定義でもエラーにならないように ?? null を使用して安全化
                                $currentConditionId = $selectedConditionId ?? null;
                                $selectedCondition = null;

                                // $conditionsの存在とIDの存在の両方をチェックして検索
                                if (isset($conditions) && $currentConditionId !== null) {
                                    $selectedCondition = $conditions->firstWhere('id', $currentConditionId);
                                }
                            @endphp
                            {{-- ✅ 修正済み: 事前に定義した $selectedCondition 変数を使用することで、未定義エラーを回避 --}}
                            @if($selectedCondition)
                                {{ $selectedCondition->name }}
                            @else
                                選択してください
                            @endif
                        </span>
                    </div>

                    {{-- ❗ ドロップダウンのリスト ❗ --}}
                    <ul id="condition-options" class="custom-select-options condition-options-list">
                        {{-- $conditionsが存在する場合のみループを実行することで安全化 --}}
                        @if(isset($conditions))
                            @foreach ($conditions as $condition)
                                {{-- 選択肢のliタグのdata-value属性に、DBのIDを設定する --}}
                                <li data-value="{{ $condition->id }}" class="custom-select-options-text">
                                    {{ $condition->name }}
                                </li>
                            @endforeach
                        @endif
                    </ul>

                    <p class="new_items-form__error-message">
                        @error('condition_id')
                        {{ $message }}
                        @enderror
                    </p>
                </div>

                {{-- 商品名と説明 --}}
                <div class="section-title">商品名と説明</div>

                {{-- 商品名 --}}
                <div class="form-group">
                    <label for="items">商品名</label>
                    <input id="items" type="text" class="form-control" name="name" required placeholder="商品名を入力" value="{{ old('name') }}">
                    <p class="new_items-form__error-message">
                        @error('name')
                        {{ $message }}
                        @enderror
                    </p>
                </div>

                {{-- ブランド名 --}}
                <div class="form-group">
                    <label for="brands">ブランド名</label>
                    <input id="brands" type="text" class="form-control" name="brands" required placeholder="ブランド名を入力" value="{{ old('brands') }}">
                    <p class="new_items-form__error-message">
                        @error('brands')
                        {{ $message }}
                        @enderror
                    </p>
                </div>

                {{-- 商品の説明 --}}
                <div class="form-group">
                    <label for="description">商品の説明</label>
                    <textarea id="description" class="form-control" name="description" required placeholder="商品の説明を入力" value="{{ old('description') }}"></textarea>
                    <p class="new_items-form__error-message">
                        @error('description')
                        {{ $message }}
                        @enderror
                    </p>
                </div>

                {{-- 販売価格 --}}
                <div class="form-group">
                    <label for="price">販売価格</label>
                    <input id="price" type="text" class="form-control" name="price" required placeholder="￥" value="{{ old('price') }}">
                    <p class="new_items-form__error-message">
                        @error('price')
                        {{ $message }}
                        @enderror
                    </p>
                </div>

                {{-- 出品するボタン --}}
                <button type="submit" class="sell-btn">
                    出品する
                </button>

            </form>

        </div>

    </main>

    <!-- jQueryは使用せず、素のJavaScriptで実装します -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // ===========================================
            // 1. 商品画像プレビュー機能
            // ===========================================
            const imageSelectBtn = document.getElementById('image-select-btn-item');
            const imageUploadInput = document.getElementById('image-upload-item');
            const imagePreview = document.getElementById('item-image-preview');
            const imageUploadArea = document.querySelector('.image-upload-area');

            // 「画像を選択する」ボタンをクリックしたら、非表示のファイル入力フィールドをクリックする
            imageSelectBtn.addEventListener('click', function() {
                imageUploadInput.click();
            });

            // ファイルが選択されたらプレビューを表示する
            imageUploadInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    const file = this.files[0];
                    const reader = new FileReader();

                    reader.onload = function(e) {
                        imagePreview.src = e.target.result;
                        imagePreview.style.display = 'block'; // プレビューを表示
                        // プレビュー表示時にボタンを隠す（またはその逆の処理を行う）
                        // 今回は、プレビューが表示されてもボタンは中央に表示されたままにします。
                        // ただし、画像が背景のように見えるように、ボタンとプレビューのZ-indexを管理します。

                        // ボタンの表示/非表示を切り替えたい場合は、以下を使用
                        // imageSelectBtn.style.opacity = '0';
                        // imageSelectBtn.style.pointerEvents = 'none';
                    }

                    reader.readAsDataURL(file);
                } else {
                    imagePreview.src = '';
                    imagePreview.style.display = 'none'; // ファイルがキャンセルされたら非表示に戻す
                    // imageSelectBtn.style.opacity = '1';
                    // imageSelectBtn.style.pointerEvents = 'auto';
                }
            });

            // ===========================================
            // 2. カスタムドロップダウン機能 (商品の状態)
            // ===========================================
            const selectElement = document.getElementById('custom-condition-select');
            const optionsList = document.getElementById('condition-options');
            const hiddenInput = document.getElementById('condition_input');

            // 1. ドロップダウンの表示/非表示を切り替える
            selectElement.addEventListener('click', function(e) { // ★修正: e を引数として受け取る★
                // 外側クリックで閉じないよう、このクリックイベントの伝播を停止
                e.stopPropagation(); // ★修正: 正しい e.stopPropagation() の構文を使用★

                // 現在の表示状態をチェック
                const isHidden = optionsList.style.display === 'none' || optionsList.style.display === '';

                // 表示/非表示を切り替える
                optionsList.style.display = isHidden ? 'block' : 'none';
                selectElement.classList.toggle('active', isHidden);
            });

            // 2. 項目が選択されたときの処理
            optionsList.querySelectorAll('li').forEach(item => {
                item.addEventListener('click', function() {
                    const value = this.getAttribute('data-value');
                    const text = this.textContent.trim(); // 空白をトリム

                    // フォーム送信用の値と表示テキストを更新
                    hiddenInput.value = value;
                    selectElement.querySelector('span').textContent = text;
                    selectElement.classList.add('selected');

                    // 全ての項目から 'selected' クラスを削除
                    optionsList.querySelectorAll('li').forEach(li => li.classList.remove('selected'));
                    // 選択された項目に 'selected' クラスを追加
                    this.classList.add('selected');

                    // ドロップダウンを非表示にする
                    optionsList.style.display = 'none';
                    selectElement.classList.remove('active');
                });
            });

            // 3. ドロップダウンの外側をクリックしたときに閉じる
            document.addEventListener('click', function(e) {
                // クリックされた要素がカスタムセレクトのコントロールでもオプションリストでもない場合
                if (!selectElement.contains(e.target) && !optionsList.contains(e.target)) {
                    optionsList.style.display = 'none';
                    selectElement.classList.remove('active');
                }
            });

            // 3. ドロップダウンの外側をクリックしたときに閉じる
            document.addEventListener('click', function(e) {
                // クリックされた要素がカスタムセレクトのコントロールでもオプションリストでもない場合
                if (!selectElement.contains(e.target) && !optionsList.contains(e.target)) {
                    optionsList.style.display = 'none';
                    selectElement.classList.remove('active');
                }
            });

            // ===========================================
            // 3. カテゴリータグのトグル機能
            // ===========================================
            document.querySelectorAll('.tag-checkbox').forEach(checkbox => {
                // ラベルはチェックボックスの子要素ではないため、チェックボックスの状態が変わったときに
                // ラベルの見た目を切り替えるCSSは、チェックボックスと隣接する兄弟要素の組み合わせ(`.tag-checkbox:checked + .tag-text`)で機能します。
                // JavaScriptで特別な処理は不要です。
                // ただし、初期状態でチェックされているタグのスタイルを適用するために、CSSで対応しています。
                // また、ラベルをクリックすることでチェックボックスの状態が切り替わります。
            });
        });
    </script>
    </body>
</html>