<h2>Item details</h2>

@if ($icon)
    <img class="icon" src="/eve/items/{{ $icon }}.png">
@endif

<h3>{{ $type->typeName }}</h3>

<table class="eve-central">
    <thead>
        <th colspan="2">Price history</th>
    </thead>
    <tbody>
        <tr>
            <td>Volume</td>
            <td class="num">{{ number_format(round($local_price->volume)) }}</td>
        </tr>
        <tr>
            <td>Average</td>
            <td class="num">{{ number_format(round($local_price->avg)) }}</td>
        <tr>
            <td>Min Price</td>
            <td class="num">{{ number_format(round($local_price->min)) }}</td>
        <tr>
            <td>Max Price</td>
            <td class="num">{{ number_format(round($local_price->max)) }}</td>
        <tr>
            <td>Median Price</td>
            <td class="num">{{ number_format(round($local_price->median)) }}</td>
        </tr>
    </tbody>
</table>

<table class="manufacturing">
    <thead>
        <tr>
            <th>Item</th>
            <th class="num">Price</th>
        </tr>
    </thead>
    <tfoot>
        <tr>
            <td>Total Price</td>
            <td class="num">{{ number_format($total_price) }}</td>
        </tr>
    </tfoot>
    <tbody>
        @foreach($manufacturing as $item)
            <tr>
                <td>{{ $item->typeName }}&nbsp;&times;&nbsp;{{ number_format($item->qty) }}</td>
                <td class="num">
                    @if ($item->jita)
                        <span class="jita-price">
                    @endif
                    {{ number_format($item->price) }}
                    @if ($item->jita)
                        </span>
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

<p class="warning">Prices shown in <span class="jita-price">red</span> are Jita prices, and indicate items that cannot be purchased locally.</p>

<table class="prices">
    <thead>
        <tr>
            <th>Location</th>
            <th class="num">Price</th>
        </tr>
    </thead>
    <tbody>
        @foreach($prices as $price)
            <tr>
                <td>{{ $price->solarSystemName }}</td>
                <td class="num">{{ number_format(round($price->median)) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<script>

    // Update the display of potential profit for this item in the main table.
    $(document).ready(function () {
        var profit = {{ $profit->profitIndustry }};
        var span_class = (profit < 0) ? 'loss' : 'profit';
        var $target = $('td.t{{ $type->typeID }}');
        $target.find('span').remove();
        $('<span class="' + span_class + '">{{ number_format($profit->profitIndustry) }}</span>').appendTo($target);
    });

</script>
