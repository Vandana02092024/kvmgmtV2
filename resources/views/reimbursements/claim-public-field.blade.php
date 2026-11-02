<div class="col-md-4 form-group">
    <label for="{{ $field }}">{{ $label }}</label>
    <input type="{{ $type ?? 'text' }}" id="{{ $field }}" class="form-control" name="{{ $field }}" value="{{ $value ?? old($field) }}" @if($readonly ?? false) readonly aria-readonly="true" @endif maxlength="{{ $maxlength ?? 50 }}" @if(($type ?? '') === 'date') max="{{ now()->format('Y-m-d') }}" @endif @if($required ?? false) required @endif>
</div>
