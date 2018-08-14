<template>
    <div v-if="loaded">

        <div :class="{loading: loading}" v-if="results.length > 0 || loading ">
            <template v-if="rankType == 'members'">
                <!-- members -->
                <h4 class="mb-3"><i class="fa fa-user mr-2"></i> Top All Time Folders</h4>
                <table class="table table-sm table-striped">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Member Name</th>
                            <th>Points</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="result in results">
                            <td>{{ result.allRank }}</td>
                            <td><a :href="'/member/'+result.userName">{{ result.friendlyName }}</a></td>
                            <td>{{ result.allPoints | points }}</td>
                        </tr>

                    </tbody>
                </table>
            </template>

            <template v-if="rankType == 'teams'">
                <!-- teams -->
                <h4 class="mb-3"><i class="fa fa-people-carry mr-2"></i> Top All Time Teams</h4>
                <table class="table table-sm table-striped">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Team Name</th>
                            <th>Points</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="result in results">
                            <td>{{ result.allRank }}</td>
                            <td><a :href="'/team/'+result.number">{{ result.name }}</a></td>
                            <td>{{ result.allPoints | points }}</td>
                        </tr>

                    </tbody>
                </table>
            </template>

        </div>

        <!-- no results -->
        <div v-else>No results are available to show.</div>

    </div>
</template>

<script>

    export default {
        props: {
            rankUrl: String,
            rankType: String,
            rankLabel: String,
        },

        data() {
            return {
                errorMsg: null,
                loading: false,
                loaded: false,

                results: [],
            }
        },
        methods: {
            setError(errorMsg) {
                this.errorMsg = errorMsg
            },

            async loadRankings() {
                this.loading = true

                let params = {}
                console.log('this.rankUrl', this.rankUrl);
                let response = await this.$request.get(this.rankUrl, {params}, this.setError)
                this.results = response.items

                this.loading = false
                this.loaded = true
            },
        },

        mounted: async function() {
            this.loadRankings()
        }
    }



</script>
