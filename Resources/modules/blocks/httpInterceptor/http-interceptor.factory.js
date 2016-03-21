(function () {
  'use strict';

  angular
    .module('blocks.httpInterceptor')
    .factory('httpInterceptor', httpInterceptor);

  httpInterceptor.$inject = [ '$q', '$injector', 'requestHandler' ];

  function httpInterceptor($q, $injector, requestHandler) {
    var $http;
    var service = {
      'request': request,
      'requestError': requestError,
      'response': response,
      'responseError': responseError
    };

    return service;

    function request(config) {
      //Start request loading
      requestHandler.requestStarted();

      return config;
    };

    function requestError(rejection) {
      //End request loading
      $http = $http || $injector.get('$http');
      if ($http.pendingRequests.length < 1) {
        requestHandler.requestEnded();
      }
      //Show globar error message
      requestHandler.requestError(rejection);

      return $q.reject(rejection);
    };

    function response(response) {
      //End request loading
      $http = $http || $injector.get('$http');
      if ($http.pendingRequests.length < 1) {
        requestHandler.requestEnded();
      }
      //Show global success message
      requestHandler.requestSuccess(response);

      return response || $q.when(response);
    };

    function responseError(rejection) {
      //End request loading
      $http = $http || $injector.get('$http');
      if ($http.pendingRequests.length < 1) {
        requestHandler.requestEnded();
      }
      //Show global error message
      requestHandler.requestError(rejection);

      return $q.reject(rejection);
    };
  };
})();