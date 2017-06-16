let REQUEST_SUCCESS = 'REQUEST_SUCCESS'
let REQUEST_ERROR = 'REQUEST_ERROR'
let _$rootScope = new WeakMap()
let _$alert = new WeakMap()
export default class RequestHandler {
  constructor($rootScope, $alert) {
    _$rootScope.set(this, $rootScope)
    _$alert.set(this, $alert)
  }
  // Handle starting and ending of requests showing and hiding loading div
  // Show loading div
  requestStarted() {
    _$rootScope.get(this).pageLoaded = false
  }
  // Hide loading div
  requestEnded() {
    _$rootScope.get(this).pageLoaded = true
  }
  // Broadcast success message
  requestSuccess(response) {
    _$rootScope.get(this).$broadcast(REQUEST_SUCCESS, response)
  }
  // Broadcast error rejection
  requestError(rejection) {
    _$rootScope.get(this).$broadcast(REQUEST_ERROR, rejection)
  }
  // Handle things globally on success
  onRequestSuccess($scope, handler) {
    $scope.$on(REQUEST_SUCCESS, (event, response) => {
      response.alert = _$alert.get(this)
      handler(response)
    })
  }
  onRequestError($scope, handler) {
    $scope.$on(REQUEST_ERROR, (event, rejection) => {
      rejection.alert = _$alert.get(this)
      handler(rejection)
    })
  }
}

RequestHandler.$inject = [ '$rootScope', '$alert' ]
