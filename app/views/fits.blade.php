@extends('layout')

@section('content')

<div class="background">

    <div class="main">

        <h1>Ship Fittings</h1>

        {{ Form::open() }}

            <div class="form-actions">
                {{ Form::button('Create new fit', array('class' => 'form-button', 'id' => 'add_new_fit')) }}
            </div>

            <div class="new-fittings"></div>

            @foreach($fits as $fit)

                <div class="fitting">
                    <h4>{{ $fit->name }}</h4>
                    <textarea name="fit_{{ $fit->id }}">{{ $fit->eft_fitting }}</textarea>
                </div>

            @endforeach

            <div class="form-actions">
                {{ Form::submit('Save and Update', array('class' => 'form-button')) }}
            </div>

        {{ Form::close() }}

    </div>

</div>

@stop
