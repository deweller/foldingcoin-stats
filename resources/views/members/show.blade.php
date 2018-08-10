@extends('layouts.app')

@section('title')
{{ $member['userName'] }} Individual Stats
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <member-display
                member-data="{{ json_encode($member) }}"
            ></member-display>
        </div>
    </div>
</div>
@endsection
