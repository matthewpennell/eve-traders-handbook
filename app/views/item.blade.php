@extends('layout')

@section('content')

    <h2>Item details</h2>

    @if ($icon)
        <img style="float: left;" src="/eve/items/{{ $icon }}.png">
    @endif

    <p>{{ $type->typeName }}</p>

    <p>{{ $type->description }}</p>

    <p>Local prices:<br>
        Volume = {{ number_format(round($local_price->volume)) }}<br>
        Average = {{ number_format(round($local_price->avg)) }}<br>
        Min Price = {{ number_format(round($local_price->min)) }}<br>
        Max Price = {{ number_format(round($local_price->max)) }}<br>
        Median Price = {{ number_format(round($local_price->median)) }}
    </p>

    <table class="manufacturing">
        <thead>
            <tr>
                <th>Item</th>
                <th>Price</th>
            </tr>
        </thead>
        <tfoot>
            <tr>
                <td>Total Price</td>
                <td class="numeric">{{ number_format($total_price) }}</td>
            </tr>
        </tfoot>
        <tbody>
            @foreach($manufacturing as $item)
                <tr>
                    <td>{{ $item->typeName }} &times; {{ number_format($item->qty) }}</td>
                    <td class="numeric">
                        @if ($item->jita)
                            <span style="color: #c00;">
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
    <p>Prices shown in <span style="color: #c00;">red</span> are Jita prices, and indicate items that cannot be purchased locally.</p>

    <table class="prices">
        <thead>
            <tr>
                <th>Location</th>
                <th>Price</th>
            </tr>
        </thead>
        <tbody>
            @foreach($prices as $price)
                <tr>
                    <td>{{ $price->solarSystemName }}</td>
                    <td class="numeric">{{ number_format(round($price->median)) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>


@stop
