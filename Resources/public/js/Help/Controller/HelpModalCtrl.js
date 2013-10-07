'use strict';

/**
 * Help Modal Controller
 */
function HelpModalCtrl($scope, $modalInstance) {
    $scope.close = function() {
        $modalInstance.dismiss('cancel');
    };
}