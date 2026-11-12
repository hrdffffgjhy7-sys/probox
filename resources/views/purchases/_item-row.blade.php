<tr data-item-row>
    <td class="td align-top">
        <select name="items[{{ $index }}][product_id]" data-product-select class="select">
            <option value="">{{ __('Select product') }}</option>
            @foreach ($products as $product)
                <option value="{{ $product->id }}"
                    data-name="{{ $product->name }}"
                    data-unit="{{ $product->unit }}"
                    data-rate="{{ $product->purchase_rate }}"
                    @selected((string) old('items.'.$index.'.product_id', $item?->product_id) === (string) $product->id)>
                    {{ $product->name }}
                </option>
            @endforeach
        </select>
        <input type="text" name="items[{{ $index }}][product_name]" data-field="product_name" value="{{ old('items.'.$index.'.product_name', $item?->product_name) }}" class="input mt-2" placeholder="{{ __('Product name') }}" required>
    </td>
    <td class="td align-top">
        <input type="number" step="0.001" min="0" name="items[{{ $index }}][quantity]" data-field="quantity" value="{{ old('items.'.$index.'.quantity', $item?->quantity ?? 1) }}" class="input w-24 text-end" required>
    </td>
    <td class="td align-top">
        <input type="text" name="items[{{ $index }}][unit]" data-field="unit" value="{{ old('items.'.$index.'.unit', $item?->unit ?? 'pcs') }}" class="input w-20">
    </td>
    <td class="td align-top">
        <input type="number" step="0.01" min="0" name="items[{{ $index }}][rate]" data-field="rate" value="{{ old('items.'.$index.'.rate', $item?->rate ?? 0) }}" class="input w-28 text-end" required>
    </td>
    <td class="td align-top">
        <input type="number" step="0.01" min="0" name="items[{{ $index }}][discount]" data-field="discount" value="{{ old('items.'.$index.'.discount', $item?->discount ?? 0) }}" class="input w-24 text-end">
    </td>
    <td class="td align-top">
        <input type="number" step="0.01" min="0" name="items[{{ $index }}][tax]" data-field="tax" value="{{ old('items.'.$index.'.tax', $item?->tax ?? 0) }}" class="input w-24 text-end">
    </td>
    <td class="td align-top text-end">
        <span class="inline-block py-2 font-semibold" data-line-total>0.00</span>
    </td>
    <td class="td align-top text-end">
        <button type="button" class="btn-ghost btn-sm text-rose-600 dark:text-rose-400" data-remove-item title="{{ __('Remove') }}">&times;</button>
    </td>
</tr>
