portfolioApp
    .controller('widgetPickerController', ["$scope", "$modalInstance", "portfolioWidgets", function ($scope, $modalInstance, portfolioWidgets) {

    $scope.portfolioWidgets = portfolioWidgets;
    $scope.selected = {
        portfolioWidget: $scope.portfolioWidgets[0]
    };

    $scope.ok = function () {
        $modalInstance.close($scope.selected.portfolioWidget);
    };

    $scope.cancel = function () {
        $modalInstance.dismiss('cancel');
    };
}]);