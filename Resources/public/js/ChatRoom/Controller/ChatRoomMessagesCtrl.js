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

    angular.module('ChatRoomModule').controller('ChatRoomMessagesCtrl', [
        '$scope',
        function ($scope) {
            $scope.messages = [];

            $scope.addMessage = function (sender, message, color) {
                $scope.messages.push({sender: sender, message: message, color: color, type: 'message'});
                $scope.$apply();
                updateScrollBarPosition();
            };

            $scope.addPresenceMessage = function (name, status) {
                $scope.messages.push({name: name, status: status, type: 'presence'});
                $scope.$apply();
                updateScrollBarPosition();
            };

            $scope.$on('newMessageEvent', function (event, messageDatas) {
                $scope.addMessage(messageDatas['sender'], messageDatas['message'], messageDatas['color']);
            });

            $scope.$on('newPresenceEvent', function (event, presenceDatas) {
                $scope.addPresenceMessage(presenceDatas['name'], presenceDatas['status']);
            });
            
            function updateScrollBarPosition()
            {
                var scrollHeight = $('#chat-messages-panel')[0].scrollHeight;
                $('#chat-messages-panel').scrollTop(scrollHeight);
            }
        }
    ]);
})();