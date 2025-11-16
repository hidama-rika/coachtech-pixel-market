/**
 * いいねボタンのクリックイベントを処理し、いいねの状態をトグル（切り替え）するスクリプト。
 * いいねが成功した場合、アイコンの見た目といいね数をリアルタイムで更新します。
 * * 必要なHTML要素:
 * - ボタン要素: id="like-button", data-like-url="[APIエンドポイント]", data-is-liked="[true/false]"
 * - アイコン要素: id="like-icon" (スタイル制御用)
 * - カウント要素: id="like-count"
 * - CSRFトークン: <meta name="csrf-token" content="...">
 */
document.addEventListener('DOMContentLoaded', () => {
    // ------------------------------------------------------------------
    // 1. DOM要素の取得
    // ------------------------------------------------------------------
    const likeButton = document.getElementById('like-toggle-button');
    const likeIcon = document.getElementById('like-icon');
    const likeCountSpan = document.getElementById('like-count');

    // ボタンが存在しない場合は処理を終了
    if (!likeButton || !likeIcon || !likeCountSpan) {
        return;
    }

    // CSRFトークンを取得
    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    if (!csrfMeta) {
        console.error('CSRFトークンを示す<meta name="csrf-token">が見つかりません。');
        return;
    }
    const csrfToken = csrfMeta.content;

    // ------------------------------------------------------------------
    // 2. 状態管理とUI更新ロジック
    // ------------------------------------------------------------------

    // 現在の状態（Bladeから渡された初期値）
    let isLiked = likeButton.getAttribute('data-is-liked') === 'true';
    let currentCount = parseInt(likeCountSpan.textContent, 10);

    /**
     * いいねの状態（アイコンとカウント）を更新する関数
     * @param {boolean} newIsLiked - 新しいいいねの状態 (true: いいね済み, false: 未いいね)
     * @param {number} newCount - 新しいいいねの数
     */
    const updateLikeState = (newIsLiked, newCount) => {
        // アイコンのクラスを更新 (likedクラスの追加/削除)
        if (newIsLiked) {
            likeIcon.classList.add('liked');
            likeButton.setAttribute('data-is-liked', 'true');
        } else {
            likeIcon.classList.remove('liked');
            likeButton.setAttribute('data-is-liked', 'false');
        }
        // カウント数を更新
        likeCountSpan.textContent = newCount;
    };

    // ボタンの初期状態を反映
    updateLikeState(isLiked, currentCount);

    // ------------------------------------------------------------------
    // 3. イベントリスナーとAPI通信
    // ------------------------------------------------------------------
    likeButton.addEventListener('click', async (event) => {
        event.preventDefault();

        // 既にAPI通信中であれば、連続クリックを無視
        if (likeButton.disabled) {
            return;
        }

        const url = likeButton.getAttribute('data-like-url');
        if (!url) {
            console.error('いいねAPIのURLが設定されていません (data-like-url属性を確認してください)。');
            return;
        }

        // --- 楽観的更新の準備 ---
        // トグル後の状態を予測
        const predictedIsLiked = !isLiked;
        const predictedCount = isLiked ? currentCount - 1 : currentCount + 1;

        // ローディング中のように見せるためボタンを無効化
        likeButton.disabled = true;

        // UIを仮更新 (APIの結果を待たずに即座に見た目を更新する)
        updateLikeState(predictedIsLiked, predictedCount);

        // アイコンを一時的に薄くするなど、処理中の視覚的フィードバックを追加しても良い
        likeButton.style.opacity = '0.6';

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest' // LaravelでAJAXリクエストとして認識させる
                },
                body: JSON.stringify({})
            });

            const data = await response.json();

            if (response.ok && data.success) {
                // 成功: サーバーからのレスポンスで最終的な状態とカウントを取得し、更新
                // ここでLaravelコントローラーが返すキャメルケースのキー名を使用
                isLiked = data.isLiked;
                currentCount = data.likeCount;

                // サーバーの最終結果でUIを確定（楽観的更新の結果と一致するはずだが、念のため）
                updateLikeState(isLiked, currentCount);

            } else {
                // 失敗 (4xx, 5xx, またはsuccess: false): UIを元の状態に戻す（ロールバック）
                updateLikeState(isLiked, currentCount);

                // エラー内容をコンソールに出力
                console.error('いいねの切り替えに失敗しました。', data.message || response.statusText);

                // ユーザーへの通知（例: 「操作に失敗しました」というトーストメッセージを表示）
            }

        } catch (error) {
            // ネットワークエラーなどが発生した場合、UIを元の状態に戻す
            updateLikeState(isLiked, currentCount);
            console.error('ネットワークエラーが発生しました:', error);

            // ユーザーへの通知
        } finally {
            // 処理が完了したらボタンを再度有効化し、フィードバックを元に戻す
            likeButton.disabled = false;
            likeButton.style.opacity = '1.0';
        }
    });
});