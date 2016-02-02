var controller = function($http, clarolineSearch, $state, $stateParams) {
    console.log('init userListCtrl');
    console.log('Grp state');
    console.log($state);
    console.log('Grp params');
    console.log($stateParams);

};

angular.module('GroupsManager').controller('UserListController', [
    '$http',
    'clarolineSearch',
    controller
]);
