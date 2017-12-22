export default function ($scope, portfolioManager, commentsManager, tinyMceConfig) {
  $scope.message = ''

  $scope.tinyMceConfig = tinyMceConfig

  $scope.create = function () {
    if (this.message) {
      commentsManager.create(portfolioManager.portfolioId, {
        'message' : this.message
      })
      this.message = ''
    }
  }

  $scope.updateCountViewComments = function () {
    $scope.displayComment= !$scope.displayComment

    if ($scope.displayComment) {
      if (0 < portfolioManager.portfolio.unreadComments) {
        portfolioManager.updateViewCommentsDate()
      }
    }
  }
}
