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

    $('#chat-room-archives-configuration-btn').on('click', function () {
        var chatRoomId = $(this).data('chat-room-id');
        
        window.Claroline.Modal.displayForm(
            Routing.generate(
                'claro_chat_room_configure_form',
                {chatRoom: chatRoomId}
            ),
            function () {
                window.location = Routing.generate(
                    'claro_chat_room_open',
                    {chatRoom: chatRoomId}
                );
            },
            function () {}
        );
    });
})();