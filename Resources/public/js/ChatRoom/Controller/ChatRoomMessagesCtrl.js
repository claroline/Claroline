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

    angular.module('ChatRoomModule').controller('ChatRoomMessagesCtrl', ['$scope',
        function ($scope) {
            $scope.messages = [];

            $scope.addMessage = function (sender, message, color) {
                $scope.messages.push({sender: sender, message: message, color: color});
                $scope.$apply();
            };

            $scope.$on('newMessageEvent', function (event, messageDatas) {
                $scope.addMessage(messageDatas['sender'], messageDatas['message'], messageDatas['color']);
            });
        }
    ]);
})();