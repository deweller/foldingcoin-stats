export function init(config) {
    config = config || {}

    let opts = {
        url: config.url || null,

        onError: config.onError || null,
        onLoad: config.onLoad || null,
        onLoadComplete: config.onLoadComplete || null,

        $request: config.request || null,

        perPage: config.perPage || 20,
    }    

    let currentPage = 0
    let currentPageCount = 0
    let currentSort = config.defaultSort || null
    let currentSortDirection = config.defaultSortDirection || 'desc'
    let currentSearchVars = {}
    let currentSearchExists = false

    let exports = {}

    init = function() {
    }

    exports.prevPage = function() {
        currentPage = currentPage - 1
        if (currentPage < 0) {
            currentPage = 0
        }
        exports.load()
    }

    exports.nextPage = function() {
        currentPage = currentPage + 1
        if (currentPage > currentPageCount - 1) {
            currentPage = currentPageCount - 1
        }
        exports.load()
    }

    exports.goToPage = function(pg) {
        // don't reload
        if (currentPage == pg) {
            return
        }

        currentPage = pg
        if (currentPage < 0) {
            currentPage = 0
        }
        if (currentPage > currentPageCount - 1) {
            currentPage = currentPageCount - 1
        }
        exports.load()
    }

    exports.toggleSort = function(field, defaultDirection) {
        let direction = defaultDirection || 'desc'
        if (currentSort != null && field == currentSort) {
            direction = currentSortDirection == 'asc' ? 'desc' : 'asc'
        }

        currentSort = field
        currentSortDirection = direction

        exports.load()
    }

    exports.search = function(searchVars) {
        // save the search vars
        currentSearchVars = searchVars
        currentSearchExists = false
        Object.keys(currentSearchVars).forEach((key,index) => {
            currentSearchExists = true
        })

        // reset to page 0
        currentPage = 0

        exports.load()
    }

    function buildRequestParams() {
        let requestParams = {}

        // page
        requestParams.pg = currentPage
        requestParams.limit = opts.perPage

        // sort
        requestParams.sort = `${currentSort} ${currentSortDirection}`

        // search
        Object.keys(currentSearchVars).forEach((key,index) => {
            requestParams[key] = currentSearchVars[key]
        })

        return requestParams
    }

    exports.load = async function() {
        let requestParams = buildRequestParams()

        // call onload
        if (opts.onLoad) {
            opts.onLoad(opts.url, requestParams)
        }

        // save these for returning the data
        let sort = currentSort
        let sortDirection = currentSortDirection
        let isSearch = currentSearchExists

        let response = await opts.$request.get(opts.url, {params: requestParams}, opts.onError)

        // save paging
        currentPageCount = response.pageCount
        currentPage = response.page

        let paging = {
            hasPrevious: !!(currentPage > 0),
            hasNext: !!(currentPage < currentPageCount - 1),
            isSearch: isSearch,

            sort: sort,
            sortDirection: sortDirection,

            page: response.page,
            perPage: response.perPage,
            pageCount: response.pageCount,
            count: response.count,
        }

        if (opts.onLoadComplete) {
            opts.onLoadComplete(response.items, paging)
        }
    }

    init()

    return exports
}