<form action="{{ $action }}" method="GET" id="searchForm" class="row g-3 mb-3">
    @foreach($fields as $field)
        <div class="col-auto">
            <input type="text" name="{{ $field['name'] }}" id="filter{{ ucfirst($field['name']) }}" class="form-control"
                placeholder="{{ $field['placeholder'] ?? $field['label'] }}" value="{{ request($field['name']) }}">
        </div>
    @endforeach

    @if(!empty($statuses))
        <div class="col-auto">
            <select name="status" id="filterStatus" class="form-select">
                <option value="" {{ request()->has('status') ? '' : 'selected' }}>Tất cả trạng thái</option>
                @foreach($statuses as $status)
                    <option value="{{ $status->value }}" @selected((string) request('status') === (string) $status->value)>
                        {{ $status->label() }}
                    </option>
                @endforeach
            </select>
        </div>
    @endif

    <div class="col-auto">
        <button type="submit" class="btn btn-primary">Lọc</button>
    </div>
</form>