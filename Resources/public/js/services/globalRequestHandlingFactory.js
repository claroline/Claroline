'use strict';
(function () {
    angular.module('websiteApp').factory('GlobalRequestHandler', ['$rootScope', function($rootScope){

        //Handle starting and ending of requests showing and hiding loading div
        //Show loading div
        var requestStarted = function() {
            $rootScope.pageLoaded = false;
        };
        //Hide loading div
        var requestEnded = function() {
            $rootScope.pageLoaded = true;
        };

        //Handle general success and error messages
        var REQUEST_SUCCESS = 'REQUEST_SUCCESS';
        var REQUEST_ERROR = 'REQUEST_ERROR';
        //Broadcast success message
        var requestSuccess = function(response) {
            $rootScope.$broadcast(REQUEST_SUCCESS, response);
        };
        //Broadcast error rejection
        var requestError = function(rejection) {
            $rootScope.$broadcast(REQUEST_ERROR, rejection);
        };
        //Handle things globally on success
        var onRequestSuccess = function($scope, handler) {
            $scope.$on(REQUEST_SUCCESS, function(event, response){
                handler(response);
            });
        };
        var onRequestError = function($scope, handler) {
            $scope.$on(REQUEST_ERROR, function(event, rejection){
               handler(rejection);
            });
        };

        return {
            requestStarted: requestStarted,
            requestEnded: requestEnded,
            requestSuccess: requestSuccess,
            requestError: requestError,
            onRequestSuccess: onRequestSuccess,
            onRequestError: onRequestError
        }

    }]);
})();