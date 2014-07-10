angular.module('ui.datepicker', []).
    directive('bDatepicker', function() {
        return {
            require:  '?ngModel',
            restrict: 'A',
            link: function($scope, element, attrs, ngModel) {
                return attrs.$observe('bDatepicker', function(format) {
                    var options = {format:    format};

                    if (ngModel) {
                        options.autoclose = true;
                    }

                    return element.datepicker(options);
                });
            }
        };
    });