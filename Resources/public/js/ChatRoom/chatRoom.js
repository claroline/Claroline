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
    firstName: null,
    lastName: null,
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
            var body = $(message).find('html > body').html();
            
            if (body === undefined) {
                body = $(message).find('body').text();
            }
            var datas = $(message).find('datas');
            var firstName = datas.attr('firstName');
            var lastName = datas.attr('lastName');
            
            
            var sender = (firstName !== undefined && lastName !== undefined) ?
                firstName + ' ' + lastName :
                Strophe.getResourceFromJid(from);
            ChatRoom.display_message(sender, body);
        }

        return true;
    },
    on_room_presence: function (presence) {
        var from = $(presence).attr('from');
        var roomName = Strophe.getBareJidFromJid(from);

        if (roomName.toLowerCase() === ChatRoom.room.toLowerCase()) {
            var username = Strophe.getResourceFromJid(from);
            var type = $(presence).attr('type');
            var datas = $(presence).find('datas');
            var firstName = datas.attr('firstName');
            var lastName = datas.attr('lastName');
            var name = (firstName !== undefined && lastName !== undefined) ?
                firstName + ' ' + lastName :
                username;
            
            if (type === 'unavailable') {
                ChatRoom.remove_user(username);
            } else {
                ChatRoom.display_user(username, name);
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
                .up()
                .c('datas', {firstName: ChatRoom.firstName, lastName: ChatRoom.lastName})
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
    connect: function (server, mucServer, roomId, roomName, username, password, firstName, lastName) {
        ChatRoom.xmppHost = server;
        ChatRoom.xmppMucHost = mucServer;
        ChatRoom.roomId = roomId;
        ChatRoom.roomName = roomName;
        ChatRoom.room = roomName + '@' + mucServer;
        ChatRoom.username = username;
        ChatRoom.password = password;
        ChatRoom.firstName = firstName;
        ChatRoom.lastName = lastName;

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
            .up()
            .c('datas', {firstName: ChatRoom.firstName, lastName: ChatRoom.lastName})
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
        var scrollHeight = $('#chat-content')[0].scrollHeight;
        $('#chat-content').scrollTop(scrollHeight);
    },
    display_user: function (username, name) {
        ChatRoom.participants[username] = {username: username, name: name};
        var txt = '<span class="participant-' +
            username +
            '"><i class="fa fa-user"></i> ' + 
            name + 
            '&nbsp;' +
            '<i class="fa fa-microphone-slash pointer-hand chat-room-mute-btn" data-toggle="tooltip" data-placement="top" title="' +
            Translator.trans('mute', {}, 'chat') + 
            '"></i>' + 
            '&nbsp;' +
            '<i class="fa fa-ban pointer-hand chat-room-ban-btn" data-toggle="tooltip" data-placement="top" title="' +
            Translator.trans('ban', {}, 'chat') + 
            '"></i>' + 
            '<br></span>';
        $('#chat-room-users-panel').append(txt);
    },
    remove_user: function (username) {
        delete ChatRoom.participants[username];
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
        
        if (msgContent !== undefined && msgContent !== '') {
            ChatRoom.send_message_to_room(msgContent);
        }
        $('#msg-input').val('');
    }
});

$('#send-msg-btn').on('click', function () {
    var msgContent = $('#msg-input').val();
    
    if (msgContent !== undefined && msgContent !== '') {
        ChatRoom.send_message_to_room(msgContent);
    }
    $('#msg-input').val('');
});

$('#chat-room-configuration-btn').on('click', function () {
    window.Claroline.Modal.displayForm(
        Routing.generate(
            'claro_chat_room_configure_form',
            {'chatRoom': ChatRoom.roomId}
        ),
        function () {},
        function () {}
    );
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