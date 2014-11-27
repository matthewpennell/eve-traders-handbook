<form method="get" action="" class="filters">
    <h3>Filters</h3>
    @foreach ($filters as $filter)
        <div class="filter">
            <label>
                {{ Form::checkbox('filter[]', $filter->categoryName, $active_filters && in_array($filter->categoryName, $active_filters)) }}
                {{ $filter->categoryName }}
            </label>
        </div>
    @endforeach
</form>
