angular.module('ui.datepicker', []).
    directive('bDatepicker', function() {
        return {
            require:  '?ngModel',
            restrict: 'A',
            link: function($scope, element, attrs, ngModel) {
                return attrs.$observe('bDatepicker', function(format) {
                    var options = {format: format};
                    var onShow = {};

                    if (ngModel) {
                        options.autoclose = true;

                        onShow = function () {
                            element.datepicker('setDate', ngModel.$viewValue);
                        };
                    }

                    return element.datepicker(options).on('show', onShow);
                });
            }
        };
    });