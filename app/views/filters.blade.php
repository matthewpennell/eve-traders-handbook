<form method="get" action="" class="filters">

    <h3>Category</h3>

    @foreach ($filters as $filter)
        <div class="filter">
            <label>
                {{ Form::checkbox('filter[]', $filter->categoryName, $active_filters && in_array($filter->categoryName, $active_filters)) }}
                {{ $filter->categoryName }}
            </label>
        </div>
    @endforeach

    <h3>Tech/Meta level</h3>

    @foreach ($meta_filters as $meta_filter)
        <div class="filter">
            <label>
                {{ Form::checkbox('meta[]', $meta_filter, $active_meta_filters && in_array($meta_filter, $active_meta_filters)) }}
                {{ $meta_filter }}
            </label>
        </div>
    @endforeach

<!--
    <h3>Ship</h3>

    @foreach ($ships as $ship)
        <div class="filter">
            <label>
                {{ Form::checkbox('ship[]', $ship->shipName) }}
                {{ $ship->shipName }}
            </label>
        </div>
    @endforeach
-->

</form>
