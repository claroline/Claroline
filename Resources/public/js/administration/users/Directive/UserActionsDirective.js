var directive = function () {
    return {
        restrict: 'E',
        templateUrl: AngularApp.webDir + 'bundles/clarolinecore/js/administration/users/Partial/user_actions.html',
        replace: true
    }
}

angular.module('UsersManager').directive('userActions', [directive]);