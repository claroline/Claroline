angular.module('ngJsonInput', [])
  .directive('ngJsonInput', function() {
        return {
            restrict: 'A',
            require: 'ngModel',
            link: function(scope, element, attr, ngModel) {
                function fromJson(json) {
                    return angular.fromJson(json);
                }

                function toJson(object) {
                    return angular.toJson(object);
                }
                
                ngModel.$parsers.push(fromJson);
                ngModel.$formatters.push(toJson);
            }
        };
});