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

    angular.module('ChatRoomModule').directive('chatRoomAudio', [
        function () {
            return {
                restrict: 'E',
                replace: true,
                templateUrl: AngularApp.webDir + 'bundles/clarolinechat/js/ChatRoom/Directive/templates/chatRoomAudio.html',
                link: function (scope, element, attrs) {
                    scope.server = attrs['chatRoomXmppHost'];
                    scope.mucServer = attrs['chatRoomXmppMucHost'];
                    scope.boshPort = attrs['chatRoomBoshPort'];
                    scope.roomId = attrs['chatRoomId'];
                    scope.roomName = attrs['chatRoomName'];
                    scope.username = attrs['chatRoomUserUsername'];
                    scope.password = attrs['chatRoomUserPassword'];
                    scope.firstName = attrs['chatRoomUserFirstName'];
                    scope.lastName = attrs['chatRoomUserLastName'];
                    scope.color = attrs['chatRoomUserColor'];
                }
            };
        }
    ]);
})();