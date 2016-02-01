var directive = function () {
    return {
        restrict: 'E',
        templateUrl: AngularApp.webDir + 'bundles/clarolinecore/js/administration/groups/Partial/group_search.html',
        replace: true
    }
}

angular.module('GroupsManager').directive('groupSearch', [directive]);