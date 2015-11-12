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

    angular.module('ChatRoomModule').controller('ChatRoomXmppCtrl', ['$scope', '$rootScope', 'XmppMucService', 'XmppService',
        function ($scope, $rootScope, XmppMucService, XmppService) {
            $scope.RTC = null;
            $scope.ice_config = {iceServers: [{url: 'stun:stun.l.google.com:19302'}]};
            $scope.RTCPeerConnection = null;
            
            $scope.connect = function (
                server,
                mucServer, 
                boshPort, 
                roomId, 
                roomName, 
                username, 
                password, 
                firstName, 
                lastName, 
                color
            ) {
                XmppMucService.connect(
                    server,
                    mucServer, 
                    boshPort, 
                    roomId, 
                    roomName, 
                    username, 
                    password, 
                    firstName, 
                    lastName, 
                    color
                );
            };
            $scope.disconnect = function () {
                XmppMucService.disconnect();
                console.log('disconnected');
            };

            $rootScope.$on('xmppConnectedEvent', function (event) {
                var connection = XmppService.getConnection();
                $scope.RTC = setupRTC();
                connection.jingle.ice_config = $scope.ice_config;
                if ($scope.RTC) {
                    connection.jingle.pc_constraints = $scope.RTC.pc_constraints;
                }
                if ($scope.RTC !== null) {
                    $scope.RTCPeerconnection = $scope.RTC.peerconnection;
                    
                    if ($scope.RTC.browser == 'firefox') {
                        connection.jingle.media_constraints.mandatory.MozDontOfferDataChannel = true;
                    }
                    
//                    $(document).bind('connected', onConnected);
//                    $(document).bind('mediaready.jingle', onMediaReady);
//                    $(document).bind('mediafailure.jingle', onMediaFailure);
//                    $(document).bind('callincoming.jingle', onCallIncoming);
//                    $(document).bind('callactive.jingle', onCallActive);
//                    $(document).bind('callterminated.jingle', onCallTerminated);

//                    $(document).bind('remotestreamadded.jingle', onRemoteStreamAdded);
//                    $(document).bind('remotestreamremoved.jingle', onRemoteStreamRemoved);
//                    $(document).bind('iceconnectionstatechange.jingle', onIceConnectionStateChanged);
//                    $(document).bind('nostuncandidates.jingle', noStunCandidates);
//                    $(document).bind('ack.jingle', function (event, sid, ack) {
//                        console.log('got stanza ack for ' + sid, ack);
//                    });
//                    $(document).bind('error.jingle', function (event, sid, err) {
//                        console.log('got stanza error for ' + sid, err);
//                    });
//                    $(document).bind('packetloss.jingle', function (event, sid, loss) {
//                        console.warn('packetloss', sid, loss);
//                    });
                    //setStatus('please allow access to microphone and camera');
                    //getUserMediaWithConstraints();
                }
            });
        }
    ]);
})();