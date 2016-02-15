angular.module('ui.fos-js-router', [])
    .filter('path', function () {
        return function (route, parameters = {}) {
            console.log(route, parameters)
            return Routing.generate(route, parameters)
        };
    })