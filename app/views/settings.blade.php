@extends('layout')

@section('content')

<div class="background">

    <h1>Settings</h1>

    {{ Form::open() }}

    @foreach($settings as $setting)
        <div class="form-field">
            {{ Form::label($setting->key, $setting->label, array('class' => 'form-label')) }}
            {{ Form::text($setting->key, $setting->value, array('class' => 'form-input')) }}
        </div>
    @endforeach

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

@stop
