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

    angular.module('ChatRoomModule', ['XmppModule']);

    $('#chat-room-app').on('keypress', '#msg-input', function (e) {

        if (e.keyCode === 13) {
            var msgContent = $(this).val();

            if (msgContent !== undefined && msgContent !== '') {
                angular.element(document.getElementById('input-box')).scope().sendMessage(msgContent);
    //            ChatRoom.send_message_to_room(msgContent);
            }
            $('#msg-input').val('');
        }
    });

    $('#chat-room-configuration-btn').on('click', function () {
    //    window.Claroline.Modal.displayForm(
    //        Routing.generate(
    //            'claro_chat_room_configure_form',
    //            {'chatRoom': ChatRoom.roomId}
    //        ),
    //        function () {},
    //        function () {}
    //    );
    });

    $(window).unload(function(){
        console.log('disconnecting...');
        angular.element(document.getElementById('chat-room-main')).scope().disconnect();
    });
})();