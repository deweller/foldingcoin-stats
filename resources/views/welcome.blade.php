@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <h2 class="mt-3 mb-5 text-center">FoldingCoin Statistics</h2>

            <home-chart-component
                :stats-begin-date="'{{$statsBeginDate}}'"
            ></home-chart-component>
        </div>
    </div>
</div>
@endsection
