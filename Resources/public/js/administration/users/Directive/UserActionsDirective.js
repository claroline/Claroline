var directive = function () {
    return {
        restrict: 'E',
        templateUrl: AngularApp.webDir + 'bundles/clarolinecore/js/administration/users/Partial/user_actions.html',
        replace: true,
        controller: 'UsersCtrl',
        controllerAs: 'uc'
    }
}

angular.module('UsersManager').directive('userActions', [directive]);