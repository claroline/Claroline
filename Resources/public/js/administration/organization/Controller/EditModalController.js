var modalController = function(organizationAPI, $scope, organizations, organization, $uibModalStack, $uibModal, clarolineAPI) {
    $scope.organization = {};

    $scope.submit = function() {
        console.log($scope.organization);
        organizationAPI.update(organization.id, $scope.organization).then(
            function successHandler (d) {
                console.log(organizations);
                $uibModalStack.dismissAll();
                clarolineAPI.replaceById(d.data, organizations);
            },
            function errorHandler (d) {
                if (d.status === 400) {
                    $uibModalStack.dismissAll();
                    $uibModal.open({
                        template: d.data,
                        controller: 'EditModalController',
                        resolve: {
                            organizations: function() {
                                return organizations;
                            },
                            organization: function() {
                                return organization;
                            }
                        }
                    });
                }
            }
        );
    }
}

angular.module('OrganizationManager').controller('EditModalController', modalController);