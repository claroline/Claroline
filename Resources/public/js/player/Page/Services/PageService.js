/**
 * Step Service
 */
(function () {
    'use strict';

    angular.module('Page').factory('PageService', [
        '$http',
        function PageService($http) {
           
           /**
             * ExercisePlayer Id
             * @type {Number}
             */
            var id = null;
            
            /**
             * current page properties
             * @type object
             */
            var page = {
                title: 'new page'
            };

            return {
                /**
                 * Test method
                 *
                 * @param   {string} [name]
                 * @returns {string}
                 */
                hello: function (name) {
                    return 'Hello ' + name;
                },
                setPlayerId: function (value){
                    id = value;
                },                
                /**
                 * Get exercise player ID
                 * @returns {Number}
                 */
                getPlayerId: function(){
                    return id;
                },
                setPage: function(value){
                    page = value;
                },
                /**
                 * 
                 * @returns {Object} page
                 */
                getPage: function(){
                    return page;
                }
            };
        }
    ]);
})();