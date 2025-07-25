@props(['url'])

ajax: function (data, callback) {
    const page = (data.start / data.length) + 1;

    let params = {
        page: page,
        length: data.length,
        draw: data.draw,
        search: data.search.value,
        order: data.order,
        columns: data.columns
    };

    {{-- Chèn các filter động từ slot --}}
     {!! $slot !!}

    $.get(@json($url), params, function (response) {
        callback({
            draw: response.draw,
            recordsTotal: response.recordsTotal,
            recordsFiltered: response.recordsFiltered,
            data: response.data
        });
    });
},
