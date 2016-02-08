var controller = function(GroupAPI, $scope, $uibModalInstance, $uibModal) {
    $scope.group = {};
    var vm = this;

    $scope.submit = function() {
        GroupAPI.edit($scope.group).then(
            function successHandler (d) {
                $uibModalInstance.close(d.data);
            },
            function errorHandler (d) {
                if (d.status === 400) { 
                    $uibModalInstance.close();
                    $uibModal.open({
                        template: d.data,
                        controller: 'EditModalController',
                        bindToController: true
                    })
                }
            }
        );
    }
};

angular.module('GroupsManager').controller('EditModalController', controller);