/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

var RTC = null;
var ice_config = {iceServers: [{url: 'stun:stun.l.google.com:19302'}]};
var RTCPeerConnection = null;
var AUTOACCEPT = true;
var PRANSWER = false; // use either pranswer or autoaccept
var RAWLOGGING = true;
var MULTIPARTY = true;
var localStream = null;
var connection = null;
var myroomjid = null;
var roomjid = null;
var list_members = [];
    
(function () {
    'use strict';

    angular.module('ChatRoomModule').factory('XmppJingleService', [
        '$rootScope', 
        'XmppService', 
        'XmppMucService',
        function ($rootScope, XmppService, XmppMucService) {
            
            return {
                getRTC: function () {

                    return RTC;
                },
                getRTCPeerConnection: function () {

                    return RTCPeerConnection;
                }
            };
        }
    ]);
})();