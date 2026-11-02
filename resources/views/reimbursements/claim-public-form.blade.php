@extends('reimbursements.claim-public-layout')
@section('title', 'New Claim Form')
@section('content')
<section id="dashboard-page" class="wrap kv-claim-form-page"><div class="container-fluid">
    <div class="row"><div class="col-sm-12"><div class="page-heading kv-page-hero kv-claims-hero"><h3 class="heading"><span class="kv-hero-title"><i class="fa fa-file-text-o" aria-hidden="true"></i><span>New Claim Form — {{ $context['store']->location }}</span></span><button type="button" class="v-btn v-btn-primary" id="claimBackButton" hidden><i class="fa fa-arrow-left" aria-hidden="true"></i> BACK</button></h3></div></div></div>
    @if($errors->any())<div class="errors" role="alert"><strong>Please review the following:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <div class="widget form-layout kv-claim-form"><div class="widget-content no-padding">
    <form method="POST" action="{{ route('claim-public.submit', ['token' => $token]) }}" id="publicClaimForm">
    {{ csrf_field() }}
    <div class="main-detail">
        <div class="kv-claim-section-heading"><span>Claim and customer details</span><small>Start with the claim reference, then enter the customer’s contact information.</small></div>
        <div class="row kv-claim-grid-row">
            @include('reimbursements.claim-public-field', ['field'=>'today_date', 'label'=>'Today’s Date', 'value'=>now()->format('m/d/Y'), 'readonly'=>true])
            @include('reimbursements.claim-public-field', ['field'=>'claim_check', 'label'=>'Claim Check #'])
            @include('reimbursements.claim-public-choice', ['field'=>'original_claim_check', 'label'=>'Do you have your original Claim check?', 'options'=>['1'=>'Yes', '0'=>'No']])
            <div class="col-md-4 form-group" id="claimDetail" hidden><label for="claim_detail">Original Claim Check #</label><input class="form-control" id="claim_detail" name="claim_detail" maxlength="255" value="{{ old('claim_detail') }}"></div>
        </div>
        <div class="row kv-claim-grid-row">
            @include('reimbursements.claim-public-field', ['field'=>'claim_check_date', 'label'=>'Claim Check Date', 'type'=>'date'])
            @include('reimbursements.claim-public-field', ['field'=>'special_claim_check', 'label'=>'Special Claim Check # (if any)'])
            @include('reimbursements.claim-public-choice', ['field'=>'compensation', 'label'=>'Compensation', 'options'=>['1'=>'Store Credit', '0'=>'Check', '2'=>'Split', '3'=>'Cash']])
        </div>
        <div class="row kv-claim-grid-row">
            @include('reimbursements.claim-public-field', ['field'=>'first_name', 'label'=>'First Name', 'required'=>true])
            @include('reimbursements.claim-public-field', ['field'=>'last_name', 'label'=>'Last Name', 'required'=>true])
            @include('reimbursements.claim-public-choice', ['field'=>'item', 'label'=>'Item was?', 'options'=>['1'=>'Dry Cleaned', '0'=>'Laundered', '2'=>'Alterations', '3'=>'Household']])
        </div>
        <div class="row kv-claim-grid-row">
            <div class="col-md-4 form-group"><label for="address">Address</label><textarea class="form-control" id="address" name="address" maxlength="2000" autocomplete="street-address" required>{{ old('address') }}</textarea></div>
            @include('reimbursements.claim-public-field', ['field'=>'city', 'label'=>'City', 'required'=>true])
            <div class="col-md-4 form-group"><label for="customerEmail">Email Address</label><input type="email" id="customerEmail" class="form-control" name="email_address" value="{{ $context['link']->email }}" readonly aria-readonly="true" autocomplete="email"></div>
        </div>
        <div class="row kv-claim-grid-row">
            <div class="col-md-4 form-group"><label for="state">State</label><select name="state" id="state" class="form-control" required><option value="">Select State</option>
                @foreach(explode('|', 'Alabama|Alaska|Arizona|Arkansas|California|Colorado|Connecticut|Delaware|District of Columbia|Florida|Georgia|Hawaii|Idaho|Illinois|Indiana|Iowa|Kansas|Kentucky|Louisiana|Maine|Maryland|Massachusetts|Michigan|Minnesota|Mississippi|Missouri|Montana|Nebraska|Nevada|New Hampshire|New Jersey|New Mexico|New York|North Carolina|North Dakota|Ohio|Oklahoma|Oregon|Pennsylvania|Rhode Island|South Carolina|South Dakota|Tennessee|Texas|Utah|Vermont|Virginia|Washington|West Virginia|Wisconsin|Wyoming') as $state)<option value="{{ $state }}" {{ old('state') === $state ? 'selected' : '' }}>{{ $state }}</option>@endforeach
            </select></div>
            @include('reimbursements.claim-public-field', ['field'=>'zip_code', 'label'=>'Zip Code', 'required'=>true])
            @include('reimbursements.claim-public-field', ['field'=>'telephone_number', 'label'=>'Telephone Number', 'type'=>'tel', 'maxlength'=>15, 'required'=>true])
        </div>
        <div class="describe-garments">
            <div class="row"><div class="col-md-9"><h4>DESCRIBE ITEM(S):</h4></div><div class="col-md-3"><button type="button" class="btn btn-sm pull-right add-btn add_decription" id="addClaimItem">+ Add More</button></div></div>
            <?php $items = old('items', [[]]); if (!is_array($items) || !$items) $items = [[]]; $items = array_slice(array_values($items), 0, (int) config('claim_form_links.max_items', 25)); ?>
            <table class="table table-striped kv-claim-items-table" id="search_table"><thead><tr><th>ITEM DESCRIPTION</th><th>MATERIAL</th><th>COLOR</th><th>DATE PURCHASED</th><th>PURCHASE LOCATION</th><th>AMOUNT PAID</th><th>ACTION</th></tr></thead><tbody id="claimItems">@foreach($items as $index => $values)@include('reimbursements.claim-public-item', ['index'=>$index, 'values'=>is_array($values) ? $values : []])@endforeach</tbody></table>
        </div>
        <p id="itemLimitMessage" aria-live="polite"></p>
        <div class="row kv-claim-total-row"><div class="col-md-4"><label for="claimTotal">Total</label><div class="claim-item-amount"><span class="dolor">$</span><output id="claimTotal" class="form-control claim-total-value" aria-live="polite">0.00</output></div></div></div>
        <div class="kv-claim-section-heading"><span>Claim total and proof of purchase</span><small>Enter the total item value and the customer’s settlement offer.</small></div>
        <div class="kv-claim-terms"><p><b>PROOF OF PURCHASE (one of the following):</b> 1) Original or duplicate receipt from store, 2) Itemized credit card receipt, or 3) Monthly statement from store where purchased.</p></div>
        <div class="kv-claim-terms"><p>After allowing for depreciation from age and wear, I would be willing to accept $ <input type="number" aria-label="Requested settlement amount ($)" id="proof_purchase" name="proof_purchase" class="kv-claim-proof-amount" inputmode="decimal" min="0.01" max="9999999.99" step="0.01" value="{{ old('proof_purchase') }}" required> as full settlement of this claim. I acknowledge that this listed amount is an offer made by me to be considered by ZIPS. Please note that once the claim is resolved, the item(s), for which you received compensation will remain in our possession.</p></div>
        <div class="kv-claim-terms"><p>Customer is responsible to ensure this form is filled out completely and legibly. Store Credit will be added to Customer account or check is mailed to the home address listed above within ten (10) business days of final resolution. Copies of this document are not provided.</p></div>
        <div class="form-group"><label class="radio-inline"><input type="checkbox" name="acknowledged" value="1" required {{ old('acknowledged') ? 'checked' : '' }}>The statements I made above are true and correct.</label></div>
        <div class="row kv-claim-grid-row">
            @include('reimbursements.claim-public-choice', ['field'=>'receipts_attached', 'label'=>'Do you have proof of purchase?', 'options'=>['1'=>'Yes', '0'=>'No']])
            @include('reimbursements.claim-public-choice', ['field'=>'claim_type', 'label'=>'Claim Type', 'options'=>['1'=>'Missing Item', '2'=>'Missing Order', '3'=>'Damaged Item']])
            <div class="col-md-4 form-group"><label for="damaged_desc">Comments / damage description</label><textarea class="form-control" id="damaged_desc" name="damaged_desc" maxlength="255">{{ old('damaged_desc') }}</textarea></div>
        </div>
        <p class="claim-store-note">Please provide any receipts to the store separately. Submission sends your claim to {{ $context['store']->location }} for review.</p>
        <div class="text-center"><button type="submit" class="v-btn v-btn-primary add-btn" id="claimSubmitButton">Submit Claim</button></div>
    </div>
    </form></div></div>
</div></section>
<template id="claimItemTemplate">@include('reimbursements.claim-public-item', ['index'=>'__INDEX__', 'values'=>[]])</template>
<script nonce="{{ $nonce }}">
(function(){
    const items=document.getElementById('claimItems'),add=document.getElementById('addClaimItem'),max={{ (int) config('claim_form_links.max_items',25) }};
    let nextIndex={{ count($items) }};
    function refresh(){
        const rows=items.querySelectorAll('[data-claim-item]');let cents=0;
        rows.forEach(function(row,index){row.querySelector('[data-remove-item]').disabled=rows.length===1;const value=Number(row.querySelector('[data-item-amount]').value);if(Number.isFinite(value)&&value>=0)cents+=Math.round(value*100);});
        document.getElementById('claimTotal').value=(cents/100).toFixed(2);add.disabled=rows.length>=max;
        document.getElementById('itemLimitMessage').textContent=rows.length>=max?'Maximum '+max+' items per claim.':'';
    }
    add.addEventListener('click',function(){if(items.querySelectorAll('[data-claim-item]').length>=max)return;items.insertAdjacentHTML('beforeend',document.getElementById('claimItemTemplate').innerHTML.replace(/__INDEX__/g,String(nextIndex++)));refresh();});
    items.addEventListener('click',function(event){const button=event.target.closest('[data-remove-item]');if(button&&items.querySelectorAll('[data-claim-item]').length>1){button.closest('[data-claim-item]').remove();refresh();}});
    items.addEventListener('input',refresh);refresh();
    const hasOriginal=document.querySelectorAll('input[name=original_claim_check]'),number=document.getElementById('claim_detail');
    function originalChanged(){const selected=document.querySelector('input[name=original_claim_check]:checked');number.required=!!selected&&selected.value==='1';document.getElementById('claimDetail').hidden=!number.required;}hasOriginal.forEach(function(radio){radio.addEventListener('change',originalChanged);});originalChanged();
    const back=document.getElementById('claimBackButton');back.hidden=window.history.length<=1;back.addEventListener('click',function(){window.history.back();});
    document.getElementById('publicClaimForm').addEventListener('submit',function(){document.getElementById('claimSubmitButton').disabled=true;document.getElementById('claimSubmitButton').textContent='Submitting…';});
})();
</script>
@endsection

