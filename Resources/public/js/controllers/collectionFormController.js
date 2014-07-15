'use strict';

portfolioApp
    .controller("collectionFormController", ["$scope", "$attrs", function($scope, $attrs) {
        $scope.emptyChild = jQuery.parseJSON($attrs.collectionForm.replace(/'/g, '"'));
        $scope.emptyChild.added = false;

        var collection = $scope.editedWidget.children || [];
        $scope.collection = collection;

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