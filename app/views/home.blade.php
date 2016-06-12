@extends('layout')

@section('content')

    <div class="background">

        <div class="main">

            <h2>Losses in selected system(s)</h2>

            <table class="losses">
                <thead>
                    <tr>
                        <th class="num">Qty</th>
                        <th>Type</th>
                        <th>Category</th>
                        <th>Meta</th>
                        <th class="num">Π/day</th>
                    </th>
                </thead>
                <tfoot>
                    <tr>
                        <td colspan="5">
                            @if ($page > 2)
                                <a href="?{{ $filter_url }}" class="ffwd">&#9666;&#9666;</a>
                            @endif
                            @if ($page > 1)
                                <a href="?page={{ $page - 1 }}{{ $filter_url }}">&#9666;</a>
                            @endif
                            Page {{ $page }} of {{ ceil($pages) }}
                            @if ($page <= ceil($pages) - 1)
                                <a href="?page={{ $page + 1 }}{{ $filter_url }}">&#9656;</a>
                            @endif
                            @if ($page <= ceil($pages) - 2)
                                <a href="?page={{ ceil($pages) }}{{ $filter_url }}" class="ffwd">&#9656;&#9656;</a>
                            @endif
                        </td>
                    </tr>
                </tfoot>
                <tbody>
                    @foreach($items as $item)
                        <tr>
                            <td class="num">
                                @if ($item->allowManufacture)
                                    <img src="i/BPO.png" class="industry" title="This item can be manufactured by players" alt="Blueprint">&nbsp;
                                @endif
                                {{ number_format($item->qty) }}</td>
                            <td class="t{{ $item->typeID }}">
                                <a href="details/{{ $item->typeID }}">{{ $item->typeName }}</a>
                                @if ($item->profitIndustry || $item->profitImport)
                                    <?php $profit = ($item->profitIndustry > $item->profitImport) ? $item->profitIndustry : $item->profitImport; ?>
                                    <span class="{{ ($profit > 0) ? 'profit' : 'loss' }} {{ ($item->profitIndustry > $item->profitImport) ? 'local' : 'import' }}">{{ number_format(round($profit)) }}</span>
                                    @if ($item->manufactureCost > 0)
                                        <span class="percentage {{ ($profit > 0) ? 'profit' : 'loss' }}">{{ number_format(round($profit / $item->manufactureCost * 100)) }}%</span>
                                    @endif
                                @endif
                            </td>
                            <td>{{ $item->categoryName }}</td>
                            <td>{{ $item->metaGroupName }}</td>
                            <td class="num">
                                @if ($item->profitIndustry || $item->profitImport)
                                    <span class="{{ ($profit > 0) ? 'profit' : 'loss' }}">{{ number_format(round($profit * $item->qty / $days_running)) }}</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="details"></div>

        </div>

        <?php echo $sidebar; ?>

    </div>

@stop
