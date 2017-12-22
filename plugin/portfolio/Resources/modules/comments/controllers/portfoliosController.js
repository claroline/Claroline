
export default function ($scope, portfolioManager, commentsManager, $filter) {
  $scope.selectedPortfolioId = 0
  $scope.selectedPortfolio = null

  $scope.clickOnPortolio = function (portfolioId) {
    if (portfolioId !== $scope.selectedPortfolioId) {
      $scope.selectedPortfolioId = portfolioId
      $scope.selectedPortfolio = $filter('filter')($scope.portfolios, {id: $scope.selectedPortfolioId})[0]
      $scope.updateCountViewComments()
    } else {
      $scope.selectedPortfolioId = 0
      $scope.selectedPortfolio = null
    }

    commentsManager.loadComments($scope.selectedPortfolioId)
  }

  $scope.refreshComments = function (clickOnPortfolio) {
    $scope.portfolios = portfolioManager.getPortfolios()
    $scope.portfolios.then(
                function (data) {
                  $scope.portfolios = data
                  if (clickOnPortfolio) {
                    $scope.clickOnPortolio(window.currentPortfolioId)
                  }
                },
                function () {
                }
            )
  }

  $scope.updateCountViewComments = function () {
    if (0 < $scope.selectedPortfolio.unreadComments) {
      portfolioManager.updateViewCommentsDate($scope.selectedPortfolio)
    }
  }

  $scope.refreshComments(true)
}
