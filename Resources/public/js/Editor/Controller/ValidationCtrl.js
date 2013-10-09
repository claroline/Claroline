'use strict';

/**
 * Planner Controller
 */
function ValidationCtrl($scope, PathFactory) {
    $scope.initPath(PathFactory.getPath());
}