RequestHandler.construct.$inject = [ '$rootScope' ]

let REQUEST_SUCCESS = 'REQUEST_SUCCESS'
let REQUEST_ERROR = 'REQUEST_ERROR'

export default class RequestHandler {
  construct ($rootScope) {
    this.$rootScope = $rootScope
  }
  // Handle starting and ending of requests showing and hiding loading div
  // Show loading div
  requestStarted () {
    this.$rootScope.pageLoaded = false
  }
  // Hide loading div
  requestEnded () {
    this.$rootScope.pageLoaded = true
  }
  // Broadcast success message
  requestSuccess (response) {
    this.$rootScope.$broadcast(REQUEST_SUCCESS, response)
  }
  // Broadcast error rejection
  requestError (rejection) {
    this.$rootScope.$broadcast(REQUEST_ERROR, rejection)
  }
  // Handle things globally on success
  onRequestSuccess ($scope, handler) {
    $scope.$on(REQUEST_SUCCESS, function (event, response) {
      handler(response)
    })
  }
  onRequestError ($scope, handler) {
    $scope.$on(REQUEST_ERROR, function (event, rejection) {
      handler(rejection)
    })
  }
}
