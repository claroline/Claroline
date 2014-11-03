/**
 * Global Controller
 */
(function () {
    'use strict';

    angular.module('EditorModule').controller('GlobalCtrl', [
        '$scope',
        function ($scope) {
            /**
             * Update Root step when path name changes
             */
            $scope.renameRootStep = function() {
                if (undefined != $scope.path.steps[0]) {
                    $scope.path.steps[0].name = $scope.path.name;
                }
            };
        }
    ]);
})();