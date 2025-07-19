<div class="table-responsive">
    <table id="{{ $id }}" class="table table-striped table-hover align-middle {{ $fixed ? 'table-fixed' : '' }}">
        <thead class="table-light">
            <tr>
                @foreach ($columns as $col)
                    <th class="{{ $col['class'] ?? '' }}">
                        {{ $col['label'] }}
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            {{ $slot }}
        </tbody>
    </table>
</div>