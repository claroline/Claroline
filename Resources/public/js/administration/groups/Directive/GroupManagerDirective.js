var directive = function () {
    return {
        restrict: 'E',
        templateUrl: AngularApp.webDir + 'bundles/clarolinecore/js/administration/users/Partial/group_manager.html',
        replace: true
    }
}

angular.module('groupsManager').directive('groupManager', [directive]);