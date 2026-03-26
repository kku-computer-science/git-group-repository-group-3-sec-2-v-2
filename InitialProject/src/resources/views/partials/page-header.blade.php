{{--
    Reusable Blue Page Header Banner
    Params:
      $title            (required) — heading text
      $searchRoute      (optional) — named route to submit search
      $searchPlaceholder (optional) — placeholder for search input
      $searchName       (optional) — input name attr, default 'textsearch'
      $searchValue      (optional) — current search value
      $extraParams      (optional) — array of hidden inputs ['name' => 'value']
--}}
<div class="page-header">
    <div class="container">
        <h1>{{ $title }}</h1>

        @if(!empty($searchRoute))
        <form method="GET" action="{{ route($searchRoute) }}" class="search-form">
            <div class="position-relative">
                <input
                    type="text"
                    class="form-control search-input"
                    name="{{ $searchName ?? 'textsearch' }}"
                    value="{{ $searchValue ?? '' }}"
                    placeholder="{{ $searchPlaceholder ?? 'Search...' }}"
                    aria-label="Search"
                >
                {{-- Extra hidden inputs --}}
                @if(!empty($extraParams))
                    @foreach($extraParams as $paramName => $paramValue)
                        <input type="hidden" name="{{ $paramName }}" value="{{ $paramValue }}">
                    @endforeach
                @endif
                <button type="submit" class="search-btn">
                    <ion-icon name="search" size="large"></ion-icon>
                </button>
            </div>
        </form>
        @endif
    </div>
</div>
