import 'angular/angular.min'

angular.module('ui.fos-js-router', [])
    .filter('path', function () {
        return function (route, parameters = {}) {
            return Routing.generate(route, parameters)
        };
    })
