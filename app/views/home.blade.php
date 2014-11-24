@extends('layout')

@section('content')

    <div class="background">

        <h2>Losses in selected system(s)</h2>

        <table class="losses">
            <thead>
                <tr>
                    <th class="num">Qty</th>
                    <th>Type</th>
                    <th>Category</th>
                    <th>Meta</th>
                </th>
            </thead>
            <tfoot>
                <tr>
                    <td colspan="4">
                        @if ($page > 2)
                            <a href="/?filter[]={{ implode('&filter[]=', $filters) }}">&laquo;</a>
                        @endif
                        @if ($page > 1)
                            <a href="/?page={{ $page - 1 }}&filter[]={{ implode('&filter[]=', $filters) }}"><</a>
                        @endif
                        Page {{ $page }} of {{ ceil($pages) }}
                        @if ($page <= ceil($pages) - 1)
                            <a href="/?page={{ $page + 1 }}&filter[]={{ implode('&filter[]=', $filters) }}">></a>
                        @endif
                        @if ($page <= ceil($pages) - 2)
                            <a href="/?page={{ ceil($pages) }}&filter[]={{ implode('&filter[]=', $filters) }}">&raquo;</a>
                        @endif
                    </td>
                </tr>
            </tfoot>
            <tbody>
                @foreach($items as $item)
                    <tr>
                        <td class="num">{{ number_format($item->qty) }}</td>
                        <td>
                            <a href="/details/{{ $item->typeID }}">{{ $item->typeName }}</a>
                            @if ($item->profitIndustry)
                                <span class="{{ $item->profitOrLoss }}">{{ number_format(round($item->profitIndustry)) }}</span>
                            @endif
                        </td>
                        <td>{{ $item->category }}</td>
                        <td>{{ $item->meta }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <form method="get" action="" class="filters">
            <h3>Filters</h3>
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

    </div>

@stop
