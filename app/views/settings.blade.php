@extends('layout')

@section('content')

<div class="background">

    <div class="main">

        <h1>Settings</h1>

        {{ Form::open() }}

            @foreach ($settings as $setting)
                @if ($setting->key != 'systems')
                    <div class="form-field">
                        {{ Form::label($setting->key, $setting->label, array('class' => 'form-label')) }}
                        {{ Form::text($setting->key, $setting->value, array('class' => 'form-input')) }}
                    </div>
                @endif
            @endforeach

            <div class="form-field">
                {{ Form::label('system-autocomplete', 'Start typing to select regions or systems:', array('class' => 'form-label')) }}
                {{ Form::text('system-autocomplete', '', array('class' => 'form-input')) }}
            </div>

            <p>Currently selected systems (click to remove):</p>

            <ul class="selected-systems">
                @foreach ($systems as $system)
                    <li>
                        <a href="#" class="remove-system" data-solarSystemID="{{ $system->solarSystemID }}">
                            {{ $system->solarSystemName }} ({{ $system->region->regionName }})
                        </a>
                    </li>
                @endforeach
            </ul>

            {{ Form::hidden('systems', $system_ids) }}

            {{ Form::label('Filters', 'Default filters', array('class' => 'form-label')) }}

            @foreach ($filters as $filter)
                <label class="inline-checkbox">
                    {{ Form::checkbox($filter->categoryName, $filter->categoryName, $filter->is_default) }}
                    {{ $filter->categoryName }}
                </label>
            @endforeach

            <div class="form-actions">
                {{ Form::submit('Save and Update', array('class' => 'form-button')) }}
            </div>

        {{ Form::close() }}

    </div>

</div>

<script>

    var systemsAndRegions = {{ $js_object }};

</script>

@stop
