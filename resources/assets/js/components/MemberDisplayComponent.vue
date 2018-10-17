<template>
    <div v-if="member.userName">
        <error-panel
            :errormsg="errorMsg"
        ></error-panel>


        <div class="row">
            <div class="col-md-6">
                <h4 class="mb-3"><i class="fa fa-user mr-2"></i> Information for {{ member.friendlyName }}</h4>
                <table class="table table-sm table-striped">
                    <thead>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>User Name</strong></td>
                            <td>{{ member.friendlyName }}</td>
                        </tr>
                        <tr>
                            <td><strong>Bitcoin Address</strong></td>
                            <td>{{ member.bitcoinAddress }}</td>
                        </tr>
                        <tr>
                            <td><strong>All Time Points</strong></td>
                            <td>{{ member.allPoints | points }}</td>
                        </tr>
                        <tr>
                            <td><strong>Points this Week</strong></td>
                            <td>{{ member.weekPoints | points }}</td>
                        </tr>
                        <tr style="display: none;">
                            <td><strong>Points in Last 24 hours</strong></td>
                            <td>{{ member.dayPoints | points }}</td>
                        </tr>

                        <tr>
                            <td><strong>All Time Work Units</strong></td>
                            <td>{{ member.allWorkUnits | points }}</td>
                        </tr>
                        <tr>
                            <td><strong>Work Units this Week</strong></td>
                            <td>{{ member.weekWorkUnits | points }}</td>
                        </tr>
                        <tr style="display: none;">
                            <td><strong>Work Units in Last 24 hours</strong></td>
                            <td>{{ member.dayWorkUnits | points }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="col-md-1">
            </div>

            <div class="col-md-5">
                <h4 class="mb-3"><i class="fa fa-people-carry mr-2"></i> Teams</h4>
                <table class="table table-sm table-striped">
                    <thead>
                        <tr>
                            <th>Team Name</th>
                            <th>Team Number</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="team in member.teams">
                            <td><a :href="'/team/'+team.number">{{ team.name }}</a></td>
                            <td><a :href="'/team/'+team.number">{{ team.number }}</a></td>
                        </tr>

                    </tbody>
                </table>
            </div>

        </div>

        <!-- member chart -->
        <h2 class="mt-3">Points</h2>
        <chart-component
            :stats-url="'/api/v1/stats/member/'+member.userName"
            :y-axis-title="'Daily points for ' + member.friendlyName"
        ></chart-component>

    </div>


</template>

<script>

    export default {
        props: {
            memberData: String,
        },
        data() {
            return {
                errorMsg: null,

                member: {}
            }
        },
        methods: {
            setError(errorMsg) {
                this.errorMsg = errorMsg
            },
        },

        mounted: function() {
            this.member = JSON.parse(this.memberData)
        }
    }



</script>
