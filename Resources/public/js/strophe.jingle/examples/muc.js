/* jshint -W117 */
var BOSH_SERVICE = '/http-bind',
    DOMAIN = window.location.hostname,
    CONFERENCEDOMAIN = 'conference.' + DOMAIN,
    ice_config = {iceServers: [{url: 'stun:stun.l.google.com:19302'}]},
    RTC = null,
    RTCPeerConnection = null,
    AUTOACCEPT = true,
    PRANSWER = false, // use either pranswer or autoaccept
    RAWLOGGING = true,
    MULTIPARTY = true,
    localStream = null,
    connection = null,
    myroomjid = null,
    roomjid = null,
    list_members = [];

function setStatus(txt) {
    console.log('status', txt);
    $('#status').text(txt);
}

function onConnect(status) {
    if (status == Strophe.Status.CONNECTING) {
        setStatus('Connecting.');
    } else if (status == Strophe.Status.CONNFAIL) {
        setStatus('Connecting failed.');
    } else if (status == Strophe.Status.DISCONNECTING) {
        setStatus('Disconnecting.');
    } else if (status == Strophe.Status.DISCONNECTED) {
        setStatus('Disconnected.');
        if (localStream) {
            localStream.stop();
            localStream = null;
        }
    } else if (status == Strophe.Status.CONNECTED) {
        setStatus('Connected.');
        connection.jingle.getStunAndTurnCredentials();

        // disco stuff
        if (connection.disco) {
            connection.disco.addIdentity('client', 'web');
            connection.disco.addFeature(Strophe.NS.DISCO_INFO);
        }
        $(document).trigger('connected');
    }
}

function onHashChange() {
    setStatus('hashChange: ' + window.location.hash);
    if (Object.keys(connection.jingle.sessions).length === 0) {
        window.location.reload();
    }
}

function onJoinComplete() {
    setStatus('onJoinComplete');
    if (list_members.length < 1) {
        setStatus('waiting for peer');
        return;
    }

    setStatus('initiating call');
    var i, sess, num;
    num = MULTIPARTY ? list_members.length : 1;
    for (i = 0; i < num; i++) {
        connection.jingle.initiate(list_members[i], myroomjid);
    }
}

function onPresence(pres) {
    var from = pres.getAttribute('from'),
        type = pres.getAttribute('type');
    if (type !== null) {
        return true;
    }
    if ($(pres).find('>x[xmlns="http://jabber.org/protocol/muc#user"]>status[code="201"]').length) {
        // http://xmpp.org/extensions/xep-0045.html#createroom-instant
        var create = $iq({type: 'set', to: roomjid})
                .c('query', {xmlns: 'http://jabber.org/protocol/muc#owner'})
                .c('x', {xmlns: 'jabber:x:data', type: 'submit'});
        connection.send(create); // fire away
    }
    if (from == myroomjid) {
        onJoinComplete();
    } else { // TODO: prevent duplicates
        list_members.push(from);
    }
    return true;
}

function onPresenceUnavailable(pres) {
    connection.jingle.terminateByJid($(pres).attr('from'));
    if (Object.keys(connection.jingle.sessions).length === 0) {
        setStatus('everyone left');
    }
    for (var i = 0; i < list_members.length; i++) {
        if (list_members[i] == $(pres).attr('from')) {
            list_members.splice(i, 1);
            break;
        }
    }
    return true;
}

function onPresenceError(pres) {
    setStatus('onPresError ' + pres);
    return true;
}

function doJoin() {
    var roomnode = null,
        pres;
    if (location.hash.length > 1) {
        roomnode = location.hash.substr(1).toLowerCase();
        if (roomnode.indexOf('/') != -1) {
            setStatus('invalid location, must not contain "/"');
            connection.disconnect();
            return;
        }
        if (roomnode.indexOf('@') != -1) { // allow #room@host
            roomjid = roomnode;
        }
    } else {
        roomnode = Math.random().toString(36).substr(2, 8);
        location.hash = roomnode;
    }
    if (roomjid === null) {
        roomjid = roomnode + '@' + CONFERENCEDOMAIN;
    }
    setStatus('Joining ' + location.hash);
    myroomjid = roomjid + '/' + Strophe.getNodeFromJid(connection.jid);
    list_members = [];
    console.log('joining', roomjid);

    // muc stuff
    connection.addHandler(onPresence, null, 'presence', null, null, roomjid, {matchBare: true});
    connection.addHandler(onPresenceUnavailable, null, 'presence', 'unavailable', null, roomjid, {matchBare: true});
    connection.addHandler(onPresenceError, null, 'presence', 'error', null, roomjid, {matchBare: true});

    pres = $pres({to: myroomjid })
            .c('x', {xmlns: 'http://jabber.org/protocol/muc'});
    connection.send(pres);
}

function onMediaReady(event, stream) {
    localStream = stream;
    connection.jingle.localStream = stream;
    for (var i = 0; i < localStream.getAudioTracks().length; i++) {
        setStatus('using audio device "' + localStream.getAudioTracks()[i].label + '"');
    }
    for (i = 0; i < localStream.getVideoTracks().length; i++) {
        setStatus('using video device "' + localStream.getVideoTracks()[i].label + '"');
    }
    // mute video on firefox and recent canary
    $('#minivideo')[0].muted = true;
    $('#minivideo')[0].volume = 0;

    RTC.attachMediaStream($('#minivideo'), localStream);

    doConnect();

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

function onMediaFailure() {
    setStatus('media failure');
}

function onCallIncoming(event, sid) {
    setStatus('incoming call' + sid);
    var sess = connection.jingle.sessions[sid];
    sess.sendAnswer();
    sess.accept();

    // alternatively...
    //sess.terminate(busy)
    //connection.jingle.terminate(sid);
}

function arrangeVideos(selector) {
    var floor = Math.floor,
        elements = $(selector),
        howMany = elements.length,
        availableWidth = $(selector).parent().innerWidth(),
        availableHeight = $(selector).parent().innerHeight(),
        usedWidth = 0,
        aspectRatio = 4 / 3;
    if (availableHeight < availableWidth / aspectRatio) {
        availableWidth = availableHeight * aspectRatio;
    }
    elements.height(availableHeight);

    elements.each(function (index) {
        $(elements[index]).removeAttr('style');
    });

    // hardcoded layout for up to four videos
    switch (howMany) {
    case 1:
        usedWidth = availableWidth;
        $(elements[0]).css('top', 0);
        $(elements[0]).css('left', ($(selector).parent().innerWidth() - availableWidth) / 2);
        break;
    case 2:
        usedWidth = availableWidth / 2;
        $(elements[0]).css({ left: '0px', top: '0px'});
        $(elements[1]).css({ right: '0px', bottom: '0px'});
        break;
    case 3:
        usedWidth = availableWidth / 2;
        $(elements[0]).css({ left: '0px', top: '0px'});
        $(elements[1]).css({ right: '0px', top: '0px'});
        $(elements[2]).css({ left: ($(selector).parent().innerWidth() - availableWidth + usedWidth) / 2, bottom: '0px' });
        break;
    case 4:
        usedWidth = availableWidth / 2;
        $(elements[0]).css({ left: '0px', top: '0px'});
        $(elements[0]).css('left', ($(selector).parent().innerWidth() - availableWidth) / 2);
        $(elements[1]).css({ right: '0px', top: '0px'});
        $(elements[1]).css('right', ($(selector).parent().innerWidth() - availableWidth) / 2);
        $(elements[2]).css({ left: '0px', bottom: '0px'});
        $(elements[2]).css('left', ($(selector).parent().innerWidth() - availableWidth) / 2);
        $(elements[3]).css({ right: '0px', bottom: '0px'});
        $(elements[3]).css('right', ($(selector).parent().innerWidth() - availableWidth) / 2);
        break;
    }
    elements.each(function (index) {
        $(elements[index]).css({
            position: 'absolute',
            width: usedWidth,
            height: usedWidth / aspectRatio
        });
        $(elements[index]).show();
    });
}


function onCallActive(event, videoelem, sid) {
    setStatus('call active ' + sid);
    $(videoelem).appendTo('#largevideocontainer');
    arrangeVideos('#largevideocontainer >');
    connection.jingle.sessions[sid].getStats(1000);
}

function onCallTerminated(event, sid, reason) {
    setStatus('call terminated ' + sid + (reason ? (': ' + reason) : ''));
    if (Object.keys(connection.jingle.sessions).length === 0) {
        setStatus('all calls terminated');
    }
    $('#largevideocontainer #largevideo_' + sid).remove();
    arrangeVideos('#largevideocontainer >');
}

function waitForRemoteVideo(selector, sid) {
    var sess = connection.jingle.sessions[sid];
    videoTracks = sess.remoteStream.getVideoTracks();
    if (videoTracks.length === 0 || selector[0].currentTime > 0) {
        $(document).trigger('callactive.jingle', [selector, sid]);
        RTC.attachMediaStream(selector, sess.remoteStream); // FIXME: why do i have to do this for FF?
        console.log('waitForremotevideo', sess.peerconnection.iceConnectionState, sess.peerconnection.signalingState);
    } else {
        setTimeout(function () { waitForRemoteVideo(selector, sid); }, 100);
    }
}

function onRemoteStreamAdded(event, data, sid) {
    setStatus('Remote stream for session ' + sid + ' added.');
    if ($('#largevideo_' + sid).length !== 0) {
        console.log('ignoring duplicate onRemoteStreamAdded...'); // FF 20
        return;
    }
    // after remote stream has been added, wait for ice to become connected
    // old code for compat with FF22 beta
    var el = $("<video autoplay='autoplay' style='display:none'/>").attr('id', 'largevideo_' + sid);
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

function onRemoteStreamRemoved(event, data, sid) {
    setStatus('Remote stream for session ' + sid + ' removed.');
}

function onIceConnectionStateChanged(event, sid, sess) {
    console.log('ice state for', sid, sess.peerconnection.iceConnectionState);
    console.log('sig state for', sid, sess.peerconnection.signalingState);
    // works like charm, unfortunately only in chrome and FF nightly, not FF22 beta
    /*
    if (sess.peerconnection.signalingState == 'stable' && sess.peerconnection.iceConnectionState == 'connected') {
        var el = $("<video autoplay='autoplay' style='display:none'/>").attr('id', 'largevideo_' + sid);
        $(document).trigger('callactive.jingle', [el, sid]);
        RTC.attachMediaStream(el, sess.remoteStream); // moving this before the trigger doesn't work in FF?!
    }
    */
}

function noStunCandidates(event) {
    setStatus('webrtc did not encounter stun candidates, NAT traversal will not work');
    console.warn('webrtc did not encounter stun candidates, NAT traversal will not work');
}

function onConnected(event) {
    doJoin();
    setTimeout(function () {
        $(window).bind('hashchange', onHashChange);
    }, 500);
}

$(window).bind('beforeunload', function () {
    if (connection && connection.connected) {
        // ensure signout
        $.ajax({
            type: 'POST',
            url: '/http-bind',
            async: false,
            cache: false,
            contentType: 'application/xml',
            data: "<body rid='" + connection.rid + "' xmlns='http://jabber.org/protocol/httpbind' sid='" + connection.sid + "' type='terminate'><presence xmlns='jabber:client' type='unavailable'/></body>",
            success: function (data) {
                console.log('signed out');
                console.log(data);
            },
            error: function (XMLHttpRequest, textStatus, errorThrown) {
                console.log('signout error', textStatus + ' (' + errorThrown + ')');
            }
        });
    }
});

$(document).ready(function () {
    RTC = setupRTC();
    connection = new Strophe.Connection(BOSH_SERVICE);
    if (RAWLOGGING) {
        connection.rawInput = function (data) { console.log('RECV: ' + data); };
        connection.rawOutput = function (data) { console.log('SEND: ' + data); };
    }
    connection.jingle.ice_config = ice_config;
    if (RTC) {
        connection.jingle.pc_constraints = RTC.pc_constraints;
    }

    $(document).bind('connected', onConnected);
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
        console.warn('packetloss', sid, loss);
    });
    if (RTC !== null) {
        RTCPeerconnection = RTC.peerconnection;
        if (RTC.browser == 'firefox') {
            connection.jingle.media_constraints.mandatory.MozDontOfferDataChannel = true;
        }
        //setStatus('please allow access to microphone and camera');
        //getUserMediaWithConstraints();
    } else {
        setStatus('webrtc capable browser required');
    }
});

