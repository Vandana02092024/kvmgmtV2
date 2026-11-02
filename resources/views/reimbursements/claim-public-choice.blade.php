<div class="col-md-4 form-group {{ $field === 'item' ? 'kv-claim-item-field' : '' }}" role="group" aria-labelledby="{{ $field }}_label">
    <label id="{{ $field }}_label">{{ $label }}</label>
    <div class="{{ $field === 'item' ? 'kv-claim-item-options' : ($field === 'compensation' ? 'kv-claim-choice-grid' : '') }}">
    @foreach($options as $key => $option)
        <label class="radio-inline" for="{{ $field }}_{{ $key }}"><input type="radio" id="{{ $field }}_{{ $key }}" name="{{ $field }}" value="{{ $key }}" {{ (string)old($field, '') === (string)$key ? 'checked' : '' }} required>{{ $option }}</label>
    @endforeach
    </div>
</div>
