<template>
    <div v-if="paging.pageCount > 1">
        <nav aria-label="Page navigation">
          <ul class="pagination justify-content-center">
            <li :class="{'page-item': true, 'disabled': !paging.hasPrevious}">
                <a @click.prevent="pager.prevPage()" class="page-link" href="#previous-page" tabindex="-1">«</a>
            </li>
            <li v-for="pg in pagesList" :class="{'page-item': true, active: paging.page == pg-1}"><a @click.prevent="pager.goToPage(pg-1)" class="page-link" :href="'#page-'+pg">{{ pg }}</a></li>
            <li :class="{'page-item': true, 'disabled': !paging.hasNext}">
                <a @click.prevent="pager.nextPage()" class="page-link" href="#next-page">»</a>
            </li>
          </ul>
        </nav>
    </div>
</template>

<script>


    export default {
        props: {
            paging: Object,
            pager: Object,
            maxPagesToShow: Number
        },

        data() {
            return {
            }
        },
        methods: {
        },
        computed: {
            pagesList() {
                let pagesList = []

                let maxPagesToShow = this.maxPagesToShow || 9

                // calculate start and end
                let start = 1
                let end = this.paging.pageCount

                if (end - start >= maxPagesToShow) {
                    start = Math.max(Math.ceil(this.pager.getCurrentPage() + 1 - maxPagesToShow / 2), 1)
                    end = start + maxPagesToShow - 1

                    if (end > this.paging.pageCount) {
                        start = end - (maxPagesToShow - 1)
                    }
                }

                for (var n = start; n <= end; n++) {
                    pagesList.push(n)
                }

                return pagesList
            },
        }
    }



</script>
