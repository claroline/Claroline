'use strict';

statisticsApp
    .controller("statisticsViewController", ["$scope", "portfolioManager", "$filter", "$http", "urlInterpolator", "translationService",
        function($scope, portfolioManager, $filter, $http, urlInterpolator, translationService) {
        $scope.selectedPortfolio = null;
        $scope.period = {
            date: {startDate: moment().startOf('month'), endDate: moment().endOf('month')}
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

        $scope.fetchVisitData = function() {
            if ($scope.selectedPortfolio !== null) {
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
                        if (data.length > 0) {
                            $scope.chartData = data;
                            $.jqplot('chart', [data], $scope.chartOptions);
                        }
                        else {
                            $scope.chartData = [];
                        }
                        $scope.fetchingChartData = false;
                    });
            }
        };
    }]);