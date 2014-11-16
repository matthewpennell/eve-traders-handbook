@extends('layout')

@section('content')

    <h1>EVE Traders Handbook</h1>

    <p>Welcome. These are the items that have been lost in your selected systems/alliances:</p>

    <form method="get" action="" class="filters">
        <div class="filter">
            <label>
                {{ Form::checkbox('filter[]', 'drone', $filters && in_array('drone', $filters)) }}
                Drone
            </label>
        </div>
        <div class="filter">
            <label>
                {{ Form::checkbox('filter[]', 'module', $filters && in_array('module', $filters)) }}
                Module
            </label>
        </div>
        <div class="filter">
            <label>
                {{ Form::checkbox('filter[]', 'ship', $filters && in_array('ship', $filters)) }}
                Ship
            </label>
        </div>
    </form>

    <table class="items-lost">
        <thead>
            <tr>
                <th>Qty</th>
                <th>Type</th>
                <th>Category</th>
                <th>Meta</th>
            </th>
        </thead>
        <tbody>

    @foreach($items as $item)
        <tr>
            <td>{{ number_format($item->qty) }}</td>
            <td>{{ $item->typeName }}</td>
            <td>{{ $item->category }}</td>
            <td>{{ $item->meta }}</td>
        </tr>
    @endforeach

        </tbody>
    </table>

@stop
