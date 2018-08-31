<template>
    <div v-if="team.name">
        <error-panel
            :errormsg="errorMsg"
        ></error-panel>


        <div class="row">
            <div class="col-md-6">
                <h4 class="mb-3"><i class="fa fa-people-carry mr-2"></i> Information for {{ team.name }}</h4>
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

            <div class="col-md-6">
                <h4 class="mb-3"><i class="fa fa-users mr-2"></i> Members in team {{ team.name }}</h4>
                <table v-if="members.length > 0" class="table table-sm">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Address</th>
                            <th>24h Points</th>
                            <th>7d Points</th>
                            <th>Total Points</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr v-for="member in members">
                            <td><a :href="'/member/'+member.userName">{{ member.friendlyName }}</a></td>
                            <td><span :title="member.bitcoinAddress">{{ member.bitcoinAddress | shortbitcoinaddress }}</span></td>
                            <td>{{ member.dayPoints | points }}</td>
                            <td>{{ member.weekPoints | points }}</td>
                            <td>{{ member.allPoints | points }}</td>
                        </tr>
                    </tbody>
                </table>
                <div v-if="members.length == 0" class="">
                    <p>No members found.</p>
                </div>
            </table>
            </div>

        </div>

        <!-- team chart -->
        <h2 class="mt-3">Points</h2>
        <chart-component
            :stats-url="'/api/v1/stats/team/'+team.number"
            :y-axis-title="'Daily points for ' + team.name"
        ></chart-component>

    </div>


</template>

<script>
    let Pager = require('../lib/Pager')

    export default {
        props: {
            teamData: String,
        },
        data() {
            return {
                errorMsg: null,

                paging: {},

                team: {},
                members: [],
                loading: false,
                loaded: false,
            }
        },
        methods: {
            setError(errorMsg) {
                this.errorMsg = errorMsg
            },

            onLoad() {
                this.loading = true
            },
            onLoadComplete(members, paging) {
                this.members = members
                this.paging = paging
                this.loading = false
            },


            // async loadTeamMembers() {
            //     this.loading = true

            //     let params = {}
            //     let response = await this.$request.get('/api/v1/team/'+this.team.number+'/members', {params}, this.setError)
            //     this.members = response.items
            //     console.log('this.members', this.members);

            //     this.loading = false
            //     this.loaded = true
            // },
        },

        mounted: function() {
            // console.log('this.teamData', this.teamData);
            this.team = JSON.parse(this.teamData)
            // this.loadTeamMembers()
            // console.log('this.team', this.team);

            this.pager = Pager.init({
                onLoad: this.onLoad,
                onLoadComplete: this.onLoadComplete,
                onError: this.setError,
                request: this.$request,

                url: '/api/v1/team/'+this.team.number+'/members',
                defaultSort: 'allPoints',
                defaultSortDirection: 'desc',
                perPage: 10,
            })
            this.pager.load()
        }
    }



</script>
