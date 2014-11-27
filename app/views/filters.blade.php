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

    <div class="filter">
        <label>
            {{ Form::checkbox('meta[]', 'Tech I', $active_meta_filters && in_array('Tech I', $active_meta_filters)) }}
            Tech I
        </label>
    </div>
    <div class="filter">
        <label>
            {{ Form::checkbox('meta[]', 'Tech II', $active_meta_filters && in_array('Tech II', $active_meta_filters)) }}
            Tech II
        </label>
    </div>

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
