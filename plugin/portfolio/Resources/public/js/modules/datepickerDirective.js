angular.module('ui.datepicker', []).
    directive('bDatepicker', function() {
        return {
            require:  '?ngModel',
            restrict: 'A',
            link: function($scope, element, attrs, ngModel) {
                return attrs.$observe('bDatepicker', function(format) {
                    var options = {format: format, autoclose: true};
                    var onShow = null;
                    if (attrs.singleDate) {
                        onShow = function(){
                            if(element.datepicker('getDate') == null) {
                                element.datepicker('setDate', element.find('input').val());
                            }
                        }
                    }

                    return element.datepicker(options).on('show', onShow);
                });

            }
        };
    });