<tr data-claim-item>
    @foreach(['item_desc' => 'Item description', 'material' => 'Material', 'color' => 'Color', 'date_purchase' => 'Date purchased', 'purchase_location' => 'Purchase location', 'amount_paid' => 'Amount paid ($)'] as $field => $label)
    <td><div class="{{ $field === 'amount_paid' ? 'claim-item-amount' : '' }}">
        @if($field === 'amount_paid')<span class="dolor">$</span>@endif
        <input class="form-control" aria-label="{{ $label }}" type="{{ $field === 'date_purchase' ? 'date' : ($field === 'amount_paid' ? 'number' : 'text') }}" id="item_{{ $index }}_{{ $field }}" name="items[{{ $index }}][{{ $field }}]" value="{{ $values[$field] ?? '' }}" @if($field === 'amount_paid') data-item-amount step="0.01" min="0" max="9999999.99" inputmode="decimal" required @elseif($field === 'date_purchase') max="{{ now()->format('Y-m-d') }}" @else maxlength="50" {{ $field === 'item_desc' ? 'required' : '' }} @endif>
    </div></td>
    @endforeach
    <td><button type="button" class="v-btn claim-item-action" data-remove-item aria-label="Remove this item"><i class="fa fa-trash" aria-hidden="true"></i></button></td>
</tr>
