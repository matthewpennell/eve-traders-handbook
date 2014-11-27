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

    {{ Form::submit('Save and Update', array('class' => 'form-button')) }}

    {{ Form::close() }}

</div>

@stop
