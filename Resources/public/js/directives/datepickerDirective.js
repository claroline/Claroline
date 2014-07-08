angular.module('b.datepicker', []).
    directive('bDatepicker', function() {
        return {
            require: '?ngModel',
            restrict: 'A',
            link: function($scope, element, attrs, controller) {
                var updateModel;
                updateModel = function(event) {
                    element.datepicker('hide');
                    element.blur();
                };
                if (controller != null) {
                    controller.$render = function() {
                        element.datepicker().data().datepicker.date = controller.$viewValue;
                        element.datepicker('setValue');
                        element.datepicker('update');

                        return controller.$viewValue;
                    };
                }
                return attrs.$observe('bDatepicker', function(format) {
                    var options = {'format': format};
                    return element.datepicker(options).on('changeDate', updateModel);
                });
            }
        };
    });