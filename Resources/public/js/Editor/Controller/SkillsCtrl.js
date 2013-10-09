'use strict';

/**
 * Skills Controller
 */
function SkillsCtrl($scope, PathFactory) {
    $scope.initPath(PathFactory.getPath());
}