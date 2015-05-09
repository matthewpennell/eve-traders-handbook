@extends('layout')

@section('content')

<div class="background">

    <div class="main">

        <h1>Settings</h1>

        {{ Form::open() }}

            @if ($api_key_id->value == '' || $api_key_verification_code->value == '')
                <div class="api-needed">
                    <h4>No API key detected</h4>
                    <p>To use the Eve Traders Handbook, you need to supply an EVE API key.</p>
                    <p><a href="https://community.eveonline.com/support/api-key/CreatePredefined?accessMask=25165832" target="_blank">Visit the EVE API Key Management site</a> and then <a href="#" class="show-api">enter your new API key details</a>.</p>
                </div>
            @endif

            <div class="api-details">
                <div class="form-field">
                    {{ Form::label($api_key_id->key, $api_key_id->label, array('class' => 'form-label')) }}
                    {{ Form::text($api_key_id->key, $api_key_id->value, array('class' => 'form-input')) }}
                </div>
                <div class="form-field">
                    {{ Form::label($api_key_verification_code->key, $api_key_verification_code->label, array('class' => 'form-label')) }}
                    {{ Form::text($api_key_verification_code->key, $api_key_verification_code->value, array('class' => 'form-input')) }}
                </div>
                <div class="form-field">
                    {{ Form::label($api_key_character_id->key, $api_key_character_id->label, array('class' => 'form-label')) }}
                    {{ Form::text($api_key_character_id->key, $api_key_character_id->value, array('class' => 'form-input')) }}
                </div>
            </div>

            <div class="form-field">
                {{ Form::label($alliances->key, $alliances->label, array('class' => 'form-label')) }}
                {{ Form::text($alliances->key, $alliances->value, array('class' => 'form-input')) }}
            </div>

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
