'use strict';

(function(){
    angular.module("websiteApp").factory('globalHttpInterceptor', ['$q', '$injector', 'GlobalRequestHandler', function ($q, $injector, requestHandler) {
        var $http;
        return {
            'request': function(config) {
                //Start request loading
                requestHandler.requestStarted();

                return config;
            },
            'requestError': function(rejection) {
                //End request loading
                $http = $http || $injector.get('$http');
                if($http.pendingRequests.length < 1) {
                    requestHandler.requestEnded();
                }
                //Show globar error message
                requestHandler.requestError(rejection);

                return $q.reject(rejection);
            },
            'response': function (response) {
                //End request loading
                $http = $http || $injector.get('$http');
                if($http.pendingRequests.length < 1) {
                    requestHandler.requestEnded();
                }
                //Show global success message
                requestHandler.requestSuccess(response);

                return response || $q.when(response);
            },
            'responseError': function (rejection) {
                //End request loading
                $http = $http || $injector.get('$http');
                if($http.pendingRequests.length < 1) {
                    requestHandler.requestEnded();
                }
                //Show global error message
                requestHandler.requestError(rejection);

                return $q.reject(rejection);
            }
        };
    }]);

    angular.module("websiteApp").config(['$httpProvider', function ($httpProvider) {
        $httpProvider.interceptors.push('globalHttpInterceptor');
    }]);
})();