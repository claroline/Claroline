/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

(function () {
    'use strict';

    angular.module('CursusRegistrationModule').directive('cursusList', [
        function () {
            return {
                restrict: 'E',
                replace: true,
                templateUrl: AngularApp.webDir + 'bundles/clarolinecursus/js/Registration/Cursus/Partial/cursus_list.html'
            };
        }
    ]);
})();