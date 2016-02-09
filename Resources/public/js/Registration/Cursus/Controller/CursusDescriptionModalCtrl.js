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

    angular.module('CursusRegistrationModule').controller('CursusDescriptionModalCtrl', [
        '$uibModalStack',
        '$sce',
        'title',
        'description',
        function ($uibModalStack, $sce, title, description) {
            this.title = title;
            this.description = $sce.trustAsHtml(description);
            
            this.closeModal = function () {
                $uibModalStack.dismissAll();
            };
        }
    ]);
})();