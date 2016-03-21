(function () {
  'use strict';

  angular
    .module('blocks.httpInterceptor')


  requestHandler.$inject = [ '$rootScope' ];

  function requestHandler($rootScope) {

    //Handle general success and error messages
    var REQUEST_SUCCESS = 'REQUEST_SUCCESS';
    var REQUEST_ERROR = 'REQUEST_ERROR';

    var service = {
      requestStarted: requestStarted,
      requestEnded: requestEnded,
      requestSuccess: requestSuccess,
      requestError: requestError,
      onRequestSuccess: onRequestSuccess,
      onRequestError: onRequestError
    }

    return service;

    //Handle starting and ending of requests showing and hiding loading div
    //Show loading div
    function requestStarted() {
      $rootScope.pageLoaded = false;
    };
    //Hide loading div
    function requestEnded() {
      $rootScope.pageLoaded = true;
    };
    //Broadcast success message
    function requestSuccess(response) {
      $rootScope.$broadcast(REQUEST_SUCCESS, response);
    };
    //Broadcast error rejection
    function requestError(rejection) {
      $rootScope.$broadcast(REQUEST_ERROR, rejection);
    };
    //Handle things globally on success
    function onRequestSuccess($scope, handler) {
      $scope.$on(REQUEST_SUCCESS, function (event, response) {
        handler(response);
      });
    };
    function onRequestError($scope, handler) {
      $scope.$on(REQUEST_ERROR, function (event, rejection) {
        handler(rejection);
      });
    };
  };
})();
