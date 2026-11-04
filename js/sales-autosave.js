(function ($) {
    'use strict';
    $(function () {
        var panel = document.getElementById('sales-comment-editor');
        if (!panel) return;
        var input = document.getElementById('sales-comment-input');
        var status = document.getElementById('sales-autosave-status');
        var comments = {}, fields = new Map(), active = null, busy = false, timer = null;
        var locationId = panel.dataset.location;
        var apiHeaders = {'Accept': 'application/json'};
        var positionFrame = null;

        function positionEditor() {
            if (!active || panel.hidden) return;
            var anchor = active.button.getBoundingClientRect();
            var width = document.documentElement.clientWidth, height = window.innerHeight;
            if (!active.button.isConnected || !anchor.width || anchor.bottom < 0 || anchor.top > height) {
                closeEditor(false); return;
            }
            var rect = panel.getBoundingClientRect(), gap = 8, margin = 8;
            var left = Math.max(margin, Math.min(anchor.left + anchor.width / 2 - rect.width / 2, width - rect.width - margin));
            var above = anchor.top - rect.height - gap;
            // Prefer above the clicked icon; use below when the viewport has no room above.
            var top = above >= margin ? above : anchor.bottom + gap;
            top = Math.max(margin, Math.min(top, height - rect.height - margin));
            panel.style.left = left + 'px'; panel.style.top = top + 'px';
        }
        function requestPosition() {
            if (positionFrame !== null) return;
            positionFrame = window.requestAnimationFrame(function () { positionFrame = null; positionEditor(); });
        }
        window.addEventListener('resize', requestPosition);
        document.addEventListener('scroll', function (event) { if (!panel.contains(event.target)) requestPosition(); }, true);
        if (window.ResizeObserver) new ResizeObserver(requestPosition).observe(panel);

        function errorText(xhr) {
            if (xhr.status === 419 || xhr.status === 401) return 'Your session expired. Keep a copy of your changes and sign in again.';
            var json = xhr.responseJSON || {};
            if (!xhr.responseJSON && xhr.responseText) {
                try { json = JSON.parse(xhr.responseText); } catch (ignore) { /* A proxy may return HTML. */ }
            }
            if (json.errors) return Object.keys(json.errors).map(function (key) { return json.errors[key].join(' '); }).join(' ');
            return json.message || json.error || 'Could not save. Check your connection and retry; your changes are still here.';
        }
        function post(url, data) {
            return $.ajax({url: url, method: 'POST', data: data, dataType: 'json', headers: apiHeaders, timeout: 20000});
        }
        function dirty(state) { return state.value !== state.saved; }
        function allStates() { return Array.from(fields.values()).concat(Object.keys(comments).map(function (key) { return comments[key]; })); }
        function renderStatus() {
            var states = allStates(), failed = states.some(function (s) { return !!s.error; });
            var pending = states.some(dirty);
            status.textContent = failed ? 'Changes need attention — retry saving' : busy ? 'Saving…' : pending ? 'Unsaved changes…' : 'All changes saved';
            status.dataset.state = failed ? 'error' : 'saved';
            var alert = document.getElementById('sales-save-error');
            alert.hidden = !failed;
            if (failed) alert.textContent = states.filter(function (s) { return !!s.error; }).map(function (s) { return s.error; }).filter(function (v, i, a) { return a.indexOf(v) === i; }).join(' ');
            if (active) renderEditor();
        }
        function renderEditor() {
            var state = active;
            document.getElementById('sales-comment-context').textContent = state.meta.name + ' · ' + state.meta.popdayname;
            input.disabled = state.loading || state.loadFailed;
            document.getElementById('sales-comment-count').textContent = state.value.length.toLocaleString() + ' / 10,000';
            document.getElementById('sales-comment-status').textContent = state.loading ? 'Loading saved comment…' : state.error ? 'Not saved' : state.saving ? 'Saving…' : dirty(state) ? 'Unsaved changes…' : 'Saved';
            var error = document.getElementById('sales-comment-error');
            error.hidden = !state.error; error.textContent = state.error || '';
            var retry = document.getElementById('sales-comment-retry');
            retry.hidden = !state.error || state.conflict;
            retry.textContent = state.loadFailed ? 'Retry loading' : 'Retry save';
            var reload = document.getElementById('sales-comment-reload');
            reload.hidden = !state.conflict;
            reload.textContent = state.reloadConfirm ? 'Discard draft and reload' : 'Reload saved comment';
            requestPosition();
        }
        function paintComment(state) {
            var button = state.button, text = state.saved;
            button.classList.toggle('has-comment', text.trim() !== '');
            button.title = text || 'Add a comment';
            button.setAttribute('aria-label', (text ? 'View or edit comment: ' : 'Add comment: ') + state.meta.name + ', ' + state.meta.popdayname);
            button.querySelector('i').className = text.trim() ? 'fa fa-comment' : 'fa fa-comment-o';
            var preview = button.nextElementSibling;
            if (!preview || !preview.classList.contains('sales-comment-preview')) {
                preview = document.createElement('span'); preview.className = 'sales-comment-preview'; button.after(preview);
            }
            preview.textContent = text; preview.title = text; preview.hidden = !text.trim();
        }
        function schedule(delay) { clearTimeout(timer); timer = setTimeout(pump, delay === undefined ? 800 : delay); renderStatus(); }
        function fieldState(el) {
            if (!fields.has(el)) fields.set(el, {el: el, value: el.value, saved: el.value, error: null});
            return fields.get(el);
        }
        document.querySelectorAll('input.sale, input.sales-daily-metric').forEach(fieldState);
        function validField(state) {
            if (state.el.classList.contains('sales-daily-metric')) return state.el.checkValidity();
            return state.value === '' || /^[+-]?(?:\d+(?:\.\d{0,2})?|\.\d{1,2})$/.test(state.value);
        }
        function entry(state) {
            var el = state.el;
            return {name: el.name, unit: el.getAttribute('unit'), dayno: el.getAttribute('dayno'), val: state.value,
                dayname: document.getElementById(el.getAttribute('dayno')).textContent.trim(), nameID: el.getAttribute('nameId'),
                weekNo: document.getElementById('weekNoData').textContent.trim(), storeNo: document.getElementById('storeNoData').textContent.trim()};
        }
        function pump() {
            if (busy) return;
            var comment = Object.keys(comments).map(function (key) { return comments[key]; }).find(function (s) { return dirty(s) && !s.error && !s.loading; });
            if (comment) { saveComment(comment); return; }
            var batch = Array.from(fields.values()).filter(function (s) { return dirty(s) && !s.error; });
            batch.forEach(function (s) { if (!validField(s)) s.error = 'Enter a valid number before saving this field.'; });
            batch = batch.filter(function (s) { return !s.error; });
            if (!batch.length) { renderStatus(); return; }
            var values = batch.map(function (s) { return s.value; }), sales = [], metrics = {};
            batch.forEach(function (s) {
                if (s.el.classList.contains('sales-daily-metric')) {
                    var date = s.el.dataset.reportDate;
                    if (!metrics[date]) metrics[date] = {date: date};
                    metrics[date][s.el.dataset.metricField] = s.value;
                } else sales.push(entry(s));
            });
            busy = true; renderStatus();
            // Only edited cells are sent. Saving another cell cannot reset a comment or an untouched value.
            var data = {allsaledata: JSON.stringify(sales), expected_location_id: locationId, auto_save: 1};
            if (Object.keys(metrics).length) data.daily_metrics = JSON.stringify(Object.keys(metrics).map(function (date) { return metrics[date]; }));
            $.ajax({url: panel.dataset.salesUrl, method: 'POST', data: data, headers: apiHeaders, dataType: 'text', timeout: 30000})
                .done(function (result) {
                    if (String(result).trim() !== '1') { batch.forEach(function (s) { s.error = 'Unexpected save response. Sign in again if your session expired.'; }); return; }
                    batch.forEach(function (s, i) { s.saved = values[i]; });
                }).fail(function (xhr) { batch.forEach(function (s) { s.error = errorText(xhr); }); })
                .always(function () { busy = false; renderStatus(); schedule(0); });
        }
        function saveComment(state) {
            var sent = state.value;
            busy = true; state.saving = true; renderStatus();
            post(panel.dataset.saveUrl, Object.assign({}, state.meta, {message: sent, expected_comment: state.saved, expected_location_id: locationId}))
                .done(function (result) { state.saved = result.comment; state.error = null; paintComment(state); })
                .fail(function (xhr) { state.error = errorText(xhr); state.conflict = xhr.status === 409; })
                .always(function () { busy = false; state.saving = false; renderStatus(); schedule(0); });
        }
        function loadComment(state) {
            var version = state.loadVersion = (state.loadVersion || 0) + 1;
            if (!state.meta.popupweekNo) state.meta.popupweekNo = document.getElementById('weekNoData').textContent.trim();
            if (!state.meta.popupStorNo) state.meta.popupStorNo = document.getElementById('storeNoData').textContent.trim();
            state.loading = true; state.loadFailed = false; state.reloadConfirm = false; state.error = null; renderStatus();
            post(panel.dataset.readUrl, Object.assign({}, state.meta, {expected_location_id: locationId}))
                .done(function (result) {
                    if (state.loadVersion !== version) return;
                    state.value = state.saved = result.comment; state.conflict = false; paintComment(state);
                    if (active === state) input.value = state.value;
                }).fail(function (xhr) { if (state.loadVersion === version) { state.error = errorText(xhr); state.loadFailed = true; } })
                .always(function () { if (state.loadVersion !== version) return; state.loading = false; renderStatus(); if (active === state && !state.loadFailed) input.focus(); });
        }
        document.querySelectorAll('.sales-comment-trigger').forEach(function (button) {
            var parent = button.closest('td, .kv-daily-entry-input'), el = parent && parent.querySelector('input[unit="amt"], input.sales-daily-metric');
            if (!el) return;
            var metricField = el.dataset.metricField;
            var day = el.getAttribute('dayno'), nameId = metricField ? 'daily_metrics_0' : el.getAttribute('nameId');
            var key = nameId + '|' + (metricField || '') + '|' + day;
            var state = comments[key] = {button: button, value: button.dataset.salesComment || '', saved: button.dataset.salesComment || '', loading: false, error: null,
                meta: {name: metricField ? el.dataset.commentLabel : el.name, nameId: nameId, unit: metricField ? 'count' : 'amt', day: day, popdayname: document.getElementById(day).textContent.trim(),
                    popupweekNo: document.getElementById('weekNoData').textContent.trim(), popupStorNo: document.getElementById('storeNoData').textContent.trim()}};
            if (metricField) state.meta.metric_field = metricField;
            paintComment(state);
            button.addEventListener('click', function (event) {
                event.preventDefault(); // The daily view moves controls into a label.
                schedule(0); active = state; panel.hidden = false; input.value = state.value;
                renderEditor();
                positionEditor();
                if (!dirty(state) && !state.saving) {
                    // Week metadata is loaded asynchronously by the existing date picker.
                    state.meta.popupweekNo = document.getElementById('weekNoData').textContent.trim();
                    state.meta.popupStorNo = document.getElementById('storeNoData').textContent.trim();
                    loadComment(state);
                } else input.focus();
            });
        });
        input.addEventListener('input', function () {
            if (!active || active.loading || active.loadFailed) return;
            active.value = input.value;
            if (!active.conflict) active.error = null;
            schedule();
        });
        input.addEventListener('blur', function () { schedule(0); });
        function closeEditor(restoreFocus) {
            panel.hidden = true; var button = active && active.button; active = null; schedule(0); if (button && restoreFocus !== false) button.focus();
        }
        document.addEventListener('pointerdown', function (event) {
            if (!panel.hidden && !panel.contains(event.target) && !event.target.closest('.sales-comment-trigger')) closeEditor(false);
        });
        document.getElementById('sales-comment-close').addEventListener('click', closeEditor);
        panel.addEventListener('keydown', function (event) { if (event.key === 'Escape') closeEditor(); });
        document.getElementById('sales-comment-retry').addEventListener('click', function () {
            if (!active) return;
            if (active.loadFailed) { loadComment(active); return; }
            active.error = null; schedule(0);
        });
        document.getElementById('sales-comment-reload').addEventListener('click', function () {
            if (!active) return;
            if (active.reloadConfirm) { loadComment(active); return; }
            active.reloadConfirm = true;
            active.error = 'Copy your draft if you want to keep it. Click “Discard draft and reload” to load the saved comment.';
            renderStatus();
        });
        $(document).on('input change', 'input.sale, input.sales-daily-metric', function () {
            var state = fieldState(this); state.value = this.value; state.error = null; schedule();
        }).on('focusout', 'input.sale, input.sales-daily-metric', function () { schedule(0); });
        $(document).on('click', '[data-ledger-view], .kv-ledger-day', function () {
            // The daily/matrix switch moves the original controls into new wrappers.
            setTimeout(function () { Object.keys(comments).forEach(function (key) { paintComment(comments[key]); }); requestPosition(); }, 0);
        });
        document.getElementById('ClickMe').addEventListener('click', function () {
            fields.forEach(function (s) { s.value = s.el.value; s.error = null; });
            Object.keys(comments).forEach(function (key) { if (!comments[key].conflict && !comments[key].loadFailed) comments[key].error = null; });
            schedule(0);
        });
        window.addEventListener('online', function () {
            allStates().forEach(function (s) { if (!s.conflict && !s.loadFailed) s.error = null; }); schedule(0);
        });
        window.addEventListener('beforeunload', function (event) {
            if (busy || allStates().some(dirty)) { event.preventDefault(); event.returnValue = ''; }
        });
    });
}(jQuery));
