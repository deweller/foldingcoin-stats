<template>
    <div>
        <error-panel
            :errormsg="errorMsg"
        ></error-panel>

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
            <tbody v-for="member in members">
                <tr>
                    <td><a :href="'/member/'+member.userName">{{ member.friendlyName }}</a></td>
                    <td><span :title="member.bitcoinAddress">{{ member.bitcoinAddress | shortbitcoinaddress }}</span></td>

                    <td>{{ member.dayPoints | points }}</td>
                    <td>{{ member.weekPoints | points }}</td>
                    <td>{{ member.allPoints | points }}</td>
                </tr>
            </tbody>
        </table>
        <div v-else>No members are available to show.</div>

    </div>
</template>

<script>
    export default {
        data() {
            return {
                errorMsg: null,
                members: [],

                loading: false,
            }
        },
        methods: {
            setError(errorMsg) {
                this.errorMsg = errorMsg
            },

            showLoading() {

            },

            async loadMemberData() {
                this.loading = true

                let start
                let params = {
                }
                let response = await this.$request.get('/api/v1/members', {params}, this.setError)
                this.members = response.items

                this.loading = false
            }
        },

        mounted: async function() {
            this.loadMemberData()
        }
    }



</script>
