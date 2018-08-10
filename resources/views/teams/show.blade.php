@extends('layouts.app')

@section('title')
{{ $team['name'] }} Team Stats
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <team-display
                team-data="{{ json_encode($team) }}"
            ></team-display>
        </div>
    </div>
</div>
@endsection
