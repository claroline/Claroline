'use strict';

/**
 * Main Controller
 */
var mainCtrl = [
    '$scope',
    '$routeParams',
    '$location',
    '$http',
    '$modal',
    function($scope, $routeParams, $location, $http, $modal, HistoryFactory, PathFactory, StepFactory) {        
        // Store symfony base partials route
        $scope.templateRoute = PlayerApp.templateRoute;
        
        /**
         * Open Help modal
         * @returns void
         */
        $scope.openHelp = function() {
            var modalInstance = $modal.open({
                templateUrl: playerApp.templateRoute + 'Help/help-modal.html',
                controller: 'HelpModalCtrl'
            });

        };
        
    }
];