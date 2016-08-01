/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

export default class VideoService {
  constructor ($http, $sce, ChatRoomService, UserService) {
    this.$http = $http
    this.$sce = $sce
    this.ChatRoomService = ChatRoomService
    this.UserService = UserService
    this.chatRoomConfig = this.ChatRoomService.getConfig()
    this.xmppConfig = this.ChatRoomService.getXmppConfig()
    this.videoConfig = {
      ice_config: {
        iceServers: this.chatRoomConfig.iceServers
      },
      AUTOACCEPT: true,
      PRANSWER: false, // use either pranswer or autoaccept
      // connection: null,
      roomjid: null,
      myUsername: null,
      users: this.UserService.getUsers(),
      sids: {},
      sourceStreams: {},
      usersMicroStatus: {},
      lastSpeakingUser: null,
      localStream: null,
      myAudioTracks: [],
      myVideoTracks: [],
      myAudioEnabled: true,
      myVideoEnabled: true,
      mySourceStream: null,
      mainStreamUsername: null,
      selectedUser: null,
      speakingUser: null
    }
    console.log(this.videoConfig.ice_config)
    this._startMedias = this._startMedias.bind(this)
    this._stopUserStream = this._stopUserStream.bind(this)
    this._manageManagementMessage = this._manageManagementMessage.bind(this)
    this._updateMainStream = this._updateMainStream.bind(this)
    this._onMediaReady = this._onMediaReady.bind(this)
    this._onMediaFailure = this._onMediaFailure.bind(this)
    this._onCallIncoming = this._onCallIncoming.bind(this)
    this._onCallTerminated = this._onCallTerminated.bind(this)
    this._onRemoteStreamAdded = this._onRemoteStreamAdded.bind(this)
    this._onRemoteStreamRemoved = this._onRemoteStreamRemoved.bind(this)
    this._onIceConnectionStateChanged = this._onIceConnectionStateChanged.bind(this)
    this._noStunCandidates = this._noStunCandidates.bind(this)
    this._waitForRemoteVideo = this._waitForRemoteVideo.bind(this)
    this.ChatRoomService.setConnectedCallback(this._startMedias)
    this.ChatRoomService.setUserDisconnectedCallback(this._stopUserStream)
    this.ChatRoomService.setManagementCallback(this._manageManagementMessage)
    setInterval(this._updateMainStream, 1000)
  }

  getVideoConfig () {
    return this.videoConfig
  }

  switchVideo () {
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

    this.sendMicroStatus()
  }

  switchUserAudio (username = null) {
    if (username === null) {
      this.switchAudio()
    } else {
      this.resquestUserMicroSwitch(username)
    }
  }

  resquestUserMicroSwitch (username) {
    this.xmppConfig['connection'].send(
      $msg({
        to: this.chatRoomConfig['room'],
        type: 'groupchat'
      }).c('body').t('')
        .up()
        .c(
          'datas',
          {
            status: 'management',
            username: username,
            type: 'video-micro-switch'
          }
      )
    )
  }

  resquestUserMicroStatus (username) {
    this.xmppConfig['connection'].send(
      $msg({
        to: this.chatRoomConfig['room'],
        type: 'groupchat'
      }).c('body').t('')
        .up()
        .c(
          'datas',
          {
            status: 'management',
            username: username,
            type: 'video-micro-status-request'
          }
      )
    )
  }

  sendMicroStatus () {
    this.xmppConfig['connection'].send(
      $msg({
        to: this.chatRoomConfig['room'],
        type: 'groupchat'
      }).c('body').t('')
        .up()
        .c(
          'datas',
          {
            status: 'management',
            username: this.chatRoomConfig['myUsername'],
            type: 'video-micro-status',
            value: this.videoConfig['myAudioEnabled']
          }
      )
    )
  }

  sendSpeakingNotification () {
    this.xmppConfig['connection'].send(
      $msg({
        to: this.chatRoomConfig['room'],
        type: 'groupchat'
      }).c('body').t('')
        .up()
        .c(
          'datas',
          {
            status: 'management',
            username: this.chatRoomConfig['myUsername'],
            type: 'speaking',
            value: 1
          }
      )
    )
  }

  stopMedia () {
    this.videoConfig['myAudioTracks'].forEach(t => {
      t.stop()
    })
    this.videoConfig['myVideoTracks'].forEach(t => {
      t.stop()
    })
  }

  selectSourceStream (username) {
    console.log(username)
    if (this.videoConfig['selectedUser'] === username) {
      this.videoConfig['selectedUser'] = null
    } else {
      this.videoConfig['selectedUser'] = username
    }
    console.log(`Selected user: ${this.videoConfig['selectedUser']}`)
    this.videoConfig['mainStreamUsername'] = username
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
          this.addSid(session['sid'], u['username'])
        }
        this.resquestUserMicroStatus(u['username'])
      }
    })
  }

  initiateHark () {
    if (typeof hark === 'function') {
      const options = { interval: 400 }
      const speechEvents = hark(this.videoConfig['localStream'], options)

      speechEvents.on('speaking', () => {
        if (this.videoConfig['myAudioEnabled']) {
          this.sendSpeakingNotification()
        }
      })
      speechEvents.on('stopped_speaking', () => {
        if (this.videoConfig['myAudioEnabled']) {
          console.log('Stopped speaking.')
        }
      })
      speechEvents.on('volume_change', (volume, treshold) => {
        if (this.videoConfig['myAudioEnabled'] && this.videoConfig['speakingUser'] !== this.chatRoomConfig['myUsername'] && volume > -50) {
          this.sendSpeakingNotification()
        }
      })
    }
  }

  addSid (sid, username) {
    this.videoConfig['sids'][sid] = username
  }

  closeAllConnections () {
    for (let sid in this.videoConfig['sids']) {
      console.log('Try to close ' + sid)
      console.log(this.xmppConfig['connection'].jingle.sessions[sid])
      if (this.xmppConfig['connection'].jingle.sessions[sid].state === 'active') {
          this.xmppConfig['connection'].jingle.sessions[sid].terminate('Closing all connections...')
          console.log(`${sid} : closed`)
      }
    }
  }

  _updateMainStream () {
    console.log('_updateMainStream')
    if (this.videoConfig['selectedUser'] !== null) {
      if (this.videoConfig['mainStreamUsername'] !== this.videoConfig['selectedUser']) {
        this.videoConfig['mainStreamUsername'] = this.videoConfig['selectedUser']
        this.ChatRoomService.refreshScope()
        console.log(`Selected user : ${this.videoConfig['mainStreamUsername']}`)
      }
    } else if (this.videoConfig['speakingUser'] !== null) {
      if (this.videoConfig['mainStreamUsername'] !== this.videoConfig['speakingUser']) {
        this.videoConfig['mainStreamUsername'] = this.videoConfig['speakingUser']
        this.ChatRoomService.refreshScope()
        console.log(`Speaking user : ${this.videoConfig['mainStreamUsername']}`)
      }
    }
  }

  _startMedias () {
    RTC = setupRTC()
    getUserMediaWithConstraints(['audio', 'video'])
    this.xmppConfig['connection'].jingle.ice_config = this.videoConfig['ice_config']
    angular.element(document).bind('mediaready.jingle', this._onMediaReady)
    angular.element(document).bind('mediafailure.jingle', this._onMediaFailure)
    angular.element(document).bind('callincoming.jingle', this._onCallIncoming)
    // angular.element(document).bind('callactive.jingle', this._onCallActive)
    angular.element(document).bind('callterminated.jingle', this._onCallTerminated)
    angular.element(document).bind('remotestreamadded.jingle', this._onRemoteStreamAdded)
    angular.element(document).bind('remotestreamremoved.jingle', this._onRemoteStreamRemoved)
    angular.element(document).bind('iceconnectionstatechange.jingle', this._onIceConnectionStateChanged)
    angular.element(document).bind('nostuncandidates.jingle', this._noStunCandidates)
    angular.element(document).bind('ack.jingle', function (event, sid, ack) {
      console.log('got stanza ack for ' + sid, ack)
    })
    angular.element(document).bind('error.jingle', function (event, sid, err) {
      if (sid) {
        console.error('got stanza error for ' + sid, err)
      } else {
        console.error('no sid defined for', err)
      }
    })
    angular.element(document).bind('packetloss.jingle', function (event, sid, loss) {
      console.warn('packetloss', sid, loss)
    })

    if (RTC) {
      RTCPeerconnection = RTC.peerconnection
      this.xmppConfig['connection'].jingle.pc_constraints = RTC.pc_constraints

      if (RTC.browser === 'firefox') {
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

  _stopUserStream (username) {
    console.log('%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%')
    for (let sid in this.videoConfig['sids']) {
      if (this.videoConfig['sids'][sid] === username) {
        this.xmppConfig['connection'].jingle.sessions[sid].terminate('disconnected user')
        delete this.xmppConfig['connection'].jingle.sessions[sid]
        delete this.videoConfig['sids'][sid]
      }
    }
    console.log('%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%')
  }

  _manageManagementMessage (type, username, name, value) {
    if (type === 'video-micro-status') {
      if (username !== this.chatRoomConfig['myUsername']) {
        this.videoConfig['usersMicroStatus'][username] = (value == 'true')
        this.ChatRoomService.refreshScope()
      }
    } else if (type === 'video-micro-status-request') {
      if (username === this.chatRoomConfig['myUsername']) {
        console.log(`resend micro status request`)
        this.sendMicroStatus()
      }
    } else if (type === 'video-micro-switch') {
      if (username === this.chatRoomConfig['myUsername']) {
        this.switchAudio()
        this.ChatRoomService.refreshScope()
      }
    } else if (type === 'speaking') {
      console.log(`${username} is speaking...`)
      this.videoConfig['speakingUser'] = username
      this.ChatRoomService.refreshScope()
    }
  }

  _onMediaReady (event, stream) {
    console.log('Media ready')
    this.videoConfig['localStream'] = stream
    this.xmppConfig['connection'].jingle.localStream = stream
    this.videoConfig['localStream'].getAudioTracks().forEach(t => this.videoConfig['myAudioTracks'].push(t))
    this.videoConfig['localStream'].getVideoTracks().forEach(t => this.videoConfig['myVideoTracks'].push(t))

    // Mute sound in my video & main video to avoid echo
    angular.element(document).find('#my-video')[0].muted = true
    angular.element(document).find('#my-video')[0].volume = true
    angular.element(document).find('#main-video')[0].muted = true
    angular.element(document).find('#main-video')[0].volume = true

    const streamURL = window.URL.createObjectURL(this.videoConfig['localStream'])
    const trustedStreamURL = this.$sce.trustAsResourceUrl(streamURL)
    this.videoConfig['mySourceStream'] = trustedStreamURL
    this.videoConfig['sourceStreams'][this.chatRoomConfig['myUsername']] = trustedStreamURL
    this.videoConfig['mainStreamUsername'] = this.chatRoomConfig['myUsername']
    this.initiateCalls()
    this.sendMicroStatus()
    this.initiateHark()
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
    sess.sendAnswer()
    sess.accept()
  }

  _onCallTerminated (event, sid, reason) {
    console.log('Call terminated')
  }

  _onRemoteStreamAdded (event, data, sid) {
    console.log(`Remote stream for session ${sid} added.`)
    this._waitForRemoteVideo(sid)
  }

  _onRemoteStreamRemoved (event, data, sid) {
    console.log(`Remote stream for session ${sid} removed.`)
  }

  _onIceConnectionStateChanged (event, sid, sess) {
    console.log('_onIceConnectionStateChanged')
    console.log('ice state for', sid, sess.peerconnection.iceConnectionState)
    console.log('sig state for', sid, sess.peerconnection.signalingState)
    console.log(sess['initiator'])

    if (sess.peerconnection.iceConnectionState === 'connected') {
      console.log('add new stream')
    } else if (sess.peerconnection.iceConnectionState === 'disconnected') {
      this.xmppConfig['connection'].jingle.sessions[sid].terminate('disconnected')
      // connection.jingle.sessions[sid].terminate('disconnected')
      console.log('remove stream')
    // $scope.removeStream(sid)
    // manageDisconnectedSid(sid)
    } else if (sess.peerconnection.iceConnectionState === 'failed' || sess.peerconnection.iceConnectionState === 'closed') {
      console.log('failed/closed stream')
      //this._reconnect ()
    }
  }

  _reconnect () {
    console.log('reconnect')
    RTCPeerconnection = RTC.peerconnection
    RTC = setupRTC()
    console.log(RTC)
    let umc = getUserMediaWithConstraints(['audio', 'video'])
    console.log(umc)
    this.xmppConfig['connection'].jingle.pc_constraints = RTC.pc_constraints

    if (RTC.browser === 'firefox') {
      // connection.jingle.media_constraints.mandatory.MozDontOfferDataChannel = true
      this.xmppConfig['connection'].jingle.media_constraints = {
        offerToReceiveAudio: true,
        offerToReceiveVideo: true,
        mozDontOfferDataChannel: true
      }
    }
  }

  _noStunCandidates (event) {
    console.error('webrtc did not encounter stun candidates, NAT traversal will not work')
  }

  _waitForRemoteVideo (sid) {
    console.log('*********** Waiting for remote video... *******************')
    const sess = this.xmppConfig['connection'].jingle.sessions[sid]
    const videoTracks = sess.remoteStream.getVideoTracks()
    // const initiator = Strophe.getResourceFromJid(sess['initiator'])

    if (videoTracks.length > 0 && this.videoConfig['sids'][sid]) {
      // angular.element(document).trigger('callactive.jingle', [null, sid])
      const streamURL = window.URL.createObjectURL(sess.remoteStream)
      const trustedStreamURL = this.$sce.trustAsResourceUrl(streamURL)
      this.videoConfig['sourceStreams'][this.videoConfig['sids'][sid]] = trustedStreamURL
      console.log('###################################################################')
      console.log(this.videoConfig['sids'])
      console.log(this.xmppConfig['connection'].jingle.sessions)
      console.log('###################################################################')
      this.ChatRoomService.refreshScope()
    } else {
      setTimeout(() => {this._waitForRemoteVideo(sid)}, 500)
    }
  }
}
