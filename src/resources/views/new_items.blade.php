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

            <div class="logo">
                <img src="{{ asset('storage/img/Vector (3).png') }}" alt="logoアイコン" class="icon logo-icon-img">
                <img src="{{ asset('storage/img/Group (2).png') }}" alt="logoテキスト" class="icon logo-text-img">
            </div>

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

            {{-- 商品画像アップロードのセクション --}}
            <div class="item-image-section">
                <div class="image-section-title">商品画像</div>
                <div class="image-upload-area">
                {{-- 画像ファイルを選択するための入力フィールド --}}
                    <input type="file" id="item-image-input" name="item_image" accept="image/*" class="item-image-input">
                {{-- スタイルを適用するためのカスタムラベル --}}
                    <label for="item-image-input" class="image-select-button">
                        画像を選択する
                    </label>
                </div>
            </div>

            <form class="form" action="/mypage/profile" method="post" novalidate>
                @csrf

                {{-- 商品の詳細 --}}
                <div class="section-title">商品の詳細</div>

                {{-- カテゴリー選択のセクションを追加 --}}
                <div class="category-section">
                    <label class="category-label">カテゴリー</label>
                    <div class="category-tags-container">
                        {{-- カテゴリータグの例 (実際はDBから取得したデータをループで表示) --}}

                        {{-- 1行目 --}}
                        <label class="tag-checkbox-label">
                            <input type="checkbox" name="categories[]" value="fashion" class="tag-checkbox">
                            <span class="tag-text selected-tag">ファッション</span>
                        </label>
                        <label class="tag-checkbox-label">
                            <input type="checkbox" name="categories[]" value="household" class="tag-checkbox">
                            <span class="tag-text">家電</span>
                        </label>
                        <label class="tag-checkbox-label">
                            <input type="checkbox" name="categories[]" value="interior" class="tag-checkbox" >
                            <span class="tag-text selected-tag">インテリア</span>
                        </label>
                        <label class="tag-checkbox-label">
                            <input type="checkbox" name="categories[]" value="ladies" class="tag-checkbox">
                            <span class="tag-text">レディース</span>
                        </label>
                        <label class="tag-checkbox-label">
                            <input type="checkbox" name="categories[]" value="mens" class="tag-checkbox">
                            <span class="tag-text">メンズ</span>
                        </label>
                        <label class="tag-checkbox-label">
                            <input type="checkbox" name="categories[]" value="cosme" class="tag-checkbox">
                            <span class="tag-text">コスメ</span>
                        </label>

                        {{-- 2行目 --}}
                        <label class="tag-checkbox-label">
                            <input type="checkbox" name="categories[]" value="other" class="tag-checkbox">
                            <span class="tag-text">本</span>
                        </label>
                        <label class="tag-checkbox-label">
                            <input type="checkbox" name="categories[]" value="game" class="tag-checkbox">
                            <span class="tag-text">ゲーム</span>
                        </label>
                        <label class="tag-checkbox-label">
                            <input type="checkbox" name="categories[]" value="sports" class="tag-checkbox">
                            <span class="tag-text">スポーツ</span>
                        </label>
                        <label class="tag-checkbox-label">
                            <input type="checkbox" name="categories[]" value="kitchen" class="tag-checkbox">
                            <span class="tag-text">キッチン</span>
                        </label>
                        <label class="tag-checkbox-label">
                            <input type="checkbox" name="categories[]" value="handmade" class="tag-checkbox">
                            <span class="tag-text">ハンドメイド</span>
                        </label>
                        <label class="tag-checkbox-label">
                            <input type="checkbox" name="categories[]" value="accessory" class="tag-checkbox">
                            <span class="tag-text">アクセサリー</span>
                        </label>

                        {{-- 3行目 --}}
                        <label class="tag-checkbox-label">
                            <input type="checkbox" name="categories[]" value="toy" class="tag-checkbox">
                            <span class="tag-text">おもちゃ</span>
                        </label>
                        <label class="tag-checkbox-label">
                            <input type="checkbox" name="categories[]" value="baby_kids" class="tag-checkbox">
                            <span class="tag-text">ベビー・キッズ</span>
                        </label>

                    </div>
                </div>

                {{-- 商品の状態 --}}
                <div class="form-group">
                    <label for="condition">商品の状態</label>

                    {{-- ❗ フォーム送信用の隠し入力フィールド ❗ --}}
                    <input type="hidden" id="condition_input" name="condition" value="">

                    {{-- ❗ カスタムドロップダウンの表示エリア ❗ --}}
                    <div id="custom-condition-select" class="form-control custom-select-control">
                        <span>選択してください</span>
                    </div>

                    {{-- ❗ ドロップダウンのリスト ❗ --}}
                    <ul id="condition-options" class="custom-select-options condition-options-list">
                        <li data-value="good_condition" class="custom-select-options-text">良好</li>
                        <li data-value="no_damage" class="custom-select-options-text">目立った傷や汚れなし</li>
                        <li data-value="minor_damage" class="custom-select-options-text">やや傷や汚れあり</li>
                        <li data-value="poor_condition" class="custom-select-options-text">状態が悪い</li>
                    </ul>

                    <p class="new_items-form__error-message">
                        @error('condition')
                        {{ $message }}
                        @enderror
                    </p>
                </div>

                {{-- 商品名と説明 --}}
                <div class="section-title">商品名と説明</div>

                {{-- 商品名 --}}
                <div class="form-group">
                    <label for="items">商品名</label>
                    <input id="items" type="items" class="form-control" name="items" required placeholder="商品名を入力">
                    <p class="new_items-form__error-message">
                        @error('items')
                        {{ $message }}
                        @enderror
                    </p>
                </div>

                {{-- ブランド名 --}}
                <div class="form-group">
                    <label for="brands">ブランド名</label>
                    <input id="brands" type="brands" class="form-control" name="brands" required placeholder="ブランド名を入力">
                    <p class="new_items-form__error-message">
                        @error('brands')
                        {{ $message }}
                        @enderror
                    </p>
                </div>

                {{-- 商品の説明 --}}
                <div class="form-group">
                    <label for="description">商品の説明</label>
                    <input id="description" type="description" class="form-control" name="description" required placeholder="商品の説明を入力">
                    <p class="new_items-form__error-message">
                        @error('description')
                        {{ $message }}
                        @enderror
                    </p>
                </div>

                {{-- 販売価格 --}}
                <div class="form-group">
                    <label for="price">販売価格</label>
                    <input id="price" type="price" class="form-control" name="price" required placeholder="￥">
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

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectElement = document.getElementById('custom-condition-select');
        const optionsList = document.getElementById('condition-options');
        const hiddenInput = document.getElementById('condition_input');

        // 1. ドロップダウンの表示/非表示を切り替える
        selectElement.addEventListener('click', function() {
            optionsList.style.display = optionsList.style.display === 'block' ? 'none' : 'block';
            selectElement.classList.toggle('active'); // 選択エリアにアクティブ状態を付与
        });

        // 2. 項目が選択されたときの処理
        optionsList.querySelectorAll('li').forEach(item => {
            item.addEventListener('click', function() {
                const value = this.getAttribute('data-value');
                const text = this.textContent;

                // フォーム送信用の値と表示テキストを更新
                hiddenInput.value = value;
                selectElement.querySelector('span').textContent = text;
                selectElement.classList.add('selected'); // 選択後のスタイルを適用

                // 全ての項目から 'selected' クラスを削除
                optionsList.querySelectorAll('li').forEach(li => li.classList.remove('selected'));
                // 選択された項目に 'selected' クラスを追加 (チェックマーク表示用)
                this.classList.add('selected');

                // ドロップダウンを非表示にする
                optionsList.style.display = 'none';
                selectElement.classList.remove('active');
            });
        });

        // 3. ドロップダウンの外側をクリックしたときに閉じる
        document.addEventListener('click', function(e) {
            if (!selectElement.contains(e.target) && !optionsList.contains(e.target)) {
                optionsList.style.display = 'none';
                selectElement.classList.remove('active');
            }
        });
    });
    </script>

    </body>
</html>