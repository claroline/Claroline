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

    angular.module('ChatRoomModule').controller('ChatRoomXmppCtrl', [
        '$scope', 
        '$rootScope', 
        'XmppService', 
        'XmppMucService',
        function ($scope, $rootScope, XmppService, XmppMucService) {
            $scope.connected = false;
            $scope.message = Translator.trans('connecting', {}, 'chat');
            $scope.messageType = 'info';
            
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
                console.log('disconnecting...');
                
                if ($scope.connected) {
                    XmppMucService.disconnect();
                    console.log('disconnected');
                }
            };
            
            $scope.isConnected = function () {
                
                return $scope.connected;
            };
            
            $scope.setMessage = function (message) {
                $scope.message = message;
            };
            
            $scope.closeRoom = function () {
                XmppMucService.closeRoom();
            };
            
            $scope.openRoom = function () {
                XmppMucService.openRoom();
            };
            
            $rootScope.$on('xmppMucConnectedEvent', function () {
                $scope.connected = true;
                $scope.$apply();
            });
            
            $rootScope.$on('xmppMucForbiddenConnectionEvent', function () {
                $scope.message = Translator.trans('not_authorized_msg', {}, 'chat');
                $scope.messageType = 'danger';
                $scope.$apply();
            });
            
            $rootScope.$on('xmppMucBannedEvent', function () {
                $scope.connected = false;
                $scope.message = Translator.trans('banned_msg', {}, 'chat');
                $scope.messageType = 'danger';
                $scope.$apply();
            });
            
            $rootScope.$on('xmppMucKickedEvent', function () {
                $scope.connected = false;
                $scope.message = Translator.trans('kicked_msg', {}, 'chat');
                $scope.messageType = 'warning';
                $scope.$apply();
            });
        }
    ]);
})();