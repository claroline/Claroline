var controller = function(GroupAPI, $scope, groups, $uibModalInstance, $uibModal) {
    $scope.group = {};
    var vm = this;

    $scope.submit = function() {
        GroupAPI.create($scope.group).then(
            function successHandler (d) {
                $uibModalInstance.close(d.data);
            },
            function errorHandler (d) {
                if (d.status === 400) { 
                    $uibModalInstance.close();
                    $uibModal.open({
                        template: d.data,
                        controller: 'CreateModalController',
                        bindToController: true,
                        resolve: {
                            groups: function() {
                                return groups;
                            }
                        }
                    })
                }
            }
        );
    }
};

angular.module('GroupsManager').controller('CreateModalController', controller);