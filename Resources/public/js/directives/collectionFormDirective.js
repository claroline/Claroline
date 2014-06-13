'use strict';

portfolioApp
    .directive("collectionForm", function() {
        var link = function (scope, element, attrs, ngModel) {
            if(!ngModel) {
                return;// do nothing if no ng-model
            }

            scope.emptyChild = jQuery.parseJSON(attrs.collectionForm.replace(/'/g, '"'));

            ngModel.$render = function() {
                var collection = ngModel.$viewValue || [];
                if (!scope.isEmpty(collection[collection.length - 1])) {
                    collection.push(angular.copy(scope.emptyChild));
                }
                scope.collection = collection;
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