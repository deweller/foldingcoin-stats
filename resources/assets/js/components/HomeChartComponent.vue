<template>
    <div>
        <error-panel
            :errormsg="errorMsg"
        ></error-panel>

        <div id="HomeStatsChart" style="width:100%; height:400px; margin-bottom: 0px;"></div>

        <div class="container text-center mb-5">
            <!-- <h4>Chart Range</h4> -->
            <div id="ChartRange" class="btn-group" role="group" aria-label="Chart Range">
              <button @click="zoom='all'" type="button" v-bind:class="btnClass('all')">All Time</button>
              <button @click="zoom='30d'" type="button" v-bind:class="btnClass('30d')">30 Days</button>
              <button @click="zoom='7d'" type="button" v-bind:class="btnClass('7d')">7 Days</button>
              <button @click="zoom='24h'" type="button" v-bind:class="btnClass('24h')">24 Hours</button>
            </div>
        </div>
    </div>
</template>

<script>
    let moment = require('moment')
    let Highcharts = require('highcharts');

    const PERIOD_HOURLY = 1
    const PERIOD_DAILY = 2

    export default {
        props: {
            statsBeginDate: String,
        },
        data() {
            return {
                zoom: '30d',

                errorMsg: null,
            }
        },
        methods: {
            setError(errorMsg) {
                this.errorMsg = errorMsg
            },
            btnClass(btnValue) {
                return {
                    btn: true,
                    // 'btn-fldclightred': (btnValue == this.zoom),
                    'btn-fldcdarkred': (btnValue == this.zoom),
                    'btn-secondary': (btnValue != this.zoom),
                    // 'btn-fldcdarkred': (btnValue != this.zoom),
                }
            },
            async loadChartData() {
                this.chart.showLoading()

                let start
                let period = PERIOD_DAILY
                if (this.zoom.indexOf('d') >= 0) {
                    let startDays = parseInt(this.zoom.substr(0, this.zoom.length - 1))
                    if (Number.isNaN(startDays)) {
                        return;
                    }
                    start = moment().subtract(startDays, 'days').format()
                } else if (this.zoom.indexOf('h') >= 0) {
                    let startHours = parseInt(this.zoom.substr(0, this.zoom.length - 1))
                    if (Number.isNaN(startHours)) {
                        return;
                    }
                    start = moment().subtract(startHours, 'hours').format()
                    period = PERIOD_HOURLY
                } else if (this.zoom == 'all') {
                    start = moment(this.statsBeginDate).format()
                } else {
                    console.error('unknown zoom', ''+this.zoom);
                    return
                }
                let params = {
                    start: start,
                    // end: moment().format(),
                    period: period,
                }
                let response = await this.$request.get('/api/v1/stats/all', {params}, this.setError)
                setChartData(this.chart, response.stats, response.meta)
                this.chart.hideLoading()
            }
        },
        watch: {
            // whenever question changes, this function will run
            zoom: function (newZoom, oldZoom) {
                // console.log('newZoom:',newZoom);
                this.loadChartData()
            }
        },
        mounted: async function() {
            this.chart = buildChart()
            this.loadChartData()

            // once an hour, update the chart
            setInterval(() => {
                this.loadChartData()
            }, 3600000)
        }
    }

    function setChartData(chart, rawChartData, chartMeta) {
        let transformedChartData = []
        for (let chartEntry of rawChartData) {
            transformedChartData.push([
                parseInt(moment(chartEntry.start_date).format('x')),
                parseInt(chartEntry.points)
            ]);
        }
        // console.log('transformedChartData', transformedChartData);

        // set new data
        chart.series[0].setData(transformedChartData)

        // update floor and ceiling
        let floor = moment(chartMeta.start).format('x')
        let ceiling = moment(chartMeta.end).format('x')
        chart.xAxis[0].update({
            floor: floor,
            ceiling: ceiling,
        })

    }

    function buildChart() {
        Highcharts.setOptions({
            lang: {
                thousandsSep: ','
            }
        })

        let chart = Highcharts.chart('HomeStatsChart', {
            chart: {
                zoomType: 'x'
            },
            title: {
                // text: 'Combined FoldingCoin Points'
                text: null
            },
            xAxis: {
                type: 'datetime',
            },
            yAxis: {
                title: {
                    text: 'Cumulative points'
                }
            },
            colors: ["#900000", "#434348", "#90ed7d", "#f7a35c", "#8085e9", "#f15c80", "#e4d354", "#2b908f", "#f45b5b", "#91e8e1"],
            plotOptions: {
                area: {
                    fillColor: {
                        linearGradient: {
                            x1: 0,
                            y1: 0,
                            x2: 0,
                            y2: 1
                        },
                        stops: [
                            [0, "#810000"],
                            [1, "#D31511"]
                        ]
                    },
                    marker: {
                        radius: 1
                    },
                    lineWidth: 3,
                    states: {
                        hover: {
                            lineWidth: 4
                        }
                    },
                    threshold: null
                }
            },
            series: [{
                type: 'area',
                name: 'FAH Points',
                showInLegend: false,
                data: [],
                tooltip: {
                    enabled: true
                }
            }],
            credits: {
                enabled: false,
            }
        })

        return chart
    }

</script>
