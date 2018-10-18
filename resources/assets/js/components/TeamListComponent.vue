<template>
    <div>

        <error-panel
            :errormsg="errorMsg"
        ></error-panel>

        <div :class="{loading: loading}" v-if="teams.length > 0 || loading || paging.isSearch">
            <h3>FoldingCoin Teams</h3>

            <form @submit.prevent="doSearch" class="mt-4 mb-2">
                <h5>Filter Results</h5>
                <div class="form-row align-items-center">
                    <div class="col-md-3">
                        <label class="sr-only" for="SearchName">Name</label>
                        <div class="input-group mb-2">
                            <div class="input-group-prepend">
                                <div class="input-group-text">Name</div>
                            </div>
                            <input v-model="searchName" type="text" class="form-control" id="SearchName" placeholder="">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="sr-only" for="SearchTeamNumber">Team Number</label>
                        <div class="input-group mb-2">
                            <div class="input-group-prepend">
                                <div class="input-group-text">Team Number</div>
                            </div>
                            <input v-model="searchNumber" type="text" class="form-control" id="SearchTeamNumber" placeholder="">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-fldcdarkred mb-2">Search</button>
                        <button @click="clearSearch" type="button" class="btn btn-secondary mb-2">Clear Search</button>
                    </div>
                </div>
            </form>

            <table v-if="teams.length > 0" class="table table-sm">
                <thead>
                    <tr>
                        <th><a @click="pager.toggleSort('name', 'asc')" href="#sort">
                            <i v-if="paging.sort == 'name'" :class="{fa: true, 'fa-sort-down': paging.sortDirection == 'desc', 'fa-sort-up': paging.sortDirection == 'asc'}"></i> 
                            Name</a></th>
                        <th class="d-none d-md-block"><a @click="pager.toggleSort('number', 'asc')" href="#sort">
                            <i v-if="paging.sort == 'number'" :class="{fa: true, 'fa-sort-down': paging.sortDirection == 'desc', 'fa-sort-up': paging.sortDirection == 'asc'}"></i> 
                            Team Number</a></th>
                        <!--<th><a @click="pager.toggleSort('dayPoints', 'desc')" href="#sort">
                            <i v-if="paging.sort == 'dayPoints'" :class="{fa: true, 'fa-sort-down': paging.sortDirection == 'desc', 'fa-sort-up': paging.sortDirection == 'asc'}"></i> 
                            24h Points</a></th>-->
                        <th class="d-none d-sm-block"><a @click="pager.toggleSort('weekPoints', 'desc')" href="#sort">
                            <i v-if="paging.sort == 'weekPoints'" :class="{fa: true, 'fa-sort-down': paging.sortDirection == 'desc', 'fa-sort-up': paging.sortDirection == 'asc'}"></i> 
                            7d Points</a></th>
                        <th><a @click="pager.toggleSort('allPoints', 'desc')" href="#sort">
                            <i v-if="paging.sort == 'allPoints'" :class="{fa: true, 'fa-sort-down': paging.sortDirection == 'desc', 'fa-sort-up': paging.sortDirection == 'asc'}"></i> 
                            Total Points</a></th>
                    </tr>
                </thead>

                <tbody>
                    <tr v-for="team in teams">
                        <td><a :href="'/team/'+team.number">{{ team.name }}</a></td>
                        <td class="d-none d-md-block"><a :href="'/team/'+team.number">{{ team.number }}</a></td>

                        <!--<td>{{ team.dayPoints | points }}</td>-->
                        <td class="d-none d-sm-block">{{ team.weekPoints | points }}</td>
                        <td>{{ team.allPoints | points }}</td>
                    </tr>
                </tbody>
            </table>
            <div v-if="teams.length == 0" class="">
                <p>No results found for this search.</p>
            </div>

            <div class="text-center" v-if="paging.count > 0">Showing {{ teams.length }} of {{ paging.count }} Folding Teams</div>
            <paging
                :pager="pager"
                :paging="paging"
            ></paging>
         </div>

        <!-- no teams -->
        <div v-else>No teams are available to show.</div>

    </div>
</template>

<script>

    let Pager = require('../lib/Pager')

    export default {
        data() {
            return {
                errorMsg: null,
                
                teams: [],
                paging: {},

                loading: false,

                searchName: '',
                searchNumber: '',
            }
        },
        methods: {
            setError(errorMsg) {
                this.errorMsg = errorMsg
            },

            showLoading() {

            },

            async loadTeamData() {
                this.loading = true
                this.pager.load()
            },

            onLoad() {
                this.loading = true
            },

            onLoadComplete(teams, paging) {
                this.teams = teams
                this.paging = paging
                this.loading = false
            },

            doSearch() {
                let vars = {}
                if (this.searchName.length > 0) {
                    vars.name = this.searchName
                }
                if (this.searchNumber.length > 0) {
                    vars.number = this.searchNumber
                }
                
                this.pager.search(vars)
            },

            clearSearch() {
                this.searchName = ''
                this.searchNumber = ''
                this.pager.search({})
            }
        },

        mounted: async function() {
            this.pager = Pager.init({
                onLoad: this.onLoad,
                onLoadComplete: this.onLoadComplete,
                onError: this.setError,
                request: this.$request,

                url: '/api/v1/teams',
                defaultSort: 'allPoints',
                defaultSortDirection: 'desc',
                perPage: 100,
            })


            this.loadTeamData()
        }
    }



</script>
