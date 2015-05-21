/**
 * RestResource
 * Override default AngularJS $resource to add update method with PUT HTTP method
 */
(function () {
    'use strict';

    angular.module('UtilsModule').factory('RestResource', [
        '$resource',
        function RestResource($resource) {
            return function (url, params, methods) {
                var defaults = {
                    update: { method: 'put', isArray: false },
                    create: { method: 'post' }
                };

                methods = angular.extend( defaults, methods );

                // Create symfony route
                var route = Routing.generate(url);

                // Override save method to make it choose between create and update based on an ID field
                var resource = $resource(route, params, methods);
                resource.prototype.$save = function () {
                    if (!this.id) {
                        return this.$create();
                    }
                    else {
                        return this.$update();
                    }
                };

                return resource;
            };
        }
    ]);
})();