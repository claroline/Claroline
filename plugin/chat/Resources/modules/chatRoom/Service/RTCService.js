/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/* eslint no-unused-vars: ["error", { "vars": "local" }] */
/* global $msg */
/* global hark */
/* global setupRTC */
/* global RTC */
/* global RTCPeerconnection */
/* global getUserMediaWithConstraints */
/* global Strophe */

import angular from 'angular/index'

export default class RTCService {
  constructor ($http, $sce, $log, ChatRoomService, UserService) {
    this.$http = $http
    this.$sce = $sce
    this.$log = $log
    this.ChatRoomService = ChatRoomService
    this.UserService = UserService
    this.chatRoomConfig = this.ChatRoomService.getConfig()
    this.xmppConfig = this.ChatRoomService.getXmppConfig()
    this.config = {
      ice_config: {
        iceServers: this.chatRoomConfig.iceServers
      },
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

    this.RTC = null
    this.$log.log(this.config.ice_config)
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
    this._waitForRemoteStream = this._waitForRemoteStream.bind(this)
    this.ChatRoomService.setConnectedCallback(this._startMedias)
    this.ChatRoomService.setUserDisconnectedCallback(this._stopUserStream)
    this.ChatRoomService.setManagementCallback(this._manageManagementMessage)
    setInterval(this._updateMainStream, 1000)
  }

  getVideoConfig () {
    return this.getConfig()
  }

  getConfig () {
    return this.config
  }

  switchVideo () {
    if (this.config['myVideoEnabled']) {
      this.config['myVideoTracks'].forEach(t => {
        t.enabled = false
      })
      this.config['myVideoEnabled'] = false
    } else {
      this.config['myVideoTracks'].forEach(t => {
        t.enabled = true
      })
      this.config['myVideoEnabled'] = true
    }
    const streamURL = window.URL.createObjectURL(this.config['localStream'])
    const trustedStreamURL = this.$sce.trustAsResourceUrl(streamURL)
    this.config['mySourceStream'] = trustedStreamURL
    this.config['sourceStreams'][this.chatRoomConfig['myUsername']] = trustedStreamURL
  }

  switchAudio () {
    if (this.config['myAudioEnabled']) {
      this.config['myAudioTracks'].forEach(t => {
        t.enabled = false
      })
      this.config['myAudioEnabled'] = false
    } else {
      this.config['myAudioTracks'].forEach(t => {
        t.enabled = true
      })
      this.config['myAudioEnabled'] = true
    }

    const streamURL = window.URL.createObjectURL(this.config['localStream'])
    const trustedStreamURL = this.$sce.trustAsResourceUrl(streamURL)
    this.config['mySourceStream'] = trustedStreamURL
    this.config['sourceStreams'][this.chatRoomConfig['myUsername']] = trustedStreamURL

    this.sendMicroStatus()
  }

  requestUserMicroSwitch (username) {
    this.xmppConfig['connection'].send(
      $msg({
        to: this.chatRoomConfig['room'],
        type: 'groupchat'
      }).c('body').t('')
        .up()
        .c('datas', {status: 'management', username: username, type: 'video-micro-switch'})
    )
  }

  requestUserMicroStatus (username) {
    this.xmppConfig['connection'].send(
      $msg({
        to: this.chatRoomConfig['room'],
        type: 'groupchat'
      }).c('body').t('')
        .up()
        .c('datas', {status: 'management', username: username, type: 'video-micro-status-request'})
    )
  }

  sendMicroStatus () {
    this.xmppConfig['connection'].send(
      $msg({
        to: this.chatRoomConfig['room'],
        type: 'groupchat'
      }).c('body').t('')
        .up()
        .c('datas', {
          status: 'management',
          username: this.chatRoomConfig['myUsername'],
          type: 'video-micro-status',
          value: this.config['myAudioEnabled']
        })
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
          'datas', {
            status: 'management',
            username: this.chatRoomConfig['myUsername'],
            type: 'speaking',
            value: 1
          })
    )
  }

  stopMedia () {
    this.config['myAudioTracks'].forEach(t => {
      t.stop()
    })
    this.config['myVideoTracks'].forEach(t => {
      t.stop()
    })
  }

  selectSourceStream (username) {
    this.$log.log(username)
    if (this.config['selectedUser'] === username) {
      this.config['selectedUser'] = null
    } else {
      this.config['selectedUser'] = username
    }
    this.$log.log(`Selected user: ${this.config['selectedUser']}`)
    this.config['mainStreamUsername'] = username
  }

  initiateCalls () {
    this.$log.log('Initiating calls...')
    this.config['users'].forEach(u => {
      if (u['username'] !== this.chatRoomConfig['myUsername']) {
        this.$log.log(`${this.chatRoomConfig['room']}/${u['username']}`)
        this.$log.log(`${this.chatRoomConfig['room']}/${this.chatRoomConfig['myUsername']}`)
        const session = this.xmppConfig['connection'].jingle.initiate(
          `${this.chatRoomConfig['room']}/${u['username']}`,
          `${this.chatRoomConfig['room']}/${this.chatRoomConfig['myUsername']}`
        )

        // as long a we don't have a better fix...
        if (this.RTC.browser === 'firefox') session.usetrickle = false

        if (session['sid']) {
          this.addSid(session['sid'], u['username'])
        }
        this.requestUserMicroStatus(u['username'])
      }
    })
  }

  initiateHark () {
    if (typeof hark === 'function') {
      const options = { interval: 400 }
      const speechEvents = hark(this.config['localStream'], options)

      speechEvents.on('speaking', () => {
        if (this.config['myAudioEnabled']) {
          this.sendSpeakingNotification()
        }
      })
      speechEvents.on('stopped_speaking', () => {
        if (this.config['myAudioEnabled']) {
          this.$log.log('Stopped speaking.')
        }
      })
      speechEvents.on('volume_change', (volume) => {
        if (this.config['myAudioEnabled'] && this.config['speakingUser'] !== this.chatRoomConfig['myUsername'] && volume > -50) {
          this.sendSpeakingNotification()
        }
      })
    }
  }

  addSid (sid, username) {
    this.config['sids'][sid] = username
  }

  closeAllConnections () {
    for (let sid in this.config['sids']) {
      this.$log.log('Try to close ' + sid)
      this.$log.log(this.xmppConfig['connection'].jingle.sessions[sid])
      if (this.xmppConfig['connection'].jingle.sessions[sid].state === 'active') {
        this.xmppConfig['connection'].jingle.sessions[sid].terminate('Closing all connections...')
        this.$log.log(`${sid} : closed`)
      }
    }
  }

  _updateMainStream () {
    this.$log.log('_updateMainStream')
    if (this.config['selectedUser'] !== null) {
      if (this.config['mainStreamUsername'] !== this.config['selectedUser']) {
        this.config['mainStreamUsername'] = this.config['selectedUser']
        this.ChatRoomService.refreshScope()
        this.$log.log(`Selected user : ${this.config['mainStreamUsername']}`)
      }
    } else if (this.config['speakingUser'] !== null) {
      if (this.config['mainStreamUsername'] !== this.config['speakingUser']) {
        this.config['mainStreamUsername'] = this.config['speakingUser']
        this.ChatRoomService.refreshScope()
        this.$log.log(`Speaking user : ${this.config['mainStreamUsername']}`)
      }
    }
  }

  _startMedias () {
    this.$log.log('REQUEST MEDIAS')
    // for getUserMediaWithConstraints()
    RTC = this.RTC = setupRTC()
    let constraints = []
    if (this.config.myAudioEnabled) constraints.push('audio')
    if (this.config.myVideoEnabled) constraints.push('video')
    if (constraints === []) this.$log.error('NO MEDIA REQUEST')
    getUserMediaWithConstraints(constraints)
    this.xmppConfig['connection'].jingle.ice_config = this.config['ice_config']
    angular.element(document).bind('mediaready.jingle', this._onMediaReady)
    angular.element(document).bind('mediafailure.jingle', this._onMediaFailure)
    angular.element(document).bind('callincoming.jingle', this._onCallIncoming)
    angular.element(document).bind('callterminated.jingle', this._onCallTerminated)
    angular.element(document).bind('remotestreamadded.jingle', this._onRemoteStreamAdded)
    angular.element(document).bind('remotestreamremoved.jingle', this._onRemoteStreamRemoved)
    angular.element(document).bind('iceconnectionstatechange.jingle', this._onIceConnectionStateChanged)
    angular.element(document).bind('nostuncandidates.jingle', this._noStunCandidates)
    angular.element(document).bind('ack.jingle', (event, sid, ack) => {
      this.$log.log('got stanza ack for ' + sid, ack)
    })
    angular.element(document).bind('error.jingle', (event, sid, err) => {
      if (sid) {
        this.$log.error('got stanza error for ' + sid, err)
      } else {
        this.$log.error('no sid defined for', err)
      }
    })
    angular.element(document).bind('packetloss.jingle', (event, sid, loss) => {
      this.$log.warn('packetloss', sid, loss)
    })

    if (this.RTC) {
      RTCPeerconnection = this.RTC.peerconnection
      this.xmppConfig['connection'].jingle.pc_constraints = this.RTC.pc_constraints

      if (this.RTC.browser === 'firefox') {
        this.xmppConfig['connection'].jingle.media_constraints.mandatory.mozDontOfferDataChannel = true
        if (this.config.myAudioEnabled) this.xmppConfig['connection'].jingle.media_constraints.offerToReceiveAudio = true
        if (this.config.myVideoEnabled) this.xmppConfig['connection'].jingle.media_constraints.offerToReceiveVideo = true
      }
    } else {
      this.$log.log('webrtc capable browser required')
    }
  }

  _stopUserStream (username) {
    this.$log.log('%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%')
    for (let sid in this.config['sids']) {
      if (this.config['sids'][sid] === username) {
        this.xmppConfig['connection'].jingle.sessions[sid].terminate('disconnected user')
        delete this.xmppConfig['connection'].jingle.sessions[sid]
        delete this.config['sids'][sid]
      }
    }
    this.$log.log('%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%')
  }

  _manageManagementMessage (type, username, name, value) {
    if (type === 'video-micro-status') {
      if (username !== this.chatRoomConfig['myUsername']) {
        this.config['usersMicroStatus'][username] = (value == 'true')
        this.ChatRoomService.refreshScope()
      }
    } else if (type === 'video-micro-status-request') {
      if (username === this.chatRoomConfig['myUsername']) {
        this.$log.log('resend micro status request')
        this.sendMicroStatus()
      }
    } else if (type === 'video-micro-switch') {
      if (username === this.chatRoomConfig['myUsername']) {
        this.switchAudio()
        this.ChatRoomService.refreshScope()
      }
    } else if (type === 'speaking') {
      this.$log.log(`${username} is speaking...`)
      this.config['speakingUser'] = username
      this.ChatRoomService.refreshScope()
    }
  }

  _onMediaReady (event, stream) {
    this.$log.log('Media ready')
    this.config['localStream'] = stream
    this.xmppConfig['connection'].jingle.localStream = stream
    this.config['localStream'].getAudioTracks().forEach(t => this.config['myAudioTracks'].push(t))
    this.config['localStream'].getVideoTracks().forEach(t => this.config['myVideoTracks'].push(t))
    // Mute sound in my video & main video to avoid echo
    angular.element(document).find('.mute').each((index, el) => el.muted = el.volume = true)
    const streamURL = window.URL.createObjectURL(this.config['localStream'])
    const trustedStreamURL = this.$sce.trustAsResourceUrl(streamURL)
    this.config['mySourceStream'] = trustedStreamURL
    this.config['sourceStreams'][this.chatRoomConfig['myUsername']] = trustedStreamURL
    this.config['mainStreamUsername'] = this.chatRoomConfig['myUsername']
    this.initiateCalls()
    this.sendMicroStatus()
    this.initiateHark()
    this.ChatRoomService.refreshScope()
  }

  _onMediaFailure () {
    this.$log.log('Media failure')
  }

  _onCallIncoming (event, sid) {
    this.$log.log(`Incoming call : ${sid}`)
    const sess = this.xmppConfig['connection'].jingle.sessions[sid]
    const initiator = Strophe.getResourceFromJid(sess['initiator'])
    this.addSid(sid, initiator)
    sess.sendAnswer()
    sess.accept()
  }

  _onCallTerminated () {
    this.$log.log('Call terminated')
  }

  _onRemoteStreamAdded (event, data, sid) {
    this.$log.log(`Remote stream for session ${sid} added.`)
    this._waitForRemoteStream(sid)
  }

  _onRemoteStreamRemoved (event, data, sid) {
    this.$log.log(`Remote stream for session ${sid} removed.`)
  }

  _onIceConnectionStateChanged (event, sid, sess) {
    this.$log.log('_onIceConnectionStateChanged')
    this.$log.log('ice state for', sid, sess.peerconnection.iceConnectionState)
    this.$log.log('sig state for', sid, sess.peerconnection.signalingState)

    if (sess.peerconnection.iceConnectionState === 'connected') {
      this.$log.log('add new stream')
    } else if (sess.peerconnection.iceConnectionState === 'disconnected') {
      this.xmppConfig['connection'].jingle.sessions[sid].terminate('disconnected')
      this.$log.log('remove stream')
    } else if (sess.peerconnection.iceConnectionState === 'failed' || sess.peerconnection.iceConnectionState === 'closed') {
      this.$log.log('failed/closed stream')
    }
  }

  _noStunCandidates () {
    this.$log.error('webrtc did not encounter stun candidates, NAT traversal will not work')
  }

  _waitForRemoteStream (sid) {
    this.$log.log('*********** Waiting for remote stream... *******************')
    const sess = this.xmppConfig['connection'].jingle.sessions[sid]
    const tracks = sess.remoteStream.getTracks()

    if (tracks.length > 0 && this.config['sids'][sid]) {
      const streamURL = window.URL.createObjectURL(sess.remoteStream)
      const trustedStreamURL = this.$sce.trustAsResourceUrl(streamURL)
      this.config['sourceStreams'][this.config['sids'][sid]] = trustedStreamURL
      this.$log.log('###################################################################')
      this.$log.log(this.config['sids'])
      this.$log.log(this.xmppConfig['connection'].jingle.sessions)
      this.$log.log('###################################################################')
      this.ChatRoomService.refreshScope()
    } else {
      setTimeout(() => {
        this._waitForRemoteStream(sid)}, 500)
    }
  }
}
