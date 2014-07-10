/*globals angular, moment, jQuery */
/*jslint vars:true */

/**
 * @license angular-date-time-input  v0.1.0
 * (c) 2013 Knight Rider Consulting, Inc. http://www.knightrider.com
 * License: MIT
 */
/**
 *
 *    @author Dale "Ducky" Lotts
 *    @since  2013-Sep-23
 */

angular.module('ui.dateTimeInput', []).directive('dateTimeInput',
    [
        function() {
            "use strict";
            return {
                require: 'ngModel',
                restrict: 'A',
                link: function(scope, element, attrs, controller) {
                    if (!attrs.dateTimeInput) {
                        throw ("dateTimeInput must specify a date format");
                    }
                    var validateFn = function(viewValue) {
                        var result = viewValue;
                        if (viewValue) {
                            var momentValue = moment(viewValue, attrs.dateTimeInput);
                            if (momentValue.isValid()) {
                                controller.$setValidity(attrs.ngModel, true);
                                result = momentValue.format();
                            }
                            else {
                                controller.$setValidity(attrs.ngModel, false);
                            }
                        }
                        return result;
                    };
                    var formatFn = function(modelValue) {
                        modelValue = modelValue.substr(0, 10);
                        var result = modelValue;
                        if (modelValue && moment(modelValue).isValid()) {
                            result = moment(modelValue, 'YYYY/MM/DD').format(attrs.dateTimeInput);
                        }
                        return result;
                    };
                    controller.$parsers.unshift(validateFn);
                    controller.$formatters.push(formatFn);
                    element.bind('blur', function() {
                        var viewValue = controller.$modelValue;
                        angular.forEach(controller.$formatters, function(formatter) {
                            viewValue = formatter(viewValue);
                        });
                        controller.$viewValue = viewValue;
                        controller.$render();
                    });
                }
            };
        }
    ]);
