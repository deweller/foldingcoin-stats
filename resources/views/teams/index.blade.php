@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        @include('layouts.includes.error-panel')

        <div class="col-md-12">
            <team-list
            ></team-list>
        </div>
    </div>
</div>
@endsection
