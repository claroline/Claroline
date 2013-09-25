'use strict';

/**
 * Help Modal Controller
 */
var HelpModalCtrlProto = [
    '$scope',
    'dialog',
    function($scope, dialog) {
        $scope.close = function() {
            dialog.close();
        };
    }
];