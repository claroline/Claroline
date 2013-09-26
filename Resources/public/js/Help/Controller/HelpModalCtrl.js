'use strict';

/**
 * Help Modal Controller
 */
var HelpModalCtrlProto = [
    '$scope',
    '$modalInstance',
    function($scope, $modalInstance) {
        $scope.close = function() {
            $modalInstance.dismiss('cancel');
        };
    }
];