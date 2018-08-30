
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue');

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

 let RequestPlugin = require('./plugins/request').default
 Vue.use(RequestPlugin)

Vue.component('error-panel', require('./components/ErrorPanelComponent.vue'));

Vue.component('chart-component', require('./components/ChartComponent.vue'));
Vue.component('rank-component', require('./components/RankComponent.vue'));

Vue.component('member-list', require('./components/MemberListComponent.vue'));
Vue.component('member-display', require('./components/MemberDisplayComponent.vue'));

Vue.component('team-list', require('./components/TeamListComponent.vue'));
Vue.component('team-display', require('./components/TeamDisplayComponent.vue'));

Vue.component('paging', require('./components/Paging.vue'));

// vue filters
Vue.filter('shortbitcoinaddress', function (value) {
    if (value == null || value.length == 0) {
        return ''
    }

    let length = value.length
    if (length < 12) {
        return value
    }

    value = value.toString()
    return value.substr(0, 6) + '…' + value.substr(-4, length)
})

let numeral = require('numeral')
Vue.filter('points', function (value) {
    if (value == null || value.length == 0) {
        return ''
    }

    value = value.toString()
    return numeral(value).format('0,0')
})


const app = new Vue({
    el: '#app'
});


