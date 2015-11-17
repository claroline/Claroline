/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

var RTC = null;
//var ice_config = {iceServers: [{url: 'stun:stun.l.google.com:19302'}]};
//var RTCPeerConnection = null;
//var AUTOACCEPT = true;
//var PRANSWER = false; // use either pranswer or autoaccept
//var RAWLOGGING = true;
//var MULTIPARTY = true;
//var localStream = null;
//var connection = null;
//var myroomjid = null;
//var roomjid = null;
//var list_members = [];

(function () {
    'use strict';

    angular.module('ChatRoomModule').controller('ChatRoomVideoCtrl', [
        '$scope',
        '$rootScope', 
        'XmppService', 
        'XmppMucService', 
        function ($scope, $rootScope, XmppService, XmppMucService) {
            $scope.localStream = null;
            
            function onMediaReady(event, stream) {
                $scope.localStream = stream;
                XmppService.getConnection().jingle.localStream = stream;
                for (var i = 0; i < $scope.localStream.getAudioTracks().length; i++) {
                    console.log('using audio device "' + $scope.localStream.getAudioTracks()[i].label + '"');
                }
                for (i = 0; i < $scope.localStream.getVideoTracks().length; i++) {
                    console.log('using video device "' + $scope.localStream.getVideoTracks()[i].label + '"');
                }
                // mute video on firefox and recent canary
                $('#minivideo')[0].muted = true;
                $('#minivideo')[0].volume = 0;

                RTC.attachMediaStream($('#minivideo'), $scope.localStream);

//                doConnect();

                if (typeof hark === "function") {
                    var options = { interval: 400 };
                    var speechEvents = hark(stream, options);

                    speechEvents.on('speaking', function () {
                        console.log('speaking');
                    });

                    speechEvents.on('stopped_speaking', function () {
                        console.log('stopped_speaking');
                    });
                    speechEvents.on('volume_change', function (volume, treshold) {
                      //console.log('volume', volume, treshold);
                        if (volume < -60) { // vary between -60 and -35
                            $('#ownvolume').css('width', 0);
                        } else if (volume > -35) {
                            $('#ownvolume').css('width', '100%');
                        } else {
                            $('#ownvolume').css('width', (volume + 100) * 100 / 25 - 160 + '%');
                        }
                    });
                } else {
                    console.warn('without hark, you are missing quite a nice feature');
                }
            }

            $rootScope.$on('xmppMucConnectedEvent', function (event) {
//                var connection = XmppService.getConnection();
//                var roomjid = XmppMucService.getRoom();
//                var myroomjid = XmppMucService.getRoom() + '/' + XmppService.getUsername();
                RTC = setupRTC();
                getUserMediaWithConstraints(['audio', 'video']);
                $(document).bind('mediaready.jingle', onMediaReady);
            });
            
        }
    ]);
})();
 