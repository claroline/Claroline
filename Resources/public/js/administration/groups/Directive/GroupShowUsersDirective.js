var directive = function () {
    return {
        restrict: 'E',
        templateUrl: AngularApp.webDir + 'bundles/clarolinecore/js/administration/groups/Partial/group_show_user.html',
        replace: true
    }
}

angular.module('GroupsManager').directive('groupShowUsers', [directive]);