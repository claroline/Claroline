/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

var RTC = null;
var ice_config = {
    iceServers: [
//        {url: 'stun:23.21.150.121'},
        {urls: 'stun:stun.l.google.com:19302'}
    ]
};
var RTCPeerconnection = null;
var AUTOACCEPT = true;
var PRANSWER = false; // use either pranswer or autoaccept
//var RAWLOGGING = true;
//var MULTIPARTY = true;
//var localStream = null;
var connection = null;
//var myroomjid = null;
var roomjid = null;
var myUsername = null;
var users = {};
var sids = {};
//var videoTracks = null;
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
            $scope.streams = [];
            $scope.currentVideoId = null;

            function checkStream()
            {
                updateUsers();
                checkUsersStatus();
                initiateCalls();
//                console.log(users);
            }

            function updateUsers()
            {
                updatePresentUsers();
                updateNewUsers('present');
            }
            
            // status = ['toCall', 'waiting', 'present', 'pending', 'working']
            function updateNewUsers(status)
            {
                var allUsers = XmppMucService.getUsers();
                
                for (var i = 0; i < allUsers.length; i++) {
                    var username = allUsers[i]['username'];
                    
                    if (username !== myUsername) {
                        
                        if (users[username] === undefined || users[username] === null) {
                            
                            users[username] = {username: username, sid: null, status: status, iteration: 0};
                        }
                    }
                }
            }
            
            function updatePresentUsers()
            {
                for (var username in users) {
//                    
                    if (users[username] !== null && !XmppMucService.hasUser(username)) {
                        users[username] = null;
                    }
                }
            }
            
            function checkUsersStatus()
            {
                for (var username in users) {
//                    
                    if (users[username] !== null) {
                        
                        if (users[username]['status'] !== 'toCall' &&
                            users[username]['sid'] === null && 
                            users[username]['iteration'] > 5) {
                        
                            users[username]['status'] = 'toCall';
                            users[username]['iteration'] = 0;
                        } else {
                            users[username]['iteration']++;
                        }
                    }
                }
            }
            
            function initiateCalls()
            {
                for (var username in users) {
                    
                    if (users[username] !== null && users[username]['sid'] === null && users[username]['status'] === 'toCall') {
                        
                        var session = connection.jingle.initiate(
                            roomjid + '/' + username,
                            roomjid + '/' + myUsername

                        );
                        
                        if (session['sid']) {
                            users[username]['sid'] = session['sid'];
                            users[username]['status'] = 'pending';
                            users[username]['iteration'] = 0;
                            addSid(session['sid'], username);
//                            $scope.addStream(session['sid'], username);
                        }
                    }
                }
            }
            
            function addSid(sid, username)
            {
                sids[sid] = {username: username, status: 'new'}
//                var isPresent = false;
//                
//                for (var i = 0; i < sids.length; i++) {
//                    
//                    if (sids[i]['sid'] === sid) {
//                        isPresent = true;
//                        break;
//                    }
//                }
//                
//                if (!isPresent) {
//                    sids.push({sid: sid, username: username, status: 'new'});
//                }
//                
//                return !isPresent;
            }
            
            function removeSid(sid)
            {
                sids[sid] = null;
//                for (var i = 0; i < sids.length; i++) {
//                    
//                    if (sids[i]['sid'] === sid) {
//                        sids.splice(i, 1);
//                        
//                        return true;
//                    }
//                }
//                
//                return false;
            }
            
            function manageDisconnectedSid(sid)
            {
                var username = sids[sid]['username'];
                
                if (users[username] !== undefined && 
                    users[username] !== null && 
                    users[username]['sid'] === sid) {
                
                    users[username]['sid'] = null;
                    users[username]['status'] = 'waiting';
                    users[username]['iteration'] = 0;
                }
            }
            
            function checkSidAndAddStream(sid)
            {
                
            }

            function onMediaReady(event, stream)
            {
                $scope.localStream = stream;
                connection.jingle.localStream = stream;
                for (var i = 0; i < $scope.localStream.getAudioTracks().length; i++) {
                    console.log('using audio device "' + $scope.localStream.getAudioTracks()[i].label + '"');
                }
                for (i = 0; i < $scope.localStream.getVideoTracks().length; i++) {
                    console.log('using video device "' + $scope.localStream.getVideoTracks()[i].label + '"');
                }
                // mute video on firefox and recent canary
                $('#my-video')[0].muted = true;
                $('#my-video')[0].volume = 0;

                RTC.attachMediaStream($('#my-video'), $scope.localStream);
                $scope.updateMainVideoSrc('my-video');

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

            function onMediaFailure()
            {
                console.log('Media failure');
            }

            function onCallIncoming(event, sid)
            {
                console.log('Incoming call : ' + sid);
                var sess = connection.jingle.sessions[sid];
                var initiator = Strophe.getResourceFromJid(sess['initiator']);
                sess.sendAnswer();
                sess.accept();
                addSid(sid, initiator);
//                $scope.addStream(sid, initiator);
                
//                if (!$scope.hasStreamFromUsername(initiator)) {
//                    console.log('**************************');
//                    console.log('No stream from ' + initiator);
//                                                                            
//                    var session = connection.jingle.initiate(
//                        roomjid + '/' + initiator,
//                        roomjid + '/' + myUsername
//                    );
//                    
//                    if (session['sid']) {
//                        $scope.addStream(session['sid'], initiator);
//                        console.log('Stream with ' + initiator + '_2 : ' + session['sid']);
//                    }
//                }

                // alternatively...
                //sess.terminate(busy)
                //connection.jingle.terminate(sid);
            }
            
            function onCallActive(event, videoelem, sid)
            {
                console.log('+++++++++++ CALL ACTIVE : ' + sid + ' +++++++++++');
//                var sess = connection.jingle.sessions[sid];
//                var initiator = Strophe.getResourceFromJid(sess['initiator']);
                var username = sids[sid]['username'];

                if (users[username] === undefined ||
                    users[username] === null || 
                    users[username]['sid'] !== sid || 
                    users[username]['status'] !== 'working') {
                
                    if (users[username] !== undefined &&
                        users[username] !== null && 
                        users[username]['sid']) {
                    
                        $scope.removeStream(users[username]['sid']);
                    }
                    users[username]['sid'] = sid;
                    users[username]['status'] = 'working';
                    users[username]['iteration'] = 0;
                    $scope.addStream(sid, sids[sid]['username']);
    //                videoelem[0].style.display = 'inline-block';
                    $(videoelem).appendTo('#participant-stream-' + sid + ' .participant-video-panel');
                    connection.jingle.sessions[sid].getStats(1000);
                }
            }

            function onCallTerminated(event, sid, reason)
            {
                console.log('Call terminated ' + sid + (reason ? (': ' + reason) : ''));
                
                if (Object.keys(connection.jingle.sessions).length === 0) {
                    console.log('All calls terminated');
                }
                $('#participants-video-container #participant-video-' + sid).remove();
            }
            
            function waitForRemoteVideo(selector, sid)
            {
                var sess = connection.jingle.sessions[sid];
                var videoTracks = sess.remoteStream.getVideoTracks();
                if (videoTracks.length === 0 || selector[0].currentTime > 0) {
                    $(document).trigger('callactive.jingle', [selector, sid]);
                    RTC.attachMediaStream(selector, sess.remoteStream); // FIXME: why do i have to do this for FF?
                    console.log('waitForremotevideo', sess.peerconnection.iceConnectionState, sess.peerconnection.signalingState);
                } else {
                    setTimeout(function () { waitForRemoteVideo(selector, sid); }, 100);
                }
            }

            function onRemoteStreamAdded(event, data, sid)
            {
                console.log('Remote stream for session ' + sid + ' added.');
                
                if ($('#participant-video-' + sid).length !== 0) {
                    console.log('ignoring duplicate onRemoteStreamAdded...'); // FF 20
                    
                    return;
                }
                // after remote stream has been added, wait for ice to become connected
                // old code for compat with FF22 beta
                var el = $('<video autoplay="autoplay" class="participant-video"/>').attr('id', 'participant-video-' + sid);
                RTC.attachMediaStream(el, data.stream);
                waitForRemoteVideo(el, sid);
                /* does not yet work for remote streams -- https://code.google.com/p/webrtc/issues/detail?id=861
                var options = { interval:500 };
                var speechEvents = hark(data.stream, options);

                speechEvents.on('volume_change', function (volume, treshold) {
                  console.log('volume for ' + sid, volume, treshold);
                });
                */
            }

            function onRemoteStreamRemoved(event, data, sid)
            {
                console.log('Remote stream for session ' + sid + ' removed.');
            }

            function onIceConnectionStateChanged(event, sid, sess)
            {
                console.log('ice state for', sid, sess.peerconnection.iceConnectionState);
//                console.log('sig state for', sid, sess.peerconnection.signalingState);
                console.log(sess['initiator']);
                
                if (sess.peerconnection.iceConnectionState === 'connected') {
                    console.log('add new stream');
                    var initiator = Strophe.getResourceFromJid(sess['initiator']);
//                    $scope.addStream(sid, initiator);
                } else if (sess.peerconnection.iceConnectionState === 'disconnected') {
                    connection.jingle.sessions[sid].terminate('disconnected');
                    console.log('remove stream');
                    $scope.removeStream(sid);
                    manageDisconnectedSid(sid);
                } else if (sess.peerconnection.iceConnectionState === 'failed' || sess.peerconnection.iceConnectionState === 'closed') {
                    $scope.removeStream(sid);
                    manageDisconnectedSid(sid);
//                    var username = $scope.getUsernameFromSid(sid);
//                    
//                    if (username !== null) {                                                    
//                        var session = connection.jingle.initiate(
//                            roomjid + '/' + username,
//                            roomjid + '/' + myUsername
//                        );
//
//                        if (session['sid']) {
//                            $scope.removeStream(sid);
//                            $scope.addStream(session['sid'], username);
//                            console.log('Stream with ' + username + '_failed : ' + session['sid']);
//                        }
//                    }
                }
                
                // works like charm, unfortunately only in chrome and FF nightly, not FF22 beta
//                
//                if (sess.peerconnection.signalingState === 'stable' && sess.peerconnection.iceConnectionState === 'connected') {
//                    var el = $('<video autoplay="autoplay" class="participant-video"/>').attr('id', 'participant-video-' + sid);
//                    $(document).trigger('callactive.jingle', [el, sid]);
//                    RTC.attachMediaStream(el, sess.remoteStream); // moving this before the trigger doesn't work in FF?!
//                }              
            }

            function noStunCandidates(event)
            {
                console.log('webrtc did not encounter stun candidates, NAT traversal will not work');
                console.warn('webrtc did not encounter stun candidates, NAT traversal will not work');
            }

            $rootScope.$on('xmppMucConnectedEvent', function (event) {
                XmppMucService.setConnected(true);
                connection = XmppService.getConnection();
                roomjid = XmppMucService.getRoom();
                myUsername = XmppService.getUsername();
                console.log('MUC CONNECT');
                
//                connection.jingle.getStunAndTurnCredentials();
                RTC = setupRTC();
                getUserMediaWithConstraints(['audio', 'video']);
                connection.jingle.ice_config = ice_config;
                
                if (RTC) {
                    connection.jingle.pc_constraints = RTC.pc_constraints;
                }
                
                $(document).bind('mediaready.jingle', onMediaReady);
                $(document).bind('mediafailure.jingle', onMediaFailure);
                $(document).bind('callincoming.jingle', onCallIncoming);
                $(document).bind('callactive.jingle', onCallActive);
                $(document).bind('callterminated.jingle', onCallTerminated);

                $(document).bind('remotestreamadded.jingle', onRemoteStreamAdded);
                $(document).bind('remotestreamremoved.jingle', onRemoteStreamRemoved);
                $(document).bind('iceconnectionstatechange.jingle', onIceConnectionStateChanged);
                $(document).bind('nostuncandidates.jingle', noStunCandidates);
                $(document).bind('ack.jingle', function (event, sid, ack) {
                    console.log('got stanza ack for ' + sid, ack);
                });
                $(document).bind('error.jingle', function (event, sid, err) {
                    console.log('got stanza error for ' + sid, err);
                });
                $(document).bind('packetloss.jingle', function (event, sid, loss) {
//                    console.warn('packetloss', sid, loss);
                });
                
                if (RTC !== null) {
                    RTCPeerconnection = RTC.peerconnection;
                    
                    if (RTC.browser == 'firefox') {
                        //connection.jingle.media_constraints.mandatory.MozDontOfferDataChannel = true;
                        connection.jingle.media_constraints = {"offerToReceiveAudio":true,"offerToReceiveVideo":true,"mozDontOfferDataChannel":true}
                    }
                    //setStatus('please allow access to microphone and camera');
                    //getUserMediaWithConstraints();
                } else {
                    console.log('webrtc capable browser required');
                }
            });
            
            $rootScope.$on('myPresenceConfirmationEvent', function () {
                updateNewUsers('toCall');
                initiateCalls();
                setInterval(checkStream, 5000);
//                var allUsers = XmppMucService.getUsers();
//                
//                for (var i = 0; i < allUsers.length; i++) {
//                    
//                    if (myUsername && allUsers[i]['username'] !== myUsername) {
//                        var session = connection.jingle.initiate(
//                            roomjid + '/' + allUsers[i]['username'],
//                            roomjid + '/' + myUsername
//
//                        );
//                        
//                        if (session['sid']) {
////                            $scope.addStream(session['sid'], allUsers[i]['username']);
////                            console.log('Stream with ' + allUsers[i]['username'] + ' : ' + session['sid']);
//                        }
//                    }
//                }
            });

            $scope.$on('userDisconnectionEvent', function (event, userDatas) {
                $scope.removeUserStream(userDatas['username']);
            });
            
            $scope.getUsernameFromSid = function (sid) {
                var username = null;
                
                for (var i = 0; i < $scope.streams.length; i++) {
                    
                    if ($scope.streams[i]['sid'] === sid) {
                        username = $scope.streams[i]['username'];
                        break;
                    }
                }
                
                return username;
            };

            $scope.removeUserStream = function (username) {
                
                for (var i = $scope.streams.length - 1; i >= 0; i--) {
                    
                    if ($scope.streams[i]['username'] === username) {
                        $scope.streams.splice(i, 1);
                    }
                }
                $scope.$apply();
            };

            $scope.hasStreamFromUsername = function (username) {
                var isPresent = false;

                for (var i = 0; i < $scope.streams.length; i++) {
                                                                                
                    if ($scope.streams[i]['username'] === username) {
                        isPresent = true;
                        break;
                    }
                }

                return isPresent;
            };

            $scope.addStream = function (sid, username) {
                var isPresent = false;
                
                for (var i = 0; i < $scope.streams.length; i++) {
                    
                    if ($scope.streams[i]['sid'] === sid) {
                        isPresent = true;
                        break;
                    }
                }
                
                if (!isPresent) {
                    $scope.streams.push({sid: sid, username: username});
                    $scope.$apply();
                }
            };
            
            $scope.removeStream = function (sid) {
                
                for (var i = 0; i < $scope.streams.length; i++) {
                    
                    if ($scope.streams[i]['sid'] === sid) {
                        $scope.streams.splice(i, 1);
                        $scope.$apply();
                        break;
                    }
                }
            };
            
            $scope.disconnect = function () {
                XmppMucService.disconnect();
            };
            
            $scope.updateMainVideoSrc = function (videoId) {
                var element = document.getElementById(videoId);
                var mainVideo = document.getElementById('main-video');
                mainVideo.src = element.src;
                mainVideo.mozSrcObject = element.mozSrcObject;
                
                if ($scope.currentVideoId !== null) {
                    $('#' + $scope.currentVideoId).closest('.participant-panel').removeClass('video-selected');
                }
                $scope.currentVideoId = videoId;
                $('#' + videoId).closest('.participant-panel').addClass('video-selected');
            };
        }
    ]);
})();
 
