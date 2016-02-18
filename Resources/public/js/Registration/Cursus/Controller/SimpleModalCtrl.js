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

    angular.module('CursusRegistrationModule').controller('SimpleModalCtrl', [
        '$uibModalStack',
        '$sce',
        'title',
        'content',
        function ($uibModalStack, $sce, title, content) {
            this.title = title;
            this.content = $sce.trustAsHtml(content);
            
            this.closeModal = function () {
                $uibModalStack.dismissAll();
            };
        }
    ]);
})();