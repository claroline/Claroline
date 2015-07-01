/**
 * Step Service
 */
(function () {
    'use strict';

    angular.module('PageModule').factory('PageService', [
        '$http',
        function PageService($http) {
            /**
             * Page object
             * @constructor
             */
            var Page = function Page(name) {
               this.name = name;
            };

           

            return {
                /**
                 * Test method
                 *
                 * @param   {object} [parentStep]
                 * @returns {Step}
                 */
                hello: function () {
                    return this.name;
                },
            };
        }
    ]);
})();