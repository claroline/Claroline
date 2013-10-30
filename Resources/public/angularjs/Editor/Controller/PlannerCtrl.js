'use strict';

/**
 * Planner Controller
 */
function PlannerCtrl($scope, PathFactory) {
    $scope.initPath(PathFactory.getPath());
}