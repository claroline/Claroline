'use strict';

portfolioApp
    .directive("collectionForm", function() {
        var link = function (scope, element, attrs, ngModel) {
            if(!ngModel) {
                return;// do nothing if no ng-model
            }

            scope.emptyChild = jQuery.parseJSON(attrs.collectionForm.replace(/'/g, '"'));
            scope.emptyChild.notAdded = true;

            ngModel.$render = function() {
                var collection = ngModel.$viewValue || [];
                scope.collection = collection;

                if (!scope.isEmpty(scope.collection[scope.collection.length - 1])) {
                    scope.collection.push(angular.copy(scope.emptyChild));
                }
            };

            scope.addChild = function(child) {
                scope.collection.push(child);
            };

            scope.deleteChild = function(child) {
                child.toDelete = true;
            };

            scope.cancelDeletionOfChild = function(child) {
                delete child.toDelete;
            };

            scope.addEmptyChild = function(index) {
                if (index == scope.collection.length && !scope.isEmpty(scope.collection[index - 2])) {
                    scope.collection.push(angular.copy(scope.emptyChild));
                }
            };

            scope.isEmpty = function(child) {
                var isEmpty = false;

                for (var attribute in child) {
                    if ("$" !== attribute[0] && '' === child[attribute]) {
                        isEmpty = true;
                    }
                }

                return isEmpty;
            }

            scope.markEmpty = function(child) {
                if (scope.isEmpty(child)) {
                    child.notAdded = true;
                }
                else {
                    delete child.notAdded;
                }
            };

            scope.isAdded = function(child) {
                return (!scope.isEmpty(child) && !child.toDelete) || child.toDelete;
            };
        };

        return {
            scope: true,
            require: '?ngModel', // get a hold of NgModelController
            restrict: 'A',
            link: link
        };
    });