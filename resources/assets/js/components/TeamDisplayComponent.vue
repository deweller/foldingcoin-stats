<template>
    <div v-if="team.name">
        <error-panel
            :errormsg="errorMsg"
        ></error-panel>


        <div class="row">
            <div class="col-md-8">
                <h4 class="mb-3"><i class="fa fa-user mr-2"></i> Information for {{ team.name }}</h4>
                <table class="table table-sm table-striped">
                    <thead>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Team Name</strong></td>
                            <td>{{ team.name }}</td>
                        </tr>
                        <tr>
                            <td><strong>Team Number</strong></td>
                            <td>{{ team.number }}</td>
                        </tr>
                        <tr>
                            <td><strong>All Time Points</strong></td>
                            <td>{{ team.allPoints | points }}</td>
                        </tr>
                        <tr>
                            <td><strong>Points this Week</strong></td>
                            <td>{{ team.weekPoints | points }}</td>
                        </tr>
                        <tr>
                            <td><strong>Points in Last 24 hours</strong></td>
                            <td>{{ team.dayPoints | points }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>

        <!-- team chart -->
        <h2 class="mt-3">Points</h2>
        <chart-component
            :stats-url="'/api/v1/stats/team/'+team.number"
            :y-axis-title="'points for ' + team.name"
        ></chart-component>

    </div>


</template>

<script>

    export default {
        props: {
            teamData: String,
        },
        data() {
            return {
                errorMsg: null,

                team: {}
            }
        },
        methods: {
            setError(errorMsg) {
                this.errorMsg = errorMsg
            },
        },

        mounted: function() {
            console.log('this.teamData', this.teamData);
            this.team = JSON.parse(this.teamData)
            console.log('this.team', this.team);
        }
    }



</script>
