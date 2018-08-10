@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <h2 class="mt-3 mb-5 text-center">FoldingCoin Statistics</h2>

            <chart-component
                stats-url="/api/v1/stats/all"
                y-axis-title="Cumulative Points"
            ></chart-component>
        </div>
    </div>
</div>
@endsection
