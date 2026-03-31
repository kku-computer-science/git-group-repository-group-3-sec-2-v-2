
<div class="container mt-5 mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
        <h3 class="mb-3 mb-md-0" style="font-weight: 500;">{{ $title }}</h3>

        @if(!empty($searchRoute))
        <form method="GET" action="{{ route($searchRoute) }}" class="d-flex w-100" style="max-width: 400px;">
            <div class="input-group">
                <input
                    type="text"
                    class="form-control"
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
                @if(!empty($searchValue))
                <button type="button" class="btn btn-outline-secondary" onclick="this.form.elements['{{ $searchName ?? 'textsearch' }}'].value=''; this.form.submit();" title="Clear">
                    <i class="fa fa-times"></i>
                </button>
                @endif
                <button type="submit" class="btn btn-outline-primary">
                    <i class="fa fa-search"></i>
                </button>
            </div>
        </form>
        @endif
    </div>
</div>
