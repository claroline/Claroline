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

    angular.module('ChatRoomModule').factory('XmppMucService', [
        '$rootScope', 
        'XmppService',
        function ($rootScope, XmppService) {
            var room = null;
            var roomId = null;
            var roomName = null;
            var xmppMucHost = null;
            var users = [];

            var onRoomMessage = function (message) {
                var from = $(message).attr('from');
                var type = $(message).attr('type');
                var roomName = Strophe.getBareJidFromJid(from);

                if (type === 'groupchat' && roomName.toLowerCase() === room.toLowerCase()) {
                    var body = $(message).find('html > body').html();

                    if (body === undefined) {
                        body = $(message).find('body').text();
                    }
                    var datas = $(message).find('datas');
                    var firstName = datas.attr('firstName');
                    var lastName = datas.attr('lastName');
                    var color = datas.attr('color');
                    color = (color === undefined) ? null : color;

                    var sender = (firstName !== undefined && lastName !== undefined) ?
                        firstName + ' ' + lastName :
                        Strophe.getResourceFromJid(from);
                    $rootScope.$broadcast(
                        'newMessageEvent', 
                        {sender: sender, message: body, color: color}
                    );
                }

                return true;
            };

            var onRoomPresence = function (presence) {
                console.log(presence);
                var from = $(presence).attr('from');
                var roomName = Strophe.getBareJidFromJid(from);
                
                if (roomName.toLowerCase() === room.toLowerCase()) {
                    var username = Strophe.getResourceFromJid(from);
                    var type = $(presence).attr('type');
                    var datas = $(presence).find('datas');
                    var firstName = datas.attr('firstName');
                    var lastName = datas.attr('lastName');
                    var color = datas.attr('color');
                    color = (color === undefined) ? null : color;

                    var name = (firstName !== undefined && lastName !== undefined) ?
                        firstName + ' ' + lastName :
                        username;

                    if (username === XmppService.getUsername()) {
                        $rootScope.$broadcast('myPresenceConfirmationEvent');
                    }
                    
                    if (type === 'unavailable') {
                        $rootScope.$broadcast('userDisconnectionEvent', username);
                    } else {
                        $rootScope.$broadcast(
                            'userConnectionEvent', 
                            {username: username, name: name, color: color}
                        );
                    }
                }

                return true;
            };

            $rootScope.$on('xmppConnectedEvent', function (event) {
                var connection = XmppService.getConnection();
                connection.addHandler(onRoomPresence, null, 'presence');
                connection.addHandler(onRoomMessage, null, 'message', 'groupchat');
                connection.send(
                    $pres({
                        to: room + "/" + XmppService.getUsername()
                    })
                    .c(
                        'datas',
                        {
                            firstName: XmppService.getFirstName(), 
                            lastName: XmppService.getLastName(), 
                            color: XmppService.getColor()
                        }
                    )
                );
                $rootScope.$broadcast('xmppMucConnectedEvent');
        
                $.ajax({
                    url: Routing.generate(
                        'claro_chat_room_presence_register',
                        {
                            chatRoom: roomId, 
                            username: XmppService.getUsername(), 
                            status: 'connection'
                        }
                    ),
                    type: 'POST'
                });
            });

            return {
                connect: function (
                    server,
                    mucServer, 
                    boshPort, 
                    roomIdParam, 
                    roomNameParam, 
                    username, 
                    password, 
                    firstName, 
                    lastName, 
                    color
                ) {
                    xmppMucHost = mucServer;
                    roomId = roomIdParam;
                    roomName = roomNameParam;
                    room = roomName + '@' + mucServer;

                    XmppService.connect(
                        server, 
                        boshPort, 
                        username, 
                        password, 
                        firstName, 
                        lastName, 
                        color
                    );
                },
                disconnect: function () {
                    var connection = XmppService.getConnection();
                    connection.send(
                        $pres({
                            to: room + "/" + XmppService.getUsername(),
                            from: XmppService.getUsername() + "@" + XmppService.getXmppHost() + '/' + roomName,
                            type: "unavailable"
                        })
                    );
                    connection.flush();
                    connection.disconnect();
        
                    $.ajax({
                        url: Routing.generate(
                            'claro_chat_room_presence_register',
                            {
                                chatRoom: roomId, 
                                username: XmppService.getUsername(), 
                                status: 'disconnection'
                            }
                        ),
                        type: 'POST'
                    });
                },
                sendMessageToRoom: function (message) {
                     XmppService.getConnection().send(
                        $msg({
                            to: room,
                            type: "groupchat"
                        }).c('body').t(message)
                        .up()
                        .c(
                            'datas',
                            {
                                firstName:  XmppService.getFirstName(), 
                                lastName: XmppService.getLastName(), 
                                color: XmppService.getColor()
                            }
                        )
                    );

                    $.ajax({
                        url: Routing.generate(
                            'claro_chat_room_message_register',
                            {
                                chatRoom: roomId, 
                                username: XmppService.getUsername(), 
                                message: message
                            }
                        ),
                        type: 'POST'
                    });
                },
                getRoom: function () {

                    return room;
                },
                getRoomName: function () {

                    return roomName;
                },
                getRoomId: function () {

                    return roomId;
                },
                getXmppMucHost: function () {
                    
                    return xmppMucHost;
                },
                getUsers: function () {
                    
                    return users;
                },
                addUser: function (userDatas) {
                    users.push(userDatas);
                },
                removeUser: function (index) {
                    users.splice(index, 1);
                }
            };
        }
    ]);
})();