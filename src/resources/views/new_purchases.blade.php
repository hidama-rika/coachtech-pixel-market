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

    <main>
        <div class="new_purchases-container">

            <div class="new_purchases-form-container">

                <form action="{{ route('purchase.store', ['item_id' => $item->id ?? 0]) }}" method="POST" class="new_purchases-form-container">
                    @csrf
                    <input type="hidden" name="item_id" value="{{ $item->id ?? '' }}">

                    <div class="purchase-details-container">

                        <div class="item-info">
                            {{-- $item の存在チェックとエラー対応 --}}
                            @isset($item)
                            <div class="item-image-area">
                                <div class="item-image">
                                    {{-- 画像URLが有効でない場合を考慮して、画像パスをそのまま使用 --}}
                                    <img src="{{ asset('storage/' . $item->image_path) }}" alt="{{ $item->name }}">
                                </div>
                            </div>
                            <div class="item-text">
                                <p class="item-name">{{ $item->name }}</p>
                                <p class="item-price">¥{{ number_format($item->price) }}</p>
                            </div>
                            @else
                                {{-- 商品情報がない場合の代替表示 --}}
                                <div class="item-image-area">
                                    <div class="item-image placeholder-image">
                                        <p>画像なし</p>
                                    </div>
                                </div>
                                <div class="item-text">
                                    <p class="item-name">商品情報が見つかりません</p>
                                    <p class="item-price">¥0</p>
                                </div>
                            @endisset
                        </div>

                        {{-- 支払い方法の選択 --}}
                        <div class="payment-method-section section-divider">
                            <p class="section-title-small">支払い方法</p>

                            {{--修正: フォーム送信用の隠し入力フィールド--}}
                            <input type="hidden" id="payment_method_input" name="payment_method" value="">

                            {{--修正: カスタムドロップダウンの表示エリア--}}
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

                        {{-- 配送先 --}}
                        <div class="shipping-address-section section-divider">
                            <p class="section-title-small">配送先</p>
                            <a href="{{ route('address.edit') }}" class="change-link">変更する</a>

                            @isset($user)
                            <!-- 配送情報を隠しフィールドとして送信 (Controllerに合わせて) -->
                                <input type="hidden" name="shipping_post_code" value="{{ $user->post_code ?? '' }}">
                                <input type="hidden" name="shipping_address" value="{{ $user->address ?? '' }}">
                                <input type="hidden" name="shipping_building" value="{{ $user->building_name ?? '' }}">

                                <!-- 住所情報が存在する場合、それを表示 -->
                                <p class="address-post-code">〒 {{ $user->post_code ?? '---' }}</p>
                                <p class="address-detail">{{ ($user->address ?? '配送先住所を登録・変更してください。') . ' ' . ($user->building_name ?? '') }}</p>
                            @else
                                <!-- 住所情報が存在しない場合、代替表示 -->
                                <p class="address-post-code">〒 住所未登録</p>
                                <p class="address-detail">配送先住所を登録・変更してください。</p>
                            @endisset
                        </div>
                    </div>

                    {{-- 購入サマリーエリア（右側） --}}
                    <div class="purchase-summary-container">
                        <div class="price-summary-box">
                            <div class="price-row">
                                <p class="summary-label">商品代金</p>
                                <p class="summary-value">¥{{ number_format($item->price ?? 0) }}</p>
                            </div>
                            <div class="price-row">
                                <p class="summary-label">支払い方法</p>
                                <p class="summary-value payment-summary" id="summary-payment-method">コンビニ払い</p>
                            </div>
                        </div>
                        <div class="purchase-button-area">
                            <button type="submit" class="purchase-btn">購入する</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectElement = document.getElementById('custom-payment-select');
        const optionsList = document.getElementById('payment-options');
        const hiddenInput = document.getElementById('payment_method_input');
        // ❗ 修正ポイント: 右側のサマリー表示要素を取得 ❗
        const summaryPaymentMethod = document.getElementById('summary-payment-method');

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

                // A. 左側のフォーム要素を更新
                hiddenInput.value = value;
                selectElement.querySelector('span').textContent = text;
                selectElement.classList.add('selected'); // 選択後のスタイルを適用

                // B. 選択状態のスタイルを更新 (チェックマーク表示用)
                optionsList.querySelectorAll('li').forEach(li => li.classList.remove('selected'));
                // 選択された項目に 'selected' クラスを追加 (チェックマーク表示用)
                this.classList.add('selected');

                // C. ドロップダウンを非表示
                optionsList.style.display = 'none';
                selectElement.classList.remove('active');

                // ❗ 修正ポイント: 右側のサマリー表示を更新 ❗
                summaryPaymentMethod.textContent = text;
            });
        });

        // 3. ドロップダウンの外側をクリックしたときに閉じる
        document.addEventListener('click', function(e) {
            if (!selectElement.contains(e.target) && !optionsList.contains(e.target)) {
                optionsList.style.display = 'none';
                selectElement.classList.remove('active');
            }
        });

        // 4. 初期ロード時の処理: サマリーに初期値を設定する
        // hiddenInputの初期値に基づいてサマリーを更新
        const initialText = optionsList.querySelector(`li[data-value="${hiddenInput.value}"]`)?.textContent || '選択してください';
        selectElement.querySelector('span').textContent = initialText;
        summaryPaymentMethod.textContent = initialText;
    });
    </script>

    </body>
</html>