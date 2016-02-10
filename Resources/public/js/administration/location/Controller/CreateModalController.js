var controller = function(locationAPI, $scope, locations, $uibModalStack, $uibModal) {
    $scope.location = {};

    $scope.submit = function() {
        locationAPI.create($scope.location).then(
            function successHandler (d) {
                $uibModalStack.dismissAll();
                locations.push(d.data);
            },
            function errorHandler (d) {
                if (d.status === 400) {
                    $uibModalStack.dismissAll();
                    $uibModal.open({
                        template: d.data,
                        controller: 'CreateModalController',
                        bindToController: true,
                        resolve: {
                            locations: function() {
                                return locations;
                            }
                        }
                    })
                }
            }
        );
    }
};

angular.module('LocationManager').controller('CreateModalController', controller);