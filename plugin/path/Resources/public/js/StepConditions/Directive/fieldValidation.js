(function () {
    'use strict';

    angular.module('StepConditionsModule')
        .directive('fieldValidation', [
            function () {
                return {
                    require: 'ngModel',
                    link: function(scope, element, attrs, ngModel) {
                        ngModel.$parsers.push(function(value) {
                            /**
                             * Check and set the input as an integer (>=0)
                             */
                            value = parseInt(value, 10);
                            if (!((typeof value === 'number') && (value % 1 === 0))){value = 1}
                            ngModel.$setViewValue(value);
                            ngModel.$render();
                            return value;
                        });
                    }
                };
            }
        ]);
})();