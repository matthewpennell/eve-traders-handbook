<div class="import-information">

    <div class="import-timestamp">
        <strong>Initial import</strong>
        {{ $initial_import }}
    </div>

    <div class="import-timestamp">
        <strong>Last import</strong>
        {{ $last_import }}
    </div>

</div>

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

    <h3>Search</h3>

    <div class="filter">
        <input type="search" name="q" class="search" placeholder="Enter search term..." value="{{ $search_term }}">
    </div>

    <h3>Tech/Meta level</h3>

    @foreach ($meta_filters as $meta_filter)
        <div class="filter">
            <label>
                {{ Form::checkbox('meta[]', $meta_filter, $active_meta_filters && in_array($meta_filter, $active_meta_filters)) }}
                {{ $meta_filter }}
            </label>
        </div>
    @endforeach

    <h3>Blueprint available</h3>

    <div class="filter">
        <label>
            {{ Form::checkbox('blueprint[]', 'Yes', $active_blueprint_filters && in_array('Yes', $active_blueprint_filters)) }}
            Yes
        </label>
    </div>
    <div class="filter">
            <label>
            {{ Form::checkbox('blueprint[]', 'No', $active_blueprint_filters && in_array('No', $active_blueprint_filters)) }}
            No
        </label>
    </div>

</form>
