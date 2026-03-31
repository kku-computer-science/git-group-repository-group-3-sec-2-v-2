@if(isset($paginator) && $paginator->total() > 0)
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2 mt-4">
        <small class="text-muted">
            Showing {{ $paginator->firstItem() }}-{{ $paginator->lastItem() }} of {{ $paginator->total() }} items
        </small>
        {{ $paginator->onEachSide(1)->links() }}
    </div>
@endif
