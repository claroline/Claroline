'use strict';

/**
 * Global Controller
 */
var GlobalCtrlProto = [
    '$scope',
    'HistoryFactory',
    function($scope, HistoryFactory) {
        /**
         * Update Root step when path name changes
         * 
         * @returns void
         */
        $scope.renameRootStep = function() {
            if (undefined != $scope.path.steps[0]) {
                $scope.path.steps[0].name = $scope.path.name;
            }
        };
        
        /**
         * Update History when general data change
         */
        $scope.updateHistory = function() {
            HistoryFactory.update($scope.path);
        };
    }
];