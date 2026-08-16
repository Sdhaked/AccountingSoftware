@php
    $row = $row ?? [];
    $selectedKind = $row['kind'] ?? 'product';
    $selectedSource = $row['source_id'] ?? '';
@endphp
<div class="create-event-form-box company-item-row" data-company-row>
    <div class="grid-2 grid-sm-1 gap-card">
        <div class="form-floating">
            <select class="form-select item-kind" name="company_items[{{ $index }}][kind]" required>
                <option value="product" @selected($selectedKind === 'product')>Product</option>
                <option value="service" @selected($selectedKind === 'service')>Service</option>
            </select>
            <label>Item Type*</label>
        </div>
        <div class="form-floating">
            <select class="form-select item-source" name="company_items[{{ $index }}][source_id]" required>
                <option value="">Select Item</option>
                @foreach($products as $product)
                    <option value="{{ $product->id }}" data-kind="product" data-company="{{ $product->company_id }}"
                            data-price="{{ $product->price }}" data-tax="{{ $product->taxClass->percentage }}"
                            @selected((string) $selectedSource === (string) $product->id && $selectedKind === 'product')>
                        {{ $product->name }} - €{{ number_format((float) $product->price, 2) }}
                    </option>
                @endforeach
                @foreach($services as $service)
                    <option value="{{ $service->id }}" data-kind="service" data-company="{{ $service->company_id }}"
                            data-price="{{ $service->default_rate }}" data-tax="0"
                            @selected((string) $selectedSource === (string) $service->id && $selectedKind === 'service')>
                        {{ $service->name }} - €{{ number_format((float) $service->default_rate, 2) }}
                    </option>
                @endforeach
            </select>
            <label>Product / Service*</label>
        </div>
        <div class="form-floating">
            <input class="form-control" type="number" min="0.001" step="0.001"
                   name="company_items[{{ $index }}][quantity]" value="{{ $row['quantity'] ?? 1 }}" required>
            <label>Quantity*</label>
        </div>
        <div class="d-flex align-items-center justify-content-between gap-2">
            <span class="item-rate text-200">Select an item to view its rate.</span>
            <button class="btn-sm btn-sec-outline remove-entry" type="button" title="Remove item">
                <i class="fa-solid fa-trash"></i>
            </button>
        </div>
    </div>
</div>
