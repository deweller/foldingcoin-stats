@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <h2 class="mt-3 mb-5 text-center">Statistics for all Folders</h2>

            <chart-component
                stats-url="/api/v1/stats/all"
                y-axis-title="Total daily FAH points"
            ></chart-component>

            <div class="row justify-content-center">
                <div class="col-md-6">
                    <rank-component
                        rank-url="/api/v1/ranked/teams"
                        rank-type="teams"
                        limit="10"
                    ></rank-component>

                    <div class="text-right"><a href="/teams">More teams <i class="fas fa-angle-double-right"></i></a></div>
                </div>


                <div class="col-md-6">
                    <rank-component
                        rank-url="/api/v1/ranked/members"
                        rank-type="members"
                        limit="10"
                    ></rank-component>

                    <div class="text-right"><a href="/members">More members <i class="fas fa-angle-double-right"></i></a></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
