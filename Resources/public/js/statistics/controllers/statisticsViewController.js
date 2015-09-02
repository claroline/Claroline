'use strict';

statisticsApp
    .controller("statisticsViewController", ["$scope",
        function($scope, portfolioManager, $filter) {
        $scope.selectedPortfolioId = 0;
        $scope.selectedPortfolio = null;

        $scope.cosPoints = [];
        for (var i=0; i<2*Math.PI; i+=0.4){
            $scope.cosPoints.push([i, Math.cos(i)]);
        }

        $scope.sinPoints = [];
        for (var i=0; i<2*Math.PI; i+=0.4){
           $scope.sinPoints.push([i, 2*Math.sin(i-.8)]);
        }

        $scope.powPoints1 = [];
        for (var i=0; i<2*Math.PI; i+=0.4) {
            $scope.powPoints1.push([i, 2.5 + Math.pow(i/4, 2)]);
        }

        $scope.powPoints2 = [];
        for (var i=0; i<2*Math.PI; i+=0.4) {
            $scope.powPoints2.push([i, -2.5 - Math.pow(i/4, 2)]);
        }

        $scope.chartData = [$scope.cosPoints, $scope.sinPoints, $scope.powPoints1, $scope.powPoints2];

        $scope.chartOptions = {
            series:[
                {
                    // Change our line width and use a diamond shaped marker.
                    lineWidth:2,
                    markerOptions: { style:'diamond' }
                },
                {
                    // Don't show a line, just show markers.
                    // Make the markers 7 pixels with an 'x' style
                    showLine:false,
                    markerOptions: { size: 7, style:"x" }
                },
                {
                    // Use (open) circlular markers.
                    markerOptions: { style:"circle" }
                },
                {
                    // Use a thicker, 5 pixel line and 10 pixel
                    // filled square markers.
                    lineWidth:5,
                    markerOptions: { style:"filledSquare", size:10 }
                }
            ]
        };

        $scope.clickOnPortolio = function() {
            $('#chart').replaceWith('<div id="chart"></div>');
            if ($scope.selectedPortfolio !== null) {
                $.jqplot('chart', $scope.chartData, $scope.chartOptions);
            }
        };
    }]);