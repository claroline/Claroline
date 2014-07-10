angular.module('ui.datepicker', []).
    directive('bDatepicker', function() {
        return {
            require: '?ngModel',
            restrict: 'A',
            link: function($scope, element, attrs, ngModel) {
                var updateModel;

                updateModel = function(event) {
                    element.datepicker('hide');
                    element.blur();
                };

                if (ngModel != null) {

                    ngModel.$render = function() {
                        element.datepicker().data().datepicker.date = ngModel.$viewValue;
                        element.datepicker('setValue');
                        element.datepicker('update');

                        return ngModel.$viewValue;
                    };
                }
                return attrs.$observe('bDatepicker', function(format) {
                    var options = {'format': format};

                    return element.datepicker(options).on('changeDate', updateModel);
                });
            }
        };
    });