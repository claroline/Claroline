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

    angular.module('ChatRoomModule').controller('ChatRoomXmppCtrl', ['$scope', 'XmppMucService',
        function ($scope, XmppMucService) {
            $scope.connect = function (
                server,
                mucServer, 
                boshPort, 
                roomId, 
                roomName, 
                username, 
                password, 
                firstName, 
                lastName, 
                color
            ) {
                XmppMucService.connect(
                    server,
                    mucServer, 
                    boshPort, 
                    roomId, 
                    roomName, 
                    username, 
                    password, 
                    firstName, 
                    lastName, 
                    color
                );
            };
            $scope.disconnect = function () {
                XmppMucService.disconnect();
                console.log('disconnected');
            };
        }
    ]);
})();