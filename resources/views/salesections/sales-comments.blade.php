<aside id="sales-comment-editor" class="sales-comment-editor" role="dialog" aria-modal="false" aria-labelledby="sales-comment-title" hidden
    data-location="{{ (int) Session::get('location_id') }}" data-read-url="{{ url('/getComment') }}" data-save-url="{{ url('/commentSave') }}" data-sales-url="{{ url('/dataSaveSales') }}">
    <div class="sales-comment-header"><div><h3 id="sales-comment-title">Sales comment</h3><p id="sales-comment-context"></p></div><button type="button" id="sales-comment-close" aria-label="Close comment editor">&times;</button></div>
    <div class="sales-comment-body">
        <label for="sales-comment-input">Comment</label>
        <textarea id="sales-comment-input" maxlength="10000" rows="4" placeholder="Add a note for this entry…" disabled></textarea>
        <div class="sales-comment-meta"><span id="sales-comment-status" role="status" aria-live="polite">Loading…</span><span id="sales-comment-count">0 / 10,000</span></div>
        <p id="sales-comment-error" role="alert" hidden></p>
        <div class="sales-comment-actions"><button type="button" id="sales-comment-retry" class="btn btn-primary" hidden>Retry save</button><button type="button" id="sales-comment-reload" class="btn btn-default" hidden>Reload saved comment</button></div>
    </div>
</aside>
