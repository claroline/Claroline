'use strict';

statisticsApp
    .controller("statisticsViewController", ["$scope", "portfolioManager", "$filter", "$http", "urlInterpolator", "translationService",
        function($scope, portfolioManager, $filter, $http, urlInterpolator, translationService) {
            $scope.selectedPortfolio = '';
            $scope.period = {
                date: {startDate: moment().startOf('month'), endDate: moment().endOf('month')}
            };
            $scope.daterangepickerOptions = {
                ranges: window.ranges,
                separator: " " + translationService.trans('date_range.separator', 'platform') + " ",
                clearClass: "btn hide",
                eventHandlers: {
                    "apply.daterangepicker": fetchVisitData
                },
                locale: {
                    format: translationService.trans('date_range.js_format', 'platform'),
                    applyLabel: translationService.trans('date_range.apply', 'platform'),
                    clearLabel: translationService.trans('date_range.clear', 'platform'),
                    fromLabel: translationService.trans('date_range.from', 'platform'),
                    toLabel: translationService.trans('date_range.to', 'platform'),
                    weekLabel: translationService.trans('date_range.week', 'platform'),
                    customRangeLabel: translationService.trans('date_range.custom_range', 'platform'),
                    daysOfWeek: JSON.parse(translationService.trans('date_range.day_names', 'platform')),
                    monthNames: JSON.parse(translationService.trans('date_range.month_names', 'platform')),
                    firstDay: parseInt(translationService.trans('date_range.first_day', 'platform'))
                }
            };
            $scope.chartData = [];
            $scope.fetchingChartData = false;

            var bg_color = "transparent";
            if (navigator.userAgent.match(/msie/i) && navigator.userAgent.match(/8/)) bg_color = "#fff";

            $scope.chartOptions = {
                title: {show: false},
                grid: {
                    drawBorder: true,
                    borderWidth: 1.0,
                    shadow: false,
                    background: bg_color
                },
                axesDefaults: {
                    labelRenderer: $.jqplot.CanvasAxisLabelRenderer,
                    tickRenderer: $.jqplot.CanvasAxisTickRenderer
                },
                axes: {
                    xaxis: {
                        renderer: $.jqplot.DateAxisRenderer,
                        tickOptions: {
                            formatString: translationService.trans('jqplot_date_output_format', 'platform'),
                            showGridline: false,
                            showMark: true,
                            angle: -20,
                            fontSize: '10px'
                        },
                        numberTicks:10
                    },
                    yaxis: {
                        min:0,
                        showTickMarks: true,
                        numberTicks: 5
                    }
                },
                highlighter: {
                    show: true,
                    sizeAdjust: 1,
                    tooltipOffset:12,
                    tooltipLocation:'n',
                    tooltipAxes:'xy',
                    formatString:'%s <br/> %d',
                    tooltipFadeSpeed:'fast'
                },
                cursor: {
                    show: true,
                    zoom: true,
                    showTooltip: false
                },
                seriesDefaults: {
                    showMarker:(true),
                    markerOptions:{shadow:false},
                    shadow:false,
                    showLine:true,
                    useNegativeColors: false,
                    fill: true,
                    lineWidth: 1.5,
                    fillAndStroke: true,
                    fillAlpha: 0.12,
                    rendererOptions:{highlightMouseOver: true, highlightMouseDown: true}
                }
            };
            $scope.fetchVisitData = fetchVisitData;

            // Functions
            function fetchVisitData() {
                if ($scope.selectedPortfolio != '') {
                    $scope.fetchingChartData = true;
                    $('#chart').replaceWith('<div id="chart"></div>');
                    var url = urlInterpolator
                        .interpolate('/analytics/{{portfolioId}}/views/{{startDate}}/{{endDate}}',
                            {
                                portfolioId: $scope.selectedPortfolio,
                                startDate: $scope.period.date.startDate.format('DD-MM-YYYY'),
                                endDate: $scope.period.date.endDate.format('DD-MM-YYYY')
                            }
                        );
                    var self = this;

                    $http.get(url)
                        .success(function(data) {
                            $scope.fetchingChartData = false;
                            $scope.chartData = data;

                            if ($scope.chartData.length > 0) {
                                $.jqplot('chart', [data], $scope.chartOptions);
                            }
                        });
                }
            };
        }
    ]);