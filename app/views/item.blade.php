@extends('layout')

@section('content')

    <h2>Item details</h2>

    <img style="float: left;" src="/eve/items/{{ $icon }}.png">

    <p>{{ $type->typeName }}</p>

    <p>{{ $type->description }}</p>

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
                            <small>Jita price:</small>
                        @endif
                        {{ number_format($item->price) }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

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
