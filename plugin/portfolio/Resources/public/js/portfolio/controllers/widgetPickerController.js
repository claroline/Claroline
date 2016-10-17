portfolioApp
  .controller('widgetPickerController', ['$scope', '$uibModalInstance', 'portfolioWidgets', 'selectedPortfolioWidget',
    function ($scope, $modalInstance, portfolioWidgets, selectedPortfolioWidget) {
      $scope.portfolioWidgets = portfolioWidgets
      $scope.portfolioWidgets.map(function (portfolioWidget) {
        portfolioWidget.isCollapsed = true
      })
      $scope.isEdit = selectedPortfolioWidget !== null
      $scope.selectedPortfolioWidgets = [(selectedPortfolioWidget && window._.find($scope.portfolioWidgets, {widget_id: selectedPortfolioWidget.widget_id})) || $scope.portfolioWidgets[0]]
      $scope.selectedPortfolioWidget = selectedPortfolioWidget && window._.find($scope.portfolioWidgets, {widget_id: selectedPortfolioWidget.widget_id})
      $scope.ok = function () {
        $modalInstance.close($scope.selectedPortfolioWidgets)
      }

      $scope.cancel = function () {
        $modalInstance.dismiss('cancel')
      }

      $scope.isSelected = function (widget) {
        return Array.isArray($scope.selectedPortfolioWidgets)
        && $scope.selectedPortfolioWidgets.indexOf(widget) !== -1
      }

      $scope.radioChanged = function (widget) {
        $scope.selectedPortfolioWidget = widget
        $scope.selectedPortfolioWidgets = [widget]
      }
    }])
