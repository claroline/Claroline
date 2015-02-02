'use strict';

portfolioApp
    .controller("collectionFormController", ["$scope", "$attrs", function($scope, $attrs) {
        $scope.emptyChild = jQuery.parseJSON($scope.collectionForm.replace(/'/g, '"'));
        $scope.emptyChild.added = false;

        $scope.collection = $scope.widget.children || [];

        $scope.resourcePickerConfig = {
            isPickerMultiSelectAllowed: true,
            callback: function (nodes) {
                angular.forEach(nodes, function (element, index) {
                    var newChild = angular.copy($scope.emptyChild);
                    newChild.resource = index;
                    newChild.id = index;
                    newChild.name = element[0];
                    delete newChild.added;
                    $scope.collection.push(newChild);
                });
                $scope.$apply();
            }
        };

        var selectedValue = [];
        angular.forEach($scope.collection, function (element, index) {
            selectedValue.push(element.badge);
        });

        $scope.badgePickerConfig = {
            data: {
                multiple: true,
                value: selectedValue
            },
            successCallback: function (nodes) {
                var receivedValue = [];
                angular.forEach(nodes, function (element, index) {
                    receivedValue.push(element.id);
                });
                var badgeToRemove = selectedValue.diff(receivedValue);
                var badgeToAdd    = receivedValue.diff(selectedValue);

                angular.forEach($scope.collection, function (element, index) {
                    var id = parseInt(element.badge);
                    if (badgeToRemove.inArray(id)) {
                        $scope.deleteChild(element);
                    }
                });
                angular.forEach(nodes, function (element, index) {
                    var id = parseInt(element.id);
                    if (badgeToAdd.inArray(id)) {
                        var badgeAboutToBeDelete = $scope.collection.filter(function(element) {return id === element.badge;});
                        if (0 < badgeAboutToBeDelete.length) {
                            delete badgeAboutToBeDelete[0].toDelete;
                        }
                        else {
                            var newChild   = angular.copy($scope.emptyChild);
                            newChild.badge = id;
                            newChild.name  = element.text;
                            newChild.img   = element.icon;
                            delete newChild.added;
                            $scope.addChild(newChild);
                        }
                    }
                });
                $scope.$apply();
                selectedValue = [];
                angular.forEach($scope.collection, function (element, index) {
                    if (!element.toDelete) {
                        selectedValue.push(element.badge);
                    }
                });
            }
        };

        $scope.addChild = function(child) {
            $scope.collection.push(child);
        };

        $scope.deleteChild = function(child) {
            child.toDelete = true;
        };

        $scope.cancelDeletionOfChild = function(child) {
            delete child.toDelete;
        };

        $scope.isEmpty = function(child) {
            var isEmpty = false;

            for (var attribute in child) {
                if ("$" !== attribute[0] && '' === child[attribute]) {
                    isEmpty = true;
                }
            }

            return isEmpty;
        }

        $scope.modify = function(child, index) {
            if ($scope.isEmpty(child)) {
                child.added = false;
            }
            else {
                if (false == child.added && (index + 1) === $scope.collection.length) {
                    $scope.collection.push(angular.copy($scope.emptyChild));
                    delete child.added;
                }
            }
        };

        $scope.isAdded = function(child) {
            return (!$scope.isEmpty(child) && !child.toDelete) || child.toDelete;
        };
        if (!$scope.isEmpty($scope.collection[$scope.collection.length - 1]) && !$attrs.editable) {
            $scope.collection.push(angular.copy($scope.emptyChild));
        }
    }]);