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

    var modal = window.Claroline.Modal;
    var id = '';

    var addFriendRequest = function(data) {
        window.location.reload();
    }

    var removeFriendRequestRow = function (event, requestid) {
        $('#friend-request-row-' + requestid).remove();
    };

    var removeFriendPendingRow = function (event, requestid) {
        $('#friend-pending-row-' + requestid).remove();
    };

    var confirmPendingFriend = function (event, friendid) {
        window.location.reload();
    }


    $('body')
        .on('click', '#request-friend-btn', function(event) {
            var url = Routing.generate('oauth_request_friend_form');
            modal.displayForm(url, addFriendRequest, function(){}, 'form_request_friend');
        })
        .on('click', '.configure-authentication-btn', function(event) {
            var url = Routing.generate('oauth_request_authentication_form', {'friend': $(event.target).attr('data-request-id')});
            modal.displayForm(url, addFriendRequest, function(){}, 'oauth_request_authentication_form');
        })
        .on('click', '.show-client-access-btn', function(event) {
            modal.simpleContainer('', $(event.target).attr('data-client-random'));
        })
        .on('click', '.show-secret-btn', function(event) {
            modal.simpleContainer('', $(event.target).attr('data-secret'));
        })
        .on('click', '.delete-friend-request-btn', function(event) {
            var requestid = $(event.target).attr('data-friend-request-id');

            modal.confirmRequest(
                Routing.generate(
                    'oauth_request_friend_remove',
                    {'friend': requestid}
                ),
                removeFriendRequestRow,
                requestid,
                Translator.trans('delete_platform_friend_message', {}, 'platform'),
                Translator.trans('delete_platform_friend', {}, 'platform')
            );
        })
        .on('click', '.delete-friend-pending-btn', function(event) {
            var friendid = $(event.target).attr('data-friend-pending-id');

            modal.confirmRequest(
                Routing.generate(
                    'oauth_pending_friend_remove',
                    {'friend': friendid}
                ),
                removeFriendPendingRow,
                friendid,
                Translator.trans('delete_friend_pending_message', {}, 'platform'),
                Translator.trans('delete_friend_pending', {}, 'platform')
            );
        })
        .on('click', '.add-friend-pending-btn', function(event) {
            var friendid = $(event.target).attr('data-friend-pending-id');
            console.debug(friendid);

            modal.confirmRequest(
                Routing.generate(
                    'oauth_friends_accept',
                    {'friend': friendid}
                ),
                confirmPendingFriend,
                friendid,
                Translator.trans('add_friend_confirm_message', {}, 'platform'),
                Translator.trans('add_pending', {}, 'platform')
            );
        });
}) ();
