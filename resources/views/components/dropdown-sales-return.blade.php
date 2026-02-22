<select class="form-select single-select-clear-field" id="sales_return_id" name="sales_return_id" data-placeholder="Select Sales Return">
    <option></option>
    @if(!empty($salesReturn))
        @foreach ($salesReturn as $return)
            <option value="{{ $return->id }}" {{ $selected == $return->id ? 'selected' : '' }}>
                {{ $return->return_code }}
            </option>
        @endforeach
    @endif
</select>
