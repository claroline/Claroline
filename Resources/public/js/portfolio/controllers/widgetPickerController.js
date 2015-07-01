portfolioApp
    .controller('widgetPickerController', ["$scope", "$modalInstance", "portfolioWidgets", "selectedPortfolioWidget", "$filter",
    function ($scope, $modalInstance, portfolioWidgets, selectedPortfolioWidget, $filter) {
        $scope.portfolioWidgets = portfolioWidgets;
        $scope.portfolioWidgets.map(function(portfolioWidget) {
            portfolioWidget.isCollapsed = true;
        });
        $scope.selectedPortfolioWidget = (selectedPortfolioWidget && $filter('filter')($scope.portfolioWidgets, {widget_id: selectedPortfolioWidget.widget_id})[0]) || $scope.portfolioWidgets[0];

        $scope.ok = function () {
            $modalInstance.close($scope.selectedPortfolioWidget);
        };

        $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
        };
    }]);