@props(['item'])

{{-- 認証済みユーザーのみいいねボタンを有効化 --}}
@auth
    {{-- 💡 index画面で複数のアイテムを扱うため、id属性に$item->idを組み込み、一意性を確保しています。 --}}
    {{-- 💡 class属性（.like-button, .like-toggle-form）を使ってJavaScriptから要素を特定します。 --}}
    <form class="like-toggle-form" action="{{ route('like.toggle', $item) }}" method="POST">
        @csrf
        <button type="button" class="reaction-item like-button"
            id="like-toggle-button-{{ $item->id }}"
            data-item-id="{{ $item->id }}"
            data-like-url="{{ route('like.toggle', ['item' => $item->id]) }}"
            data-is-liked="@if(Auth::user()->isLiking($item)) true @else false @endif"
        >
            <span class="reaction-icon">
                {{-- ユーザーがいいね済みなら 'liked' クラスを付与 --}}
                <img src="{{ asset('storage/img/Vector (4).png') }}" alt="いいねアイコン"
                    class="like-icon-img @if(Auth::user()->isLiking($item)) liked @endif"
                    id="like-icon-{{ $item->id }}">
            </span>
            {{-- いいねカウントもアイテムIDと組み合わせて一意に --}}
            <span class="reaction-count" id="like-count-{{ $item->id }}">
                {{ $item->likedUsers->count() }}
            </span>
        </button>
    </form>
@else
    {{-- 未認証ユーザーはボタンとして機能させず、アイコンとカウントのみ表示 --}}
    <div class="reaction-item">
        <span class="reaction-icon">
            <img src="{{ asset('storage/img/Vector (4).png') }}" alt="いいねアイコン" class="icon like-icon-img @if($item->is_liked_by_guest ?? false) liked @endif">
        </span>
        <span class="reaction-count">{{ $item->likedUsers->count() }}</span>
    </div>
@endauth