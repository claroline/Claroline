'use strict';

portfolioApp
    .directive("collectionForm", function() {
        var link = function (scope, element, attrs, ngModel) {
            console.log(attrs);
            if(!ngModel) {
                return;// do nothing if no ng-model
            }

            ngModel.$render = function() {
                scope.collection = ngModel.$viewValue || [];
            };

            scope.emptyChild = jQuery.parseJSON(attrs.collectionForm.replace(/'/g, '"'));

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
                if (index == scope.collection.length) {
                    scope.collection.push(angular.copy(scope.emptyChild));
                }
            };
        };

        return {
            scope: true,
            require: '?ngModel', // get a hold of NgModelController
            restrict: 'A',
            link: link
        };
    });