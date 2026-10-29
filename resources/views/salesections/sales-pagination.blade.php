@if($paginator->hasPages())
<nav aria-label="Sales report pages" class="clearfix">
    <p class="pull-left" style="margin:20px 0">
        Showing {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} of {{ $paginator->total() }} weekly reports
    </p>
    <ul class="pagination" style="margin:20px 0 !important">
        <li class="{{ $paginator->onFirstPage() ? 'disabled' : '' }}">
            @if($paginator->onFirstPage())<span>Previous</span>
            @else<a href="{{ $paginator->appends(request()->except('page'))->previousPageUrl() }}" rel="prev">Previous</a>@endif
        </li>
        <li class="active"><span>Page {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }}</span></li>
        <li class="{{ $paginator->hasMorePages() ? '' : 'disabled' }}">
            @if($paginator->hasMorePages())<a href="{{ $paginator->appends(request()->except('page'))->nextPageUrl() }}" rel="next">Next</a>
            @else<span>Next</span>@endif
        </li>
    </ul>
</nav>
@endif
