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

    angular.module('ChatRoomModule').controller('ChatRoomInitCtrl', [
        '$scope', 
        '$rootScope',
        '$http',
        'XmppService', 
        'XmppMucService',
        function ($scope, $rootScope, $http, XmppService, XmppMucService) {
            $scope.init = false;
            
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
            
            $rootScope.$on('xmppMucConnectedEvent', function () {
                var route = Routing.generate(
                    'claro_chat_room_status_edit', 
                    {chatRoom: XmppMucService.getRoomId(), roomStatus: 1}
                );
                $http.post(route).then(function (datas) {
                    
                    if (datas['status'] === 200) {
                        XmppMucService.openRoom();
                        $scope.init = true;
                        window.location.reload();
                    }
                });
            });
            
            $scope.isInit = function () {
                
                return $scope.init;
            };
        }
    ]);
})();