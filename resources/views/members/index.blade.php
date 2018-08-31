@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        @include('layouts.includes.error-panel')

        <div class="col-md-12">
            <h3>All FoldingCoin Participants</h3>

            <member-list
                api-url="/api/v1/members"
                per-page="100"
                :compact="false"
            ></member-list>
        </div>
    </div>
</div>
@endsection
