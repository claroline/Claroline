/**
 * Question module
 */
(function () {
    'use strict';

    angular.module('Question', [
        
    ]);

    jsPlumb.ready(function () {
        
        console.log("question module");
        angular.element(document).ready(function () {
            angular.bootstrap(document, ['Question']);
        });

    });

})();

