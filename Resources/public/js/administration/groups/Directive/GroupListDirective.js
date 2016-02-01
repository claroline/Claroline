var directive = function () {
    return {
        restrict: 'E',
        templateUrl: AngularApp.webDir + 'bundles/clarolinecore/js/administration/groups/Partial/group_list.html',
        replace: true
    }
}

angular.module('GroupsManager').directive('groupList', [directive]);