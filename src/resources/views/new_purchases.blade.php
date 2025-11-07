<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COACHTECH-商品購入画面</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/new_purchases.css')}}">
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

    <main>
        <div class="new_purchases-container">

            <div class="new_purchases-form-container">

                <div class="purchase-details-container">

                    <div class="item-info">
                        <div class="item-image-area">
                            <div class="item-image">商品画像</div>
                        </div>
                        <div class="item-text">
                            <p class="item-name">商品名</p>
                            <p class="item-price">¥ 47,000</p>
                        </div>
                    </div>

                    <div class="payment-method-section section-divider">
                        <p class="section-title-small">支払い方法</p>

                        {{-- ❗ 修正: フォーム送信用の隠し入力フィールド ❗ --}}
                        <input type="hidden" id="payment_method_input" name="payment_method" value="">

                        {{-- ❗ 修正: カスタムドロップダウンの表示エリア ❗ --}}
                        <div id="custom-payment-select" class="form-control custom-select-control payment-select-display">
                            <span class="payment-select-text">選択してください</span>
                        </div>

                        {{-- ❗ 修正: ドロップダウンのリスト ❗ --}}
                        <ul id="payment-options" class="custom-select-options payment-options-list">
                            <li data-value="convenience" class="custom-select-options-text">コンビニ払い</li>
                            <li data-value="card" class="custom-select-options-text">カード支払い</li>
                        </ul>

                        {{-- エラーメッセージのエリア（Laravelの@errorディレクティブを仮定） --}}
                        <p class="new_purchases-form__error-message">
                            @error('payment_method')
                            {{ $message }}
                            @enderror
                        </p>

                    </div>

                    <div class="shipping-address-section section-divider">
                        <p class="section-title-small">配送先</p>
                        <a href="/address/edit" class="change-link">変更する</a>
                        <p class="address-post-code">〒 XXX-YYYY</p>
                        <p class="address-detail">ここには住所と建物が入ります</p>
                    </div>
                </div>

                <div class="purchase-summary-container">
                    <div class="price-summary-box">
                        <div class="price-row">
                            <p class="summary-label">商品代金</p>
                            <p class="summary-value">¥ 47,000</p>
                        </div>
                        <div class="price-row">
                            <p class="summary-label">支払い方法</p>
                            <p class="summary-value payment-summary">コンビニ払い</p>
                        </div>
                    </div>
                    <div class="purchase-button-area">
                        <button type="submit" class="purchase-btn">購入する</button>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectElement = document.getElementById('custom-payment-select');
        const optionsList = document.getElementById('payment-options');
        const hiddenInput = document.getElementById('payment_method_input');

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