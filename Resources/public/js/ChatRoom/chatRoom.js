/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

var ChatRoom = {
    connection: null,
    room: null,
    roomId: null,
    roomName: null,
    username: null,
    password: null,
    xmppHost: null,
    xmppMucHost: null,
    NS_MUC: "http://jabber.org/protocol/muc",
    participants: {},
    messages: [],
    on_room_message: function (message) {
        var from = $(message).attr('from');
        var type = $(message).attr('type');
        var roomName = Strophe.getBareJidFromJid(from);

        if (type === 'groupchat' &&
            roomName.toLowerCase() === ChatRoom.room.toLowerCase()) {
            var sender = Strophe.getResourceFromJid(from);
            var body = $(message).find('body').text(); 
            ChatRoom.display_message(sender, body);
        }

        return true;
    },
    on_room_presence: function (presence) {
        var from = $(presence).attr('from');
        var roomName = Strophe.getBareJidFromJid(from);

        if (roomName.toLowerCase() === ChatRoom.room.toLowerCase()) {
            var user = Strophe.getResourceFromJid(from);
            var type = $(presence).attr('type');
            
            if (type === 'unavailable') {
                ChatRoom.remove_user(user);
            } else {
                ChatRoom.display_user(user);
            }
        }

        return true;
    },
    on_connection_callback: function (status) {

        if (status === Strophe.Status.CONNECTED) { 
            console.log('Connected');
            ChatRoom.connection.send($pres().c('priority').t('-1'));
            ChatRoom.connection.addHandler(ChatRoom.on_room_presence, null, 'presence');
            ChatRoom.connection.addHandler(ChatRoom.on_room_message, null, 'message', 'groupchat');

            ChatRoom.connection.send(
                $pres({
                    to: ChatRoom.room + "/" + ChatRoom.username
                }).c('x', {xmlns: ChatRoom.NS_MUC})
            );
        } else if (status === Strophe.Status.CONNFAIL) {
            console.log('Connection failed !');
        } else if (status === Strophe.Status.DISCONNECTED) {
            console.log('Disconnected');
        } else if (status === Strophe.Status.CONNECTING) {
            console.log('Connecting...');
        } else if (nStatus === Strophe.Status.DISCONNECTING) {
            console.log('Disconnecting...');   
        }
    },
    connect: function (server, mucServer, roomId, roomName, username, password) {
        ChatRoom.xmppHost = server;
        ChatRoom.xmppMucHost = mucServer;
        ChatRoom.roomId = roomId;
        ChatRoom.roomName = roomName;
        ChatRoom.room = roomName + '@' + mucServer;
        ChatRoom.username = username;
        ChatRoom.password = password;

        ChatRoom.connection = new Strophe.Connection('/http-bind');
        ChatRoom.connection.connect(
            ChatRoom.username + '@' + ChatRoom.xmppHost,
            ChatRoom.password, 
            ChatRoom.on_connection_callback
        );
    },
    send_message_to_room: function (message) {
        ChatRoom.connection.send(
            $msg({
                to: ChatRoom.room,
                type: "groupchat"
            }).c('body').t(message)
        );

        $.ajax({
            url: Routing.generate(
                'claro_chat_room_message_register',
                {
                    chatRoom: ChatRoom.roomId, 
                    username: ChatRoom.username, 
                    message: message
                }
            ),
            type: 'POST'
        });
    },
    display_message: function (sender, message) {
        ChatRoom.messages.push({sender: sender, message: message});
        var txt = '<span><b class="received-message">' + sender + '</b> : ' + message + '<br></span>';
        $('#chat-content').append(txt);
//        var scrollHeight = $('#chat-content')[0].scrollHeight;
//        $('#chat-content').scrollTop(scrollHeight);
    },
    display_user: function (username) {
        ChatRoom.participants[username] = username;
        var txt = '<span class="participant-' + username + '"><i class="fa fa-user"></i> ' + username + '<br></span>';
        $('#chat-room-users-panel').append(txt);
    },
    remove_user: function (username) {
        delete ChatRoom.participants[username];
        console.log(ChatRoom.participants);
        $('.participant-' + username).remove();
    }
};

var chatRoomApp = angular.module('chatRoomApp', []);

chatRoomApp.controller('mainMenuCtrl', function($scope) {
    $scope.name = 'Bienvenue sur le Chat'
});

$('#msg-input').on('keypress', function (e) {

    if (e.keyCode === 13) {
        var msgContent = $(this).val();
        ChatRoom.send_message_to_room(msgContent);
        $('#msg-input').val('');
    }
});

$('#send-msg-btn').on('click', function () {
    var msgContent = $('#msg-input').val();
    ChatRoom.send_message_to_room(msgContent);
    $('#msg-input').val('');
});

$(window).unload(function(){
    ChatRoom.connection.send(
        $pres({
            to: ChatRoom.room + "/" + ChatRoom.username,
            type: "unavailable"
        })
    );
    ChatRoom.connection.flush();
    ChatRoom.connection.disconnect();
});