var directive = function () {
    return {
        restrict: 'E',
        templateUrl: AngularApp.webDir + 'bundles/clarolinecore/js/administration/groups/Partial/group_actions.html',
        replace: true
    }
}

angular.module('GroupsManager').directive('groupActions', [directive]);