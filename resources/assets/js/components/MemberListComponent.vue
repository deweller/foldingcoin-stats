<template>
    <div>

        <error-panel
            :errormsg="errorMsg"
        ></error-panel>

        <div 
            v-if="members.length > 0 || loading || paging.isSearch"
            :class="{loading: loading}"
        >
            <form
                v-if="!compact"
                @submit.prevent="doSearch" class="mt-4 mb-2">
                <h5>Filter Results</h5>
                <div class="form-row align-items-center">
                    <div class="col-md-4">
                        <label class="sr-only" for="SearchUsername">Username</label>
                        <div class="input-group mb-2">
                            <div class="input-group-prepend">
                                <div class="input-group-text">Username</div>
                            </div>
                            <input v-model="searchUsername" type="text" class="form-control" id="SearchUsername" placeholder="">
                        </div>
                    </div>
                    <div class="col-md-5">
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
            <form
                v-if="compact"
                @submit.prevent="doSearch" class="mt-2 mb-1">
                <div class="form-row align-items-center">
                    <div class="col-8">
                        <label class="sr-only" for="SearchUsername">Username</label>
                        <div class="input-group input-group-sm mb-2">
                            <div class="input-group-prepend">
                                <div class="input-group-text">Username</div>
                            </div>
                            <input v-model="searchUsername" type="text" class="form-control" id="SearchUsername" placeholder="">
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="input-group input-group-sm">
                            <button type="submit" class="btn btn-sm btn-fldcdarkred mb-2 mr-1">Search</button>
                            <button @click="clearSearch" type="button" class="btn btn-sm btn-secondary mb-2">Clear</button>
                        </div>
                    </div>
                </div>
            </form>

            <table v-if="members.length > 0" class="table table-sm">
                <thead>
                    <tr>
                        <th><a @click="pager.toggleSort('userName', 'asc')" href="#sort">
                            <i v-if="paging.sort == 'userName'" :class="{fa: true, 'fa-sort-down': paging.sortDirection == 'desc', 'fa-sort-up': paging.sortDirection == 'asc'}"></i> 
                            Username</a></th>
                        <th v-if="!compact" class="d-none d-md-block">Address</th>
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
                        <td v-if="!compact" class="d-none d-md-block"><span :title="member.bitcoinAddress">{{ member.bitcoinAddress | shortbitcoinaddress }}</span></td>

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
        props: {
            apiUrl: String,
            perPage: String,
            compact: {
                type: Boolean,
                default: false
            }
        },
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

                this.reformatUsernameSearchFields()

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
            },

            reformatUsernameSearchFields() {
                // check for FriendlyName_TAG_BitcoinAddress format
                let re = new RegExp('([^_]+)_([^_]+)_([^_]+)');
                let matches = re.exec(''+this.searchUsername)
                if (matches != null) {
                    this.searchUsername = matches[1]
                    this.searchBitcoinAddress = matches[3]
                }
            }
        },

        mounted: async function() {
            this.pager = Pager.init({
                onLoad: this.onLoad,
                onLoadComplete: this.onLoadComplete,
                onError: this.setError,
                request: this.$request,

                url: this.apiUrl,
                defaultSort: 'allPoints',
                defaultSortDirection: 'desc',
                perPage: this.perPage,
            })


            this.loadMemberData()
        }
    }



</script>
