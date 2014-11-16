@extends('layout')

@section('content')
    @foreach($users as $user)
        <p><a href="mailto:{{ $user->email }}">{{ $user->name }}</a></p>
    @endforeach
@stop
