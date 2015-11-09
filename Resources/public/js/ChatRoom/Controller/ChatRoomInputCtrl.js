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

    angular.module('ChatRoomModule').controller('ChatRoomInputCtrl', ['$scope', 'XmppMucService',
        function ($scope, XmppMucService) {
            $scope.input = '';

            $scope.sendMessage = function () {

                if ($scope.input) {
                    XmppMucService.sendMessageToRoom($scope.input);
                    $scope.input = '';
                }
            };
        }
    ]);     
})();