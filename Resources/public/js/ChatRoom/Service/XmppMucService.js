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
        '$http',
        'XmppService',
        function ($rootScope, $http, XmppService) {
            var connected = false;
            var room = null;
            var roomId = null;
            var roomName = null;
            var xmppMucHost = null;
            var myUsername = null;
            var myRole = null;
            var myAffiliation = null;
            var users = [];
            var bannedUsers = [];
            var vm;

            var onRoomMessage = function (message) {
//                console.log(message);
                var from = $(message).attr('from');
                var type = $(message).attr('type');
                var roomName = Strophe.getBareJidFromJid(from);

                if (type === 'groupchat' && roomName.toLowerCase() === room.toLowerCase()) {
                    var delayElement = $(message).find('delay');
                    
                    if (delayElement === undefined || delayElement[0] === undefined) {
                        var body = $(message).find('html > body').html();
                        var statusElement  = $(message).find('status');

                        if (statusElement === undefined || statusElement.attr('code') !== '104') {

                            if (body === undefined) {
                                body = $(message).find('body').text();
                            }
                            var datas = $(message).find('datas');
                            var status = datas.attr('status');

                            if (status === 'raw') {
                                $rootScope.$broadcast('rawRoomMessageEvent', {message: body});
                            } else if (status === 'management') {
                                console.log(message);
                                var type =  datas.attr('type');
                                var username = datas.attr('username');
                                var value =  datas.attr('value');
                                $rootScope.$broadcast('managementEvent', {type: type, username: username, value: value});
                            } else {
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
                        }
                    }
                }

                return true;
            };

            var onRoomPresence = function (presence) {
//                console.log(presence);
                var from = $(presence).attr('from');
                var roomName = Strophe.getBareJidFromJid(from);
                var status = $(presence).find('status');
                var statusCode = status.attr('code');
                var error = $(presence).find('error');
                var errorCode = error.attr('code');
//                console.log('##### STATUS = ' + statusCode + ' ####');
//                console.log('##### ERROR = ' + errorCode + ' ####');
                
                if (roomName.toLowerCase() === room.toLowerCase()) {
                    var username = Strophe.getResourceFromJid(from);
//                    console.log('##### USERNAME = ' + username + ' ####');
                    var type = $(presence).attr('type');
                    var datas = $(presence).find('datas');
                    var firstName = datas.attr('firstName');
                    var lastName = datas.attr('lastName');
                    var color = datas.attr('color');
                    var item = $(presence).find('item');
                    var affiliation = item.attr('affiliation');
                    var role = item.attr('role');
                    color = (color === undefined) ? null : color;

                    var name = (firstName !== undefined && lastName !== undefined) ?
                        firstName + ' ' + lastName :
                        username;

                    if (errorCode === '403') {
                        $rootScope.$broadcast('xmppMucForbiddenConnectionEvent');
                        
                        return true;
                    }

                    if (username === XmppService.getUsername()) {
                        myRole = role;
                        myAffiliation = affiliation;
                        
                        if (statusCode === '110') {
                            myUsername = username;
                            $rootScope.$broadcast('xmppMucConnectedEvent');
                            $rootScope.$broadcast('myPresenceConfirmationEvent');

                            var route = Routing.generate(
                                'claro_chat_room_presence_register',
                                {
                                    chatRoom: roomId, 
                                    username: XmppService.getUsername(),
                                    fullName: XmppService.getFullName(),
                                    status: 'connection'
                                }
                            );
                            $http.post(route);
                            
                            if (vm.isAdmin()) {
                                vm.requestOutcastList();
                            }
                        } else if (statusCode === '301') {
                            $rootScope.$broadcast('xmppMucBannedEvent');
                            var route = Routing.generate(
                                'claro_chat_room_presence_register',
                                {
                                    chatRoom: roomId, 
                                    username: XmppService.getUsername(), 
                                    fullName: XmppService.getFullName(),
                                    status: 'banned'
                                }
                            );
                            $http.post(route);
                        } else if (statusCode === '307') {
                            $rootScope.$broadcast('xmppMucKickedEvent');
                            var route = Routing.generate(
                                'claro_chat_room_presence_register',
                                {
                                    chatRoom: roomId, 
                                    username: XmppService.getUsername(), 
                                    fullName: XmppService.getFullName(),
                                    status: 'kicked'
                                }
                            );
                            $http.post(route);
                        }
                    }
                    
                    if (type === 'unavailable') {
                        vm.removeUser(username, statusCode);
                    } else {
                        vm.addUser(username, name, color, affiliation, role);
                    }
                }

                return true;
            };

            var onIQStanza = function (iq) {
//                console.log(iq);
                var type = $(iq).attr('type');
                var id = $(iq).attr('id');
//                console.log(type);
//                console.log(id);
                
                if (type === 'result') {
                    
                    if (id === 'room-outcast-list') {
                        
                        var items = $(iq).find('item');
                        items.each(function () {
                            var jid = $(this).attr('jid');
                            var username = Strophe.getNodeFromJid(jid);
                            vm.addBannedUser(username);
                        });
                    } else if (id.substring(0, 4) === 'ban-') {
                        var username = id.substring(4, id.length);
                        vm.addBannedUser(username);
                    } else if (id.substring(0, 6) === 'unban-') {
                        var username = id.substring(6, id.length);
                        vm.removeBannedUser(username);
                    }
                }

                return true;
            };

            $rootScope.$on('xmppConnectedEvent', function (event) {
                var connection = XmppService.getConnection();
                connection.addHandler(onRoomPresence, null, 'presence');
                connection.addHandler(onRoomMessage, null, 'message', 'groupchat');
                connection.addHandler(onIQStanza, null, 'iq');
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
                    vm = this;
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
                    connected = false;
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
        
                    var route = Routing.generate(
                        'claro_chat_room_presence_register',
                        {
                            chatRoom: roomId, 
                            username: XmppService.getUsername(), 
                            fullName: XmppService.getFullName(),
                            status: 'disconnection'
                        }
                    );
                    $.ajax({
                        url: route,
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

                    var route = Routing.generate(
                        'claro_chat_room_message_register',
                        {
                            chatRoom: roomId, 
                            username: XmppService.getUsername(),
                            fullName: XmppService.getFullName(),
                            message: message
                        }
                    );
                    $http.post(route);
                },
                getRoomConfiguration: function () {
                    var iq = $iq({
                        id: 'room-config-request',
                        from: XmppService.getUsername() + "@" + XmppService.getXmppHost() + '/' + roomName,
                        to: room,
                        type: 'get'
                    }).c('query', {xmlns: 'http://jabber.org/protocol/muc#owner'});
                    XmppService.getConnection().sendIQ(iq);
                },
                setConnected: function (isConnected) {
                    connected = isConnected;
                },
                isConnected: function () {
                    
                    return connected;
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
                getBannedUsers: function () {
                  
                    return bannedUsers;
                },
                addUser: function (username, name, color, affiliation, role) {
                    var isPresent = false;
                    
                    for (var i = 0; i < users.length; i++) {
                        var currentUsername =  users[i]['username'];

                        if (username === currentUsername) {
                            isPresent = true;
                            users[i]['affiliation'] = affiliation;
                            users[i]['role'] = role;
                            break;
                        }
                    }

                    if (!isPresent) {
                        users.push({username: username, name: name, color: color, affiliation: affiliation, role: role});
                        $rootScope.$broadcast('newPresenceEvent', {username: username, name: name, status: 'connection'});
                    }
                    $rootScope.$broadcast(
                        'userConnectionEvent', 
                        {username: username, name: name, color: color, affiliation: affiliation, role: role}
                    );
                },
                removeUser: function (username, statusCode) {

                    for (var i = 0; i < users.length; i++) {
                        var currentUsername =  users[i]['username'];

                        if (username === currentUsername) {
                            var currentName = users[i]['name'];
                            var currentUsername = users[i]['username'];
                            users.splice(i, 1);
                            $rootScope.$broadcast(
                                'userDisconnectionEvent',
                                {
                                    username: currentUsername, 
                                    name: currentName, 
                                    statusCode: statusCode
                                }
                            );
//                            $rootScope.$broadcast('newPresenceEvent', {username: currentUsername, name: currentName, status: 'disconnection'});
                            break;
                        }
                    }
                },
                hasUser: function (username) {
                    var isPresent = false;
                    
                    for (var i = 0; i < users.length; i++) {
                        var currentUsername =  users[i]['username'];

                        if (username === currentUsername) {
                            isPresent = true;
                            break;
                        }
                    }
                    
                    return isPresent;
                },
                getUserFullName: function (username) {
                    var name = username;
                    
                    for (var i = 0; i < users.length; i++) {
                        var currentUsername =  users[i]['username'];

                        if (username === currentUsername) {
                            name = users[i]['name'];
                            break;
                        }
                    }
                    
                    return name;
                },
                kickUser: function (username) {
                    var iq = $iq({
                        id: 'kick-' + username,
                        from: XmppService.getUsername() + "@" + XmppService.getXmppHost() + '/' + roomName,
                        to: room,
                        type: 'set'
                    }).c('x', {xmlns: 'http://jabber.org/protocol/muc#admin'})
                    .c('item', {nick: username, role: 'none'});
                    XmppService.getConnection().sendIQ(iq);
                },
                muteUser: function (username) {
                    var iq = $iq({
                        id: 'mute-' + username,
                        from: XmppService.getUsername() + "@" + XmppService.getXmppHost() + '/' + roomName,
                        to: room,
                        type: 'set'
                    }).c('x', {xmlns: 'http://jabber.org/protocol/muc#admin'})
                    .c('item', {nick: username, role: 'visitor'});
                    XmppService.getConnection().sendIQ(iq);
                },
                unmuteUser: function (username) {
                    var iq = $iq({
                        id: 'unmute-' + username,
                        from: XmppService.getUsername() + "@" + XmppService.getXmppHost() + '/' + roomName,
                        to: room,
                        type: 'set'
                    }).c('x', {xmlns: 'http://jabber.org/protocol/muc#admin'})
                    .c('item', {nick: username, role: 'participant'});
                    XmppService.getConnection().sendIQ(iq);
                },
                banUser: function (username) {
                    var iq = $iq({
                        id: 'ban-' + username,
                        from: XmppService.getUsername() + "@" + XmppService.getXmppHost() + '/' + roomName,
                        to: room,
                        type: 'set'
                    }).c('query', {xmlns: 'http://jabber.org/protocol/muc#admin'})
                    .c('item', {jid: username + "@" + XmppService.getXmppHost(), affiliation: 'outcast'});
                    XmppService.getConnection().sendIQ(iq);
                },
                unbanUser: function (username) {
                    var iq = $iq({
                        id: 'unban-' + username,
                        from: XmppService.getUsername() + "@" + XmppService.getXmppHost() + '/' + roomName,
                        to: room,
                        type: 'set'
                    }).c('query', {xmlns: 'http://jabber.org/protocol/muc#admin'})
                    .c('item', {jid: username + "@" + XmppService.getXmppHost(), affiliation: 'none'});
                    XmppService.getConnection().sendIQ(iq);
                },
                addBannedUser: function (username) {
                    var isPresent = false;
                    for (var i = 0; i < bannedUsers.length; i++) {
                        var currentUsername =  bannedUsers[i]['username'];

                        if (username === currentUsername) {
                            isPresent = true;
                            break;
                        }
                    }

                    if (!isPresent) {
                        bannedUsers.push(username);
                        $rootScope.$broadcast('xmppMucBanUserEvent', username);
                    }
                },
                removeBannedUser: function (username) {

                    for (var i = 0; i < bannedUsers.length; i++) {
                        var currentUsername =  bannedUsers[i];

                        if (username === currentUsername) {
                            bannedUsers.splice(i, 1);
                            $rootScope.$broadcast('xmppMucUnbanUserEvent', username);
                            break;
                        }
                    }
                },
                requestOutcastList: function () {
                    var iq = $iq({
                        id: 'room-outcast-list',
                        from: XmppService.getUsername() + "@" + XmppService.getXmppHost() + '/' + roomName,
                        to: room,
                        type: 'get'
                    }).c('query', {xmlns: 'http://jabber.org/protocol/muc#admin'})
                    .c(
                        'item',
                        {
                            affiliation: 'outcast'
                        }
                    );
                    XmppService.getConnection().sendIQ(iq);
                },
                closeRoom: function () {
                    var iq = $iq({
                        id: 'room-config-submit',
                        from: XmppService.getUsername() + "@" + XmppService.getXmppHost() + '/' + roomName,
                        to: room,
                        type: 'set'
                    }).c('query', {xmlns: 'http://jabber.org/protocol/muc#owner'})
                    .c(
                        'x',
                        {
                            xmlns: 'jabber:x:data',
                            type: 'submit'
                        }
                    )
                    .c('field', {var: 'FORM_TYPE'})
                    .c('value').t('http://jabber.org/protocol/muc#roomconfig')
                    .up()
                    .up()
                    .c('field', {var: 'muc#roomconfig_persistentroom'})
                    .c('value').t(1)
                    .up()
                    .up()
                    .c('field', {var: 'muc#roomconfig_moderatedroom'})
                    .c('value').t(1)
                    .up()
                    .up()
                    .c('field', {var: 'muc#roomconfig_whois'})
                    .c('value').t('moderators');
                    XmppService.getConnection().sendIQ(iq);
                    
                    for (var i = 0; i < users.length; i++) {
                        var username = users[i]['username'];
                        var iq = $iq({
                            id: 'mute-' + username,
                            from: XmppService.getUsername() + "@" + XmppService.getXmppHost() + '/' + roomName,
                            to: room,
                            type: 'set'
                        }).c('x', {xmlns: 'http://jabber.org/protocol/muc#admin'})
                        .c('item', {nick: username, role: 'visitor'});
                        XmppService.getConnection().sendIQ(iq);
                    }
                    
                    var message = Translator.trans('chat_room_closed_msg', {}, 'chat');
                    
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
                                color: XmppService.getColor(),
                                status: 'raw'
                            }
                        )
                    );

                    var route = Routing.generate(
                        'claro_chat_room_status_register',
                        {
                            chatRoom: roomId, 
                            username: XmppService.getUsername(),
                            fullName: XmppService.getFullName(),
                            status: 'closed'
                        }
                    );
                    $http.post(route);
                },
                openRoom: function () {
                    var iq = $iq({
                        id: 'room-config-submit',
                        from: XmppService.getUsername() + "@" + XmppService.getXmppHost() + '/' + roomName,
                        to: room,
                        type: 'set'
                    }).c('query', {xmlns: 'http://jabber.org/protocol/muc#owner'})
                    .c(
                        'x',
                        {
                            xmlns: 'jabber:x:data',
                            type: 'submit'
                        }
                    )
                    .c('field', {var: 'FORM_TYPE'})
                    .c('value').t('http://jabber.org/protocol/muc#roomconfig')
                    .up()
                    .up()
                    .c('field', {var: 'muc#roomconfig_persistentroom'})
                    .c('value').t(1)
                    .up()
                    .up()
                    .c('field', {var: 'muc#roomconfig_moderatedroom'})
                    .c('value').t(0)
                    .up()
                    .up()
                    .c('field', {var: 'muc#roomconfig_whois'})
                    .c('value').t('moderators');
                    XmppService.getConnection().sendIQ(iq);
                    
                    for (var i = 0; i < users.length; i++) {
                        var username = users[i]['username'];
                        var iq = $iq({
                            id: 'mute-' + username,
                            from: XmppService.getUsername() + "@" + XmppService.getXmppHost() + '/' + roomName,
                            to: room,
                            type: 'set'
                        }).c('x', {xmlns: 'http://jabber.org/protocol/muc#admin'})
                        .c('item', {nick: username, role: 'participant'});
                        XmppService.getConnection().sendIQ(iq);
                    }
                    
                    var message = Translator.trans('chat_room_open_msg', {}, 'chat');
                    
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
                                color: XmppService.getColor(),
                                status: 'raw'
                            }
                        )
                    );

                    var route = Routing.generate(
                        'claro_chat_room_status_register',
                        {
                            chatRoom: roomId, 
                            username: XmppService.getUsername(),
                            fullName: XmppService.getFullName(),
                            status: 'open'
                        }
                    );
                    $http.post(route);
                },
                isAdmin: function () {
                    
                    return myAffiliation === 'admin' || myAffiliation === 'owner';
                },
                isModerator: function () {
                    
                    return myRole === 'moderator';
                },
                canParticipate: function () {
                    
                    return myRole !== 'none' && myRole !== 'visitor';
                },
                broadcastCustomEvent: function (eventName, datas) {
                    $rootScope.$broadcast(eventName, datas);
                }
            };
        }
    ]);
})();
