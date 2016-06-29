/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

export default class VideoService {
  constructor($http,$sce, ChatRoomService, UserService) {
    this.$http = $http
    this.$sce = $sce
    this.ChatRoomService = ChatRoomService
    this.UserService = UserService
    this.chatRoomConfig = this.ChatRoomService.getConfig()
    this.xmppConfig = this.ChatRoomService.getXmppConfig()
    this.videoConfig = {
      ice_config: {
        iceServers: [{urls: ['stun:stun.l.google.com:19302']}]
      },
      AUTOACCEPT: true,
      PRANSWER: false, // use either pranswer or autoaccept
      //connection: null,
      roomjid: null,
      myUsername: null,
      users: this.UserService.getUsers(),
      sids: {},
      sourceStreams: {},
      lastSpeakingUser: null,
      localStream: null,
      myAudioTracks: [],
      myVideoTracks: [],
      myAudioEnabled: true,
      myVideoEnabled: true,
      mySourceStrream: null,
      mainStreamUsername: null,
      mainStreamIndex: 0
    }
    this.myAudioTracks = []
    this.myVideoTracks = []
    this.switchCamera = this.switchCamera.bind(this)
    this._onMediaReady = this._onMediaReady.bind(this)
    this._onMediaFailure = this._onMediaFailure.bind(this)
    this._onCallIncoming = this._onCallIncoming.bind(this)
    //this._onCallActive = this._onCallActive.bind(this)
    this._onCallTerminated = this._onCallTerminated.bind(this)
    this._onRemoteStreamAdded = this._onRemoteStreamAdded.bind(this)
    this._onRemoteStreamRemoved = this._onRemoteStreamRemoved.bind(this)
    this._onIceConnectionStateChanged = this._onIceConnectionStateChanged.bind(this)
    this._noStunCandidates = this._noStunCandidates.bind(this)
    this._waitForRemoteVideo = this._waitForRemoteVideo.bind(this)
    this.ChatRoomService.setConnectedCallBack(this.switchCamera)
  }

  getVideoConfig () {
    return this.videoConfig
  }

  switchCamera () {
    console.log('activating camera...')
    RTC = setupRTC()
    getUserMediaWithConstraints(['audio', 'video'])
    this.xmppConfig['connection'].jingle.ice_config = this.videoConfig['ice_config']
    angular.element(document).bind('mediaready.jingle', this._onMediaReady)
    angular.element(document).bind('mediafailure.jingle', this._onMediaFailure)
    angular.element(document).bind('callincoming.jingle', this._onCallIncoming)
    //angular.element(document).bind('callactive.jingle', this._onCallActive)
    angular.element(document).bind('callterminated.jingle', this._onCallTerminated)
    angular.element(document).bind('remotestreamadded.jingle', this._onRemoteStreamAdded)
    angular.element(document).bind('remotestreamremoved.jingle', this._onRemoteStreamRemoved)
    angular.element(document).bind('iceconnectionstatechange.jingle', this._onIceConnectionStateChanged)
    angular.element(document).bind('nostuncandidates.jingle', this._noStunCandidates)
    angular.element(document).bind('ack.jingle', function (event, sid, ack) {
      console.log('got stanza ack for ' + sid, ack)
    })
    angular.element(document).bind('error.jingle', function (event, sid, err) {
      console.log('got stanza error for ' + sid, err)
    })
    angular.element(document).bind('packetloss.jingle', function (event, sid, loss) {
      console.warn('packetloss', sid, loss)
    })

    if (RTC) {
      RTCPeerconnection = RTC.peerconnection
      this.xmppConfig['connection'].jingle.pc_constraints = RTC.pc_constraints

      if (RTC.browser === 'firefox') {
        //connection.jingle.media_constraints.mandatory.MozDontOfferDataChannel = true;
        this.xmppConfig['connection'].jingle.media_constraints = {
          offerToReceiveAudio: true,
          offerToReceiveVideo: true,
          mozDontOfferDataChannel: true
        }
      }
    } else {
      console.log('webrtc capable browser required')
    }
  }

  switchVideo () {
    //const streamURL = window.URL.createObjectURL(this.videoConfig['users'][index]['sourceStream'])

    if (this.videoConfig['myVideoEnabled']) {
      this.videoConfig['myVideoTracks'].forEach(t => {
        this.videoConfig['localStream'].removeTrack(t)
      })
      this.videoConfig['myVideoEnabled'] = false
    } else {
      this.videoConfig['myVideoTracks'].forEach(t => {
        this.videoConfig['localStream'].addTrack(t)
      })
      this.videoConfig['myVideoEnabled'] = true
    }
    const streamURL = window.URL.createObjectURL(this.videoConfig['localStream'])
    const trustedStreamURL = this.$sce.trustAsResourceUrl(streamURL)
    this.videoConfig['mySourceStream'] = trustedStreamURL
    this.videoConfig['sourceStreams'][this.chatRoomConfig['myUsername']] = trustedStreamURL
    //const index = this.UserService.getUserIndex(this.chatRoomConfig['myUsername'])

    //if (index > -1) {
      //this.videoConfig['users'][index]['sourceStream'] = trustedStreamURL
    //}
  }

  switchAudio () {
    if (this.videoConfig['myAudioEnabled']) {
      this.videoConfig['myAudioTracks'].forEach(t => {
        this.videoConfig['localStream'].removeTrack(t)
      })
      this.videoConfig['myAudioEnabled'] = false
    } else {
      this.videoConfig['myAudioTracks'].forEach(t => {
        this.videoConfig['localStream'].addTrack(t)
      })
      this.videoConfig['myAudioEnabled'] = true
    }
    const streamURL = window.URL.createObjectURL(this.videoConfig['localStream'])
    const trustedStreamURL = this.$sce.trustAsResourceUrl(streamURL)
    this.videoConfig['mySourceStream'] = trustedStreamURL
    this.videoConfig['sourceStreams'][this.chatRoomConfig['myUsername']] = trustedStreamURL
    //const index = this.UserService.getUserIndex(this.chatRoomConfig['myUsername'])

    //if (index > -1) {
    //  this.videoConfig['users'][index]['sourceStream'] = trustedStreamURL
    //}
  }

  selectSourceStream (username) {
    this.videoConfig['mainStreamUsername'] = username
    //const index = this.UserService.getUserIndex(username)
    //
    //if (index > -1) {
    //  console.log(`${username} : ${index}`)
    //  this.videoConfig['mainStreamUsername'] = username
    //  this.videoConfig['mainStreamIndex'] = index
    //}
  }

  initiateCalls () {
    console.log('Initiating calls...')
    this.videoConfig['users'].forEach(u => {
      if (u['username'] !== this.chatRoomConfig['myUsername']) {
        console.log(`${this.chatRoomConfig['room']}/${u['username']}`)
        console.log(`${this.chatRoomConfig['room']}/${this.chatRoomConfig['myUsername']}`)

        const session = this.xmppConfig['connection'].jingle.initiate(
          `${this.chatRoomConfig['room']}/${u['username']}`,
          `${this.chatRoomConfig['room']}/${this.chatRoomConfig['myUsername']}`
        )

        if (session['sid']) {
            this.addSid(session['sid'], u['username']);
        }
      }
    })
  }

  addSid (sid, username) {
    this.videoConfig['sids'][sid] = username
  }

  _onMediaReady (event, stream) {
    console.log('Media ready')
    this.videoConfig['localStream'] = stream
    this.xmppConfig['connection'].jingle.localStream = stream

    for (let i = 0; i < this.videoConfig['localStream'].getAudioTracks().length; i++) {
      console.log('using audio device "' + this.videoConfig['localStream'].getAudioTracks()[i].label + '"')
      this.videoConfig['myAudioTracks'].push(this.videoConfig['localStream'].getAudioTracks()[i])
    }
    for (let i = 0; i < this.videoConfig['localStream'].getVideoTracks().length; i++) {
      console.log('using video device "' + this.videoConfig['localStream'].getVideoTracks()[i].label + '"')
      this.videoConfig['myVideoTracks'].push(this.videoConfig['localStream'].getVideoTracks()[i])
    }
    // mute video on firefox and recent canary

    if (RTC.browser === 'firefox') {
        //$('#my-video')[0].muted = true
        //$('#my-video')[0].volume = 0
    } else {
        //document.getElementById('my-video').muted = true
        //document.getElementById('main-video').muted = true
    }
    const streamURL = window.URL.createObjectURL(this.videoConfig['localStream'])
    const trustedStreamURL = this.$sce.trustAsResourceUrl(streamURL)
    this.videoConfig['mySourceStream'] = trustedStreamURL
    this.videoConfig['sourceStreams'][this.chatRoomConfig['myUsername']] = trustedStreamURL
    this.videoConfig['mainStreamUsername'] = this.chatRoomConfig['myUsername']
    //const index = this.UserService.getUserIndex(this.chatRoomConfig['myUsername'])

    //if (index > -1) {
    //  this.videoConfig['users'][index]['sourceStream'] = trustedStreamURL
      //RTC.attachMediaStream(angular.element(document).find('#my-video'), this.videoConfig['localStream'])
      //updateMainVideoDisplay();
    //}
    this.initiateCalls()
    this.ChatRoomService.refreshScope()
  }

  _onMediaFailure () {
    console.log('Media failure')
  }

  _onCallIncoming (event, sid) {
    console.log(`Incoming call : ${sid}`)
    const sess = this.xmppConfig['connection'].jingle.sessions[sid]
    const initiator = Strophe.getResourceFromJid(sess['initiator'])
    this.addSid(sid, initiator)
    sess.sendAnswer();
    sess.accept();
  }

  //_onCallActive (event, videoelem, sid) {
  //  console.log('*********** Call active *******************')
  //}

  _onCallTerminated (event, sid, reason) {
    console.log('Call terminated')
  }

  _onRemoteStreamAdded (event, data, sid) {
    console.log(`Remote stream for session ${sid} added.`)
    //console.log(data)

    //if ($('#participant-video-' + sid).length !== 0) {
    //    console.log('ignoring duplicate onRemoteStreamAdded...'); // FF 20
    //
    //    return;
    //}
    // after remote stream has been added, wait for ice to become connected
    // old code for compat with FF22 beta
    //const el = $('<video autoplay="autoplay" class="participant-video"/>').attr('id', 'participant-video-' + sid);
    //const el = angular.element(document).find('.other-stream')
    //RTC.attachMediaStream(el, data.stream)
    //this._waitForRemoteVideo(el, sid)
    this._waitForRemoteVideo(sid)


    /* does not yet work for remote streams -- https://code.google.com/p/webrtc/issues/detail?id=861
    var options = { interval:500 };
    var speechEvents = hark(data.stream, options);

    speechEvents.on('volume_change', function (volume, treshold) {
      console.log('volume for ' + sid, volume, treshold);
    });
    */
  }

  _onRemoteStreamRemoved (event, data, sid) {
    console.log(`Remote stream for session ${sid} removed.`)
  }

  _onIceConnectionStateChanged (event, sid, sess) {
    console.log('_onIceConnectionStateChanged')
    console.log('ice state for', sid, sess.peerconnection.iceConnectionState);
    console.log('sig state for', sid, sess.peerconnection.signalingState);
    console.log(sess['initiator']);

    if (sess.peerconnection.iceConnectionState === 'connected') {
      console.log('add new stream');
    } else if (sess.peerconnection.iceConnectionState === 'disconnected') {
      //connection.jingle.sessions[sid].terminate('disconnected');
      console.log('remove stream');
      //$scope.removeStream(sid);
      //manageDisconnectedSid(sid);
    } else if (sess.peerconnection.iceConnectionState === 'failed' || sess.peerconnection.iceConnectionState === 'closed') {
      console.log('failed/closed stream');
      //$scope.removeStream(sid);
      //manageDisconnectedSid(sid);
    }
  }

  _noStunCandidates (event) {
    console.log('webrtc did not encounter stun candidates, NAT traversal will not work')
    console.warn('webrtc did not encounter stun candidates, NAT traversal will not work')
  }

  _waitForRemoteVideo(sid) {
    console.log('*********** Waiting for remote video... *******************')
    const sess = this.xmppConfig['connection'].jingle.sessions[sid];
    const videoTracks = sess.remoteStream.getVideoTracks()
    //const initiator = Strophe.getResourceFromJid(sess['initiator'])

    if (videoTracks.length > 0 && this.videoConfig['sids'][sid]) {
      //angular.element(document).trigger('callactive.jingle', [null, sid])
      const streamURL = window.URL.createObjectURL(sess.remoteStream)
      const trustedStreamURL = this.$sce.trustAsResourceUrl(streamURL)
      this.videoConfig['sourceStreams'][this.videoConfig['sids'][sid]] = trustedStreamURL
      this.ChatRoomService.refreshScope()
      //const index = this.UserService.getUserIndex(initiator)

      //if (index > -1) {
      //  this.videoConfig['users'][index]['sourceStream'] = trustedStreamURL
      //  this.ChatRoomService.refreshScope()
        //console.log('##########################')
        //console.log(trustedStreamURL)
        //RTC.attachMediaStream(angular.element(document).find('#my-video'), this.videoConfig['localStream'])
        //updateMainVideoDisplay();
      //}
      //RTC.attachMediaStream(selector, sess.remoteStream); // FIXME: why do i have to do this for FF?
      //console.log('waitForremotevideo', sess.peerconnection.iceConnectionState, sess.peerconnection.signalingState)
    } else {
      setTimeout(() => {this._waitForRemoteVideo(sid)}, 500)
    }
  }
}