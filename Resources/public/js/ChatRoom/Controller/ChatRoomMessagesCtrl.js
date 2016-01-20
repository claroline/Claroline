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
        '$http',
        'XmppMucService', 
        function ($scope, $http, XmppMucService) {
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

            $scope.addRawMessage = function (message) {
                $scope.messages.push({message: message, type: 'raw'});
                $scope.$apply();
                updateScrollBarPosition();
            };

            $scope.$on('newMessageEvent', function (event, messageDatas) {
                $scope.addMessage(messageDatas['sender'], messageDatas['message'], messageDatas['color']);
            });

            $scope.$on('newPresenceEvent', function (event, presenceDatas) {
                $scope.addPresenceMessage(presenceDatas['name'], presenceDatas['status']);
            });

            $scope.$on('rawRoomMessageEvent', function (event, datas) {
                $scope.addRawMessage(datas['message']);
            });
            
            $scope.$on('myPresenceConfirmationEvent', function () {
                displayArchives();
                updateScrollBarPosition();
            });
            
            function displayArchives()
            {
                var route = Routing.generate('claro_chat_room_archives_retrieve', {chatRoom: XmppMucService.getRoomId()});
                $http.get(route).then(function (datas) {
                    
                    if (datas['status'] === 200) {
                        var messages = datas['data'];
                        
                        if (messages.length > 0) {
                            $('#chat-messages-panel-archives').append('<div class="text-center"><b>***** ' + Translator.trans('archives', {}, 'chat') + ' *****</b></div>');
                            
                            for (var i = 0; i < messages.length; i++) {
                                var type = messages[i]['type'];
                                var userFullName = messages[i]['userFullName'];
                                var content = messages[i]['content'];
                                var e;
                                var msg;
                                
                                if (type === 0) {
                                    e = '<div><b>' + userFullName + '</b>: ' + content + '</div>';
                                } else if (type === 1) {
                                    
                                    if (content === 'connection') {
                                        msg = Translator.trans('has_joined_the_chat_room', {}, 'chat');
                                    } else if (content === 'disconnection') {
                                        msg = Translator.trans('has_left_the_chat_room', {}, 'chat');
                                    } else if (content === 'kicked') {
                                        msg = Translator.trans('has_been_kicked_from_the_chat_room', {}, 'chat');
                                    } else if (content === 'banned') {
                                        msg = Translator.trans('has_been_banned_from_the_chat_room', {}, 'chat');
                                    }
                                    e = '<div><b>' + userFullName + ' ' + msg + '</b></div>';
                                } else if (type === 2) {
                                    
                                    if (content === 'open') {
                                        msg = Translator.trans('chat_room_open_msg', {}, 'chat');
                                    } else if (content === 'closed') {
                                        msg = Translator.trans('chat_room_closed_msg', {}, 'chat');
                                    }
                                    e = '<div><b>' + '</b></div>';
                                }
                                $('#chat-messages-panel-archives').append(e);
                            }
                            $('#chat-messages-panel-archives').append('<div class="text-center"><b>********************</b></div>');
                        }
                    }
                });
            }
            
            function updateScrollBarPosition()
            {
                var scrollHeight = $('#chat-messages-panel')[0].scrollHeight;
                $('#chat-messages-panel').scrollTop(scrollHeight);
            }
        }
    ]);
})();