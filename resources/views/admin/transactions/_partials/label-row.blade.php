@php
    $row = $row ?? [];
    $selectedLabel = $row['label_id'] ?? '';
    $isOther = (string) $selectedLabel === 'other';
@endphp
<div class="create-event-form-box label-item-row" data-label-row>
    <div class="grid-2 grid-sm-1 gap-card">
        <div class="form-floating">
            <select class="form-select label-select" name="items[{{ $index }}][label_id]" required>
                <option value="">Select Label</option>
                @foreach($labels as $label)
                    <option value="{{ $label->id }}" @selected((string) $selectedLabel === (string) $label->id)>{{ $label->name }}</option>
                @endforeach
                <option value="other" @selected($isOther)>Other</option>
            </select>
            <label>Label*</label>
        </div>
        <div class="form-floating other-label-wrap {{ $isOther ? '' : 'd-none' }}">
            <input class="form-control other-label" type="text" maxlength="255"
                   name="items[{{ $index }}][other_label]" value="{{ $row['other_label'] ?? '' }}">
            <label>Other Label Name*</label>
        </div>
        @if($type === 'income')
            <div class="form-floating">
                <input class="form-control" type="number" min="0.001" step="0.001"
                       name="items[{{ $index }}][quantity]" value="{{ $row['quantity'] ?? 1 }}" required>
                <label>Quantity*</label>
            </div>
        @endif
        <div class="form-floating">
            <input class="form-control" type="number" min="0" step="0.01"
                   name="items[{{ $index }}][price]" value="{{ $row['price'] ?? '' }}" required>
            <label>Price (€)*</label>
        </div>
        <div class="d-flex align-items-center justify-content-end">
            <button class="btn-sm btn-sec-outline remove-entry" type="button" title="Remove item">
                <i class="fa-solid fa-trash"></i>
            </button>
        </div>
    </div>
</div>
