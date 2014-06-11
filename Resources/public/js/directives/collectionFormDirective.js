'use strict';

portfolioApp
    .directive("collectionForm", function() {
        var link = function (scope, element, attrs, ngModel) {
            console.log('pouet');
            if(!ngModel) {
                return;// do nothing if no ng-model
            }

            ngModel.$render = function() {
                scope.collection = ngModel.$viewValue || [];
            };

            scope.addChild = function(child) {
                scope.collection.push({'name': child});
            };
        };

        return {
            scope: true,
            require: '?ngModel', // get a hold of NgModelController
            restrict: 'A',
            link: link
        };
    });