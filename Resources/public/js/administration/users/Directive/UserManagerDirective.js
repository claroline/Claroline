var directive = function () {
    return {
        restrict: 'E',
        templateUrl: AngularApp.webDir + 'bundles/clarolinecore/js/administration/users/Partial/user_manager.html',
        replace: true,
        //maybe remove this at an other point... idk yet. w/e.
        controller: 'UserController',
        controllerAs: 'uc'
    }
}

angular.module('UsersManager').directive('userManager', [directive]);