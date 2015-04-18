<h2>Item details</h2>

@if ($icon)
    <img class="icon" src="{{ $icon }}">
@endif

<h3 id="{{ $type->typeID }}">{{ $type->typeName }}</h3>

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

@if ($manufacturing)
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
            <tr class="efficiency">
                <td colspan="2">
                    Material efficiency:
                    <select id="material-efficiency">
                        <option value="0"
                            @if ($material_efficiency->materialEfficiency == 0)
                                selected="selected"
                            @endif
                        >0</option>
                        <option value="1"
                        @if ($material_efficiency->materialEfficiency == 1)
                        selected="selected"
                        @endif
                        >1%</option>
                        <option value="2"
                        @if ($material_efficiency->materialEfficiency == 2)
                        selected="selected"
                        @endif
                        >2%</option>
                        <option value="3"
                        @if ($material_efficiency->materialEfficiency == 3)
                        selected="selected"
                        @endif
                        >3%</option>
                        <option value="4"
                        @if ($material_efficiency->materialEfficiency == 4)
                        selected="selected"
                        @endif
                        >4%</option>
                        <option value="5"
                        @if ($material_efficiency->materialEfficiency == 5)
                        selected="selected"
                        @endif
                        >5%</option>
                        <option value="6"
                        @if ($material_efficiency->materialEfficiency == 6)
                        selected="selected"
                        @endif
                        >6%</option>
                        <option value="7"
                        @if ($material_efficiency->materialEfficiency == 7)
                        selected="selected"
                        @endif
                        >7%</option>
                        <option value="8"
                        @if ($material_efficiency->materialEfficiency == 8)
                        selected="selected"
                        @endif
                        >8%</option>
                        <option value="9"
                        @if ($material_efficiency->materialEfficiency == 9)
                        selected="selected"
                        @endif
                        >9%</option>
                        <option value="10"
                        @if ($material_efficiency->materialEfficiency == 10)
                        selected="selected"
                        @endif
                        >10%</option>
                    </select>
                </td>
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
@endif

@if ($t2_options)
    <table class="manufacturing">
        <thead>
            <tr>
                <th>Decryptor</th>
                <th class="num">Cost/unit</th>
            </tr>
        </thead>
        <tbody>
            @foreach($t2_options as $decryptor)
                <tr>
                    <td>{{ $decryptor['typeName'] }}</td>
                    <td class="num">{{ number_format($decryptor['cost']) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif

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

<p class="warning">Prices shown in <span class="jita-price">red</span> are Jita prices, and indicate items that cannot be purchased locally.</p>

<script>

    // Update the display of potential profit for this item in the main table.
    $(document).ready(function () {
        var profitIndustry = {{ $profit->profitIndustry }},
            profitImport = {{ $profit->profitImport }};
        var profit = (profitIndustry > profitImport) ? profitIndustry : profitImport,
            profitType = (profitIndustry > profitImport) ? 'local' : 'import';
        var profitOrLoss = (profit > 0) ? 'profit' : 'loss';
        var $target = $('td.t{{ $type->typeID }}');
        $target.find('span').remove();
        $('<span class="' + profitType + ' ' + profitOrLoss + '">{{ number_format($profitToUse) }}</span>').appendTo($target);
        $('<span class="percentage ' + profitOrLoss + '">{{ number_format(round($profitToUse / $costToUse * 100)) }}%</span>').appendTo($target);
    });

</script>
