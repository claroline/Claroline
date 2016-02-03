var controller = function($http, clarolineSearch, $stateParams) {
    console.log('init userListCtrl');
    console.log('Grp params');
    this.groupId = $stateParams.groupId;

};

angular.module('GroupsManager').controller('UserListController', [
    '$http',
    'clarolineSearch',
    '$stateParams',
    controller
]);