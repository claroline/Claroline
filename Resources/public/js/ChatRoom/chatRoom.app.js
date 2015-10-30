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
    
    var chatRoomId;
    var chatRoomName;
    var username;
    var password;
    var xmppHost;
    var xmppMucHost;
    var xmppPort;
    var connection;
    
    function OnMessageStanza(stanza)
    {
        var from = $(stanza).attr('from');
        var type = $(stanza).attr('type');
        var jid = Strophe.getBareJidFromJid(from);
        var sender = Strophe.getResourceFromJid(from);
        var body = $(stanza).find('body').text();
        
        var message = '<li><b class="received-message">' + sender + ' : </b>' + body + '</li>';
        $('#chat-content').append(message);
        var scrollHeight = $('#chat-content')[0].scrollHeight;
        $('#chat-content').scrollTop(scrollHeight);
        
        return true;
    }
    
    function OnPresenceStanza(stanza)
    {
//        var from = $(stanza).attr('from');
//        var jid = Strophe.getBareJidFromJid(from);
//        var type = $(stanza).attr('type');
//        var show = $(stanza).find('show').text();
        console.log(stanza);
        
        return true;
    }
    
    function OnRosterStanza(stanza)
    {
        console.log('hey');
//        var from = $(stanza).attr('from');
//        var jid = Strophe.getBareJidFromJid(from);
//        var type = $(stanza).attr('type');
//        var show = $(stanza).find('show').text();
        
        return true;
    }
    
    var connectionCallBack = function (status) {
                
        if (status === Strophe.Status.CONNECTED) { 
            console.log('Connected');
            connection.addHandler(OnPresenceStanza, null, 'presence', 'groupchat');
            connection.addHandler(OnMessageStanza, null, 'message', 'groupchat');
            connection.addHandler(OnRosterStanza, 'jabber:iq:roster', 'iq', 'set');
            connection.send($pres());
            console.log(chatRoomName);
            connection.muc.join(
                chatRoomName + '@' + xmppMucHost, 
                username + '@' + xmppHost
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
    };
    
    function connection()
    {
        username = $('#chat-datas-box').data('username');
        password = $('#chat-datas-box').data('password');
        xmppHost = $('#chat-datas-box').data('xmpp-host');
        xmppMucHost = $('#chat-datas-box').data('xmpp-muc-host');
        xmppPort = $('#chat-datas-box').data('xmpp-port');
        chatRoomName = $('#chat-datas-box').data('resource-guid');
        chatRoomId = $('#chat-datas-box').data('chat-room-id');
        
        connection = new Strophe.Connection('/http-bind');
        connection.connect(
            username + '@' + xmppHost,
            password, 
            connectionCallBack
        );
    }
    var chatRoomApp = angular.module('chatRoomApp', []);
    
    chatRoomApp.controller('mainMenuCtrl', function($scope) {
        $scope.name = 'Bienvenue sur le Chat'
    });

    $('#msg-input').on('keypress', function (e) {
        
        if (e.keyCode === 13) {
            var msgContent = $(this).val();
            console.log(msgContent);
            connection.muc.message(chatRoomName + '@' + xmppMucHost, null, msgContent, null, 'groupchat');
            $('#msg-input').val('');
            
            $.ajax({
                url: Routing.generate(
                    'claro_chat_room_message_register',
                    {chatRoom: chatRoomId, username: username, message: msgContent}
                ),
                type: 'POST'
            });
//            connection.muc.configure(chatRoomName + '@' + xmppMucHost, function (datas) {console.log(datas); return true;});
//            console.log(connection.muc.rooms[chatRoomName + '@' + xmppMucHost]);
//            connection.muc.queryOccupants(chatRoomName + '@' + xmppMucHost, function (datas) {console.log(datas); return true;}, function (datas) {console.log(datas); return true;})
//
//            var info = $iq({
//              from: username,
//              to: chatRoomName + '@' + xmppMucHost,
//              type: 'get'
//            }).c('query',  {xmlns: Strophe.NS.DISCO_ITEMS});
//            connection.sendIQ(info, function (datas) {console.log(datas); return true;}, function (datas) {console.log(datas); return true;});
        }
    });

    $('#send-msg-btn').on('click', function () {
        var msgContent = $('#msg-input').val();
        connection.muc.message(chatRoomName + '@' + xmppMucHost, null, msgContent, null, 'groupchat');
        $('#msg-input').val('');
            
        $.ajax({
            url: Routing.generate(
                'claro_chat_room_message_register',
                {chatRoom: chatRoomId, username: username, message: msgContent}
            ),
            type: 'POST'
        });
    });
    
    connection();
})();