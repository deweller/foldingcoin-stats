<template>
    <div>

        <error-panel
            :errormsg="errorMsg"
        ></error-panel>

        <div :class="{loading: loading}" v-if="members.length > 0 || loading || paging.isSearch">
            <h3>FoldingCoin Participants</h3>

            <form @submit.prevent="doSearch" class="mt-4 mb-2">
                <h5>Filter Results</h5>
                <div class="form-row align-items-center">
                    <div class="col-md-3">
                        <label class="sr-only" for="SearchUsername">Username</label>
                        <div class="input-group mb-2">
                            <div class="input-group-prepend">
                                <div class="input-group-text">Username</div>
                            </div>
                            <input v-model="searchUsername" type="text" class="form-control" id="SearchUsername" placeholder="">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="sr-only" for="SearchBitcoinAddress">Bitcoin Address</label>
                        <div class="input-group mb-2">
                            <div class="input-group-prepend">
                                <div class="input-group-text">Address</div>
                            </div>
                            <input v-model="searchBitcoinAddress" type="text" class="form-control" id="SearchBitcoinAddress" placeholder="">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-fldcdarkred mb-2">Search</button>
                        <button @click="clearSearch" type="button" class="btn btn-secondary mb-2">Clear Search</button>
                    </div>
                </div>
            </form>

            <table v-if="members.length > 0" class="table table-sm">
                <thead>
                    <tr>
                        <th><a @click="pager.toggleSort('userName', 'asc')" href="#sort">
                            <i v-if="paging.sort == 'userName'" :class="{fa: true, 'fa-sort-down': paging.sortDirection == 'desc', 'fa-sort-up': paging.sortDirection == 'asc'}"></i> 
                            Username</a></th>
                        <th class="d-none d-md-block">Address</th>
                        <th><a @click="pager.toggleSort('dayPoints', 'desc')" href="#sort">
                            <i v-if="paging.sort == 'dayPoints'" :class="{fa: true, 'fa-sort-down': paging.sortDirection == 'desc', 'fa-sort-up': paging.sortDirection == 'asc'}"></i> 
                            24h Points</a></th>
                        <th><a @click="pager.toggleSort('weekPoints', 'desc')" href="#sort">
                            <i v-if="paging.sort == 'weekPoints'" :class="{fa: true, 'fa-sort-down': paging.sortDirection == 'desc', 'fa-sort-up': paging.sortDirection == 'asc'}"></i> 
                            7d Points</a></th>
                        <th><a @click="pager.toggleSort('allPoints', 'desc')" href="#sort">
                            <i v-if="paging.sort == 'allPoints'" :class="{fa: true, 'fa-sort-down': paging.sortDirection == 'desc', 'fa-sort-up': paging.sortDirection == 'asc'}"></i> 
                            Total Points</a></th>
                    </tr>
                </thead>

                <tbody>
                    <tr v-for="member in members">
                        <td><a :href="'/member/'+member.userName">
                            <span class="d-block d-md-none text-truncate" style="max-width: 120px;">
                                {{ member.friendlyName }}
                            </span>
                            <span class="d-none d-md-block text-truncate" style="max-width: 160px;">
                                {{ member.friendlyName }}
                            </span>
                        </a></td>
                        <td class="d-none d-md-block"><span :title="member.bitcoinAddress">{{ member.bitcoinAddress | shortbitcoinaddress }}</span></td>

                        <td>{{ member.dayPoints | points }}</td>
                        <td>{{ member.weekPoints | points }}</td>
                        <td>{{ member.allPoints | points }}</td>
                    </tr>
                </tbody>
            </table>
            <div v-if="members.length == 0" class="">
                <p>No results found for this search.</p>
            </div>

            <div class="text-center" v-if="paging.count > 0">Showing {{ members.length }} of {{ paging.count }} Folding Members</div>
            <paging
                :pager="pager"
                :paging="paging"
            ></paging>
        </div>

        <!-- no members -->
        <div v-else>No members are available to show.</div>

    </div>
</template>

<script>

    let Pager = require('../lib/Pager')

    export default {
        data() {
            return {
                errorMsg: null,
                
                members: [],
                paging: {},

                loading: false,

                searchUsername: '',
                searchBitcoinAddress: '',
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
                this.pager.load()
            },

            onLoad() {
                this.loading = true
            },

            onLoadComplete(members, paging) {
                this.members = members
                this.paging = paging
                this.loading = false
            },

            doSearch() {
                let vars = {}
                if (this.searchUsername.length > 0) {
                    vars.userName = this.searchUsername
                }
                if (this.searchBitcoinAddress.length > 0) {
                    vars.bitcoinAddress = this.searchBitcoinAddress
                }
                
                this.pager.search(vars)
            },

            clearSearch() {
                this.searchUsername = ''
                this.searchBitcoinAddress = ''
                this.pager.search({})
            }
        },

        mounted: async function() {
            this.pager = Pager.init({
                onLoad: this.onLoad,
                onLoadComplete: this.onLoadComplete,
                onError: this.setError,
                request: this.$request,

                url: '/api/v1/members',
                defaultSort: 'allPoints',
                defaultSortDirection: 'desc',
                perPage: 100,
            })


            this.loadMemberData()
        }
    }



</script>
