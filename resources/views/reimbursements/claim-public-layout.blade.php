<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex,nofollow"><meta name="referrer" content="no-referrer">
<title>@yield('title', 'Claim Form') · ZIPS</title>
@foreach(['bootstrap.min', 'font-awesome.min', 'layout', 'custom', 'responsive', 'kv-redesign'] as $stylesheet)
<link rel="stylesheet" href="{{ asset('css/'.$stylesheet.'.css') }}?v={{ filemtime(base_path('css/'.$stylesheet.'.css')) }}">
@endforeach
<style>
body.kv-public-claim .main-content{float:none;width:100%;padding:0!important;margin:0!important}
.kv-public-claim .kv-claim-form-page .kv-claim-form .main-detail{border:1px solid #dce5ef}
.kv-public-claim .kv-claim-form-page .kv-claim-form .kv-claim-grid-row .form-group{margin-bottom:0}
.kv-public-claim .kv-claim-form-page .kv-claim-form input[readonly]{background:#f8fafc}
.kv-public-claim .kv-claim-form-page .kv-claim-form .claim-store-note{margin:0 0 16px;color:#718198;font-size:11px}
.kv-public-claim .kv-claim-form-page .kv-claim-form .claim-item-action{border:0;cursor:pointer}
.kv-public-claim button:disabled{opacity:.5;cursor:default}
.kv-public-claim .claim-total-value{display:block;max-width:240px;padding-left:28px}
.kv-public-claim .claim-item-amount{position:relative}
.kv-public-claim .claim-item-amount input{padding-left:20px!important}
.kv-public-claim .claim-item-amount .dolor{position:absolute;top:10px;left:9px}
.kv-public-claim .errors{padding:12px 16px;margin-bottom:16px;border:1px solid #ebc1bd;border-radius:8px;background:#fff0ef;color:#8b2b25}
.kv-public-claim .panel{padding:24px;margin:24px auto;max-width:900px}
.kv-public-claim footer{text-align:center;font-size:11px;color:#718198;padding:0 16px 24px}
.kv-public-claim [hidden]{display:none!important}
</style></head>
<body class="kv-app-body kv-section-reimbursement kv-module-screen kv-public-claim"><main class="main-content">@yield('content')</main><footer>Your information is shared with the store to review your claim.</footer></body></html>
