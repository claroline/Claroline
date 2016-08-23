/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import $ from 'jquery'
import ChatRoom from '../Model/ChatRoom'

/* global Routing */
/* global Translator */
/* global Strophe */

/* global $pres */
/* global $iq */
/* global $msg */

export default class ChatRoomService {
  constructor ($rootScope, $http, $log, $httpParamSerializerJQLike, XmppService, UserService) {
    this.$rootScope = $rootScope
    this.$http = $http
    this.$log = $log
    this.$httpParamSerializerJQLike = $httpParamSerializerJQLike
    this.XmppService = XmppService
    this.UserService = UserService
    this.messages = []
    this.xmppConfig = XmppService.getConfig()
    this.config = {
      connected: false,
      busy: false, // connecting
      configuring: false,
      resourceId: ChatRoomService._getGlobal('resourceId'),
      resourceName: ChatRoomService._getGlobal('resourceName'),
      chatRoom: ChatRoomService._getGlobal('chatRoom'),
      room: `${ChatRoomService._getGlobal('roomName')}@${ChatRoomService._getGlobal('xmppMucHost')}`,
      roomName: ChatRoomService._getGlobal('roomName'),
      canChat: ChatRoomService._getGlobal('canChat'),
      canEdit: ChatRoomService._getGlobal('canEdit'),
      xmppMucHost: ChatRoomService._getGlobal('xmppMucHost'),
      iceServers: ChatRoomService._getGlobal('iceServers'),
      myUsername: null,
      myRole: 'none',
      myAffiliation: null,
      adminConnected: false,
      adminUsername: ChatRoomService._getGlobal('chatAdminUsername'),
      adminPassword: ChatRoomService._getGlobal('chatAdminPassword'),
      messageType: null,
      message: null
    }
    this._onRoomAdminPresenceInit = this._onRoomAdminPresenceInit.bind(this)
    this._onRoomAdminPresence = this._onRoomAdminPresence.bind(this)
    this._onRoomPresence = this._onRoomPresence.bind(this)
    this._onRoomMessage = this._onRoomMessage.bind(this)
    this._onIQStanzaInit = this._onIQStanzaInit.bind(this)
    this._onIQStanza = this._onIQStanza.bind(this)
    this._fullConnection = this._fullConnection.bind(this)
    this._onRoomClose = this._onRoomClose.bind(this)
    this._onChangeRoomType = this._onChangeRoomType.bind(this)

    this._connectedCallback = () => {
    }
    this._userDisconnectedCallback = () => {
    }
    this._managementCallback = () => {
    }
    this._closeCallback = () => {
    }
    this._changeRoomTypeCallback = () => {
    }
  }

  getConfig () {
    return this.config
  }

  getXmppConfig () {
    return this.xmppConfig
  }

  getUsers () {
    return this.UserService.getUsers()
  }

  getBannedUsers () {
    return this.UserService.getBannedUsers()
  }

  getMessages () {
    return this.messages
  }

  setConnectedCallback (callback) {
    this._connectedCallback = callback
  }

  setUserDisconnectedCallback (callback) {
    this._userDisconnectedCallback = callback
  }

  setCloseCallback (callback) {
    this._closeCallback = callback
  }

  setChangeRoomTypeCallback (callback) {
    this._changeRoomTypeCallback = callback
  }

  setManagementCallback (callback) {
    this._managementCallback = callback
  }

  connect () {
    this.XmppService.connectWithAdmin()
  }

  connectToRoom () {
    this.$log.log('CONNECT TO ROOM')
    this.config['busy'] = true
    if (this.xmppConfig['connected'] && this.xmppConfig['adminConnected']) {
      this.$log.log('Connecting to room...')
      this.connectAdminToRoom()
    } else {
      this.$log.log('Not connected to XMPP')
      this.XmppService.setConnectedCallback(this._fullConnection)
      this.XmppService.connectWithAdmin()
    }
  }

  connectAdminToRoom () {
    if (this.xmppConfig['adminConnected']) {
      this.config['busy'] = true
      this.$log.log(`Connecting ${this.config['adminUsername']} to room...`)
      this.xmppConfig['adminConnection'].addHandler(this._onRoomAdminPresence, null, 'presence')
      this.xmppConfig['adminConnection'].send($pres({to: `${this.config['room']}/${this.config['adminUsername']}`}))
    } else {
      this.$log.log(`${this.config['adminUsername']} is not connected`)
    }
  }

  connectUserToRoom () {
    if (this.xmppConfig['connected']) {
      this.$log.log('Connecting user to room...')
      this.config['busy'] = true
      this.xmppConfig['connection'].addHandler(this._onRoomPresence, null, 'presence')
      this.xmppConfig['connection'].addHandler(this._onRoomMessage, null, 'message', 'groupchat')
      this.xmppConfig['connection'].addHandler(this._onIQStanza, null, 'iq')
      this.$log.log(`${this.config['room']}/${this.xmppConfig['username']}`)
      this.xmppConfig['connection'].send(
        $pres({to: `${this.config['room']}/${this.xmppConfig['username']}`}).c(
          'datas',
          {
            firstName: this.xmppConfig['firstName'],
            lastName: this.xmppConfig['lastName'],
            color: this.xmppConfig['color']
          }
        )
      )
    } else {
      this.$log.log('Not connected to XMPP')
    }
  }

  disconnectFromRoom () {
    this.$log.log('DISCONNECT FROM ROOM')
    if (this.config['connected']) {
      const presence = $pres({
        from: `${this.xmppConfig['username']}@${this.xmppConfig['xmppHost']}/${this.config['roomName']}`,
        to: `${this.config['room']}/${this.config['myUsername']}`,
        type: 'unavailable'
      })
      this.xmppConfig['connection'].send(presence)
      this.xmppConfig['connection'].disconnect()
    }

    this.config['busy'] = false
  }

  initializeRoom () {
    if (this.xmppConfig['adminConnected']) {
      this.$log.log(`${this.config['adminUsername']} is connected`)
      this.xmppConfig['adminConnection'].addHandler(this._onRoomAdminPresenceInit, null, 'presence')
      this.xmppConfig['adminConnection'].addHandler(this._onIQStanzaInit, null, 'iq')
      this.xmppConfig['adminConnection'].send($pres({to: `${this.config['room']}/${this.config['adminUsername']}`}))
    } else {
      this.$log.log(`${this.config['adminUsername']} is not connected`)
    }
  }

  configureRoom () {
    this.$log.log('configure room')
    const iq = $iq({
      id: 'room-config-submit',
      from: `${this.config['adminUsername']}@${this.xmppConfig['xmppHost']}/${this.config['roomName']}`,
      to: this.config['room'],
      type: 'set'
    }).c('query', {xmlns: 'http://jabber.org/protocol/muc#owner'})
      .c('x', {xmlns: 'jabber:x:data', type: 'submit'})
      .c('field', {var: 'FORM_TYPE'})
      .c('value').t('http://jabber.org/protocol/muc#roomconfig')
      .up()
      .up()
      .c('field', {var: 'muc#roomconfig_persistentroom'})
      .c('value').t(1)
      .up()
      .up()
      .c('field', {var: 'muc#roomconfig_moderatedroom'})
      .c('value').t(0)
      .up()
      .up()
      .c('field', {var: 'muc#roomconfig_whois'})
      .c('value').t('moderators')
    this.xmppConfig['adminConnection'].sendIQ(iq)
  }

  openRoom () {
    this.config['chatRoom']['room_status'] = 1
    this.editChatRoom(this.config['chatRoom'])
  }

  editChatRoom (chatRoom) {
    return this.$http.put(
      Routing.generate('api_put_chat_room', {chatRoom: this.config.chatRoom.id}),
      this.$httpParamSerializerJQLike({'chat_room': chatRoom}),
      {headers: {'Content-Type': 'application/x-www-form-urlencoded'}}
    ).then((data) => {
      this.config.chatRoom.configuring = true
      return this.config.chatRoom = data.data
    })
  }

  isAdmin () {
    return this.config['myAffiliation'] === 'admin' || this.config['myAffiliation'] === 'owner'
  }

  isModerator () {
    return this.config['myRole'] === 'moderator'
  }

  canParticipate () {
    return this.config['connected'] && this.config['myRole'] !== 'none' && this.config['myRole'] !== 'visitor'
  }

  sendMessage (message) {
    if (message !== '') {
      this.messages.push({sender: this.xmppConfig['fullName'], message: message, color: this.xmppConfig['color'], type: 'message'})
      this.registerMessage(message, this.config['myUsername'], this.xmppConfig['fullName']).then(
        (d) => {
          if (d === 'ok') {
            this.xmppConfig['connection'].send(
              $msg({
                to: this.config['room'],
                type: 'groupchat'
              })
                .c('body')
                .t(message)
                .up()
                .c(
                  'datas',
                  {firstName: this.xmppConfig['firstName'], lastName: this.xmppConfig['lastName'], color: this.xmppConfig['color']}
              )
            )
          }
        }
      )
    }
  }

  getOldMessages () {
    const messages = []
    const route = Routing.generate('api_get_registered_messages' , {chatRoom: this.config.chatRoom.id})
    this.$http.get(route).then(d => {
      d['data'].forEach(m => {
        messages.push({name: m['userFullName'], content: m['content'], color: m['color'], type: m['type'], creationDate: m['creationDate']})
      })
    })

    return messages
  }

  initializeRoleAndAffiliation () {
    this.$log.log('initialize role & affiliation...')

    if (this.config['myAffiliation'] !== 'owner' && this.config['myAffiliation'] !== 'outcast' && this.config['myRole'] !== 'visitor') {
      if (this.config['canEdit']) {
        if (this.config['myAffiliation'] !== 'admin') {
          this.$log.log('Granting ADMIN affiliation...')
          const affiliationIq = $iq({
            id: `role-${this.config['myUsername']}`,
            from: `${this.config['adminUsername']}@${this.xmppConfig['xmppHost']}/${this.config['roomName']}`,
            to: this.config['room'],
            type: 'set'
          }).c('query', {xmlns: 'http://jabber.org/protocol/muc#admin'})
            .c('item', {jid: `${this.config['myUsername']}@${this.xmppConfig['xmppHost']}`, affiliation: 'admin'})
          this.xmppConfig['adminConnection'].sendIQ(affiliationIq)
        }

        if (this.config['myRole'] !== 'moderator') {
          this.$log.log('Granting MODERATOR role...')
          const roleIq = $iq({
            id: `role-${this.config['myUsername']}`,
            from: `${this.config['adminUsername']}@${this.xmppConfig['xmppHost']}/${this.config['roomName']}`,
            to: this.config['room'],
            type: 'set'
          }).c('x', {xmlns: 'http://jabber.org/protocol/muc#admin'})
            .c('item', {nick: this.config['myUsername'], role: 'moderator'})
          this.xmppConfig['adminConnection'].sendIQ(roleIq)
        }
      } else {
        if (this.config['myAffiliation'] !== 'none') {
          this.$log.log('Granting NONE affiliation...')
          const affiliationIq = $iq({
            id: `role-${this.config['myUsername']}`,
            from: `${this.config['adminUsername']}@${this.xmppConfig['xmppHost']}/${this.config['roomName']}`,
            to: this.config['room'],
            type: 'set'
          }).c('query', {xmlns: 'http://jabber.org/protocol/muc#admin'})
            .c('item', {jid: `${this.config['myUsername']}@${this.xmppConfig['xmppHost']}`, affiliation: 'none'})
          this.xmppConfig['adminConnection'].sendIQ(affiliationIq)
        }

        if (this.config['myRole'] !== 'participant') {
          this.$log.log('Granting PARTICIPANT role...')
          const roleIq = $iq({
            id: `role-${this.config['myUsername']}`,
            from: `${this.config['adminUsername']}@${this.xmppConfig['xmppHost']}/${this.config['roomName']}`,
            to: this.config['room'],
            type: 'set'
          }).c('x', {xmlns: 'http://jabber.org/protocol/muc#admin'})
            .c('item', {nick: this.config['myUsername'], role: 'participant'})
          this.xmppConfig['adminConnection'].sendIQ(roleIq)
        }
      }
    }

  // this.$log.log('Disconnecting the admin (roles are set)')
  // this.xmppConfig['adminConnection'].disconnect()
  }

  requestOutcastList () {
    const iq = $iq({
      id: 'room-outcast-list',
      from: `${this.xmppConfig['username']}@${this.xmppConfig['xmppHost']}/${this.config['roomName']}`,
      to: this.config['room'],
      type: 'get'
    }).c('query', {xmlns: 'http://jabber.org/protocol/muc#admin'})
      .c('item', {affiliation: 'outcast'})
    this.xmppConfig['connection'].sendIQ(iq)
  }

  kickUser (username) {
    if (this.config['canEdit'] && this.config['myRole'] === 'moderator') {
      const iq = $iq({
        id: `kick-${username}`,
        from: `${this.xmppConfig['username']}@${this.xmppConfig['xmppHost']}/${this.config['roomName']}`,
        to: this.config['room'],
        type: 'set'
      }).c('x', {xmlns: 'http://jabber.org/protocol/muc#admin'})
        .c('item', {nick: username, role: 'none'})
      this.xmppConfig['connection'].sendIQ(iq)
    }
  }

  muteUser (username) {
    if (this.config['canEdit'] && this.config['myRole'] === 'moderator') {
      const iq = $iq({
        id: `mute-${username}`,
        from: `${this.xmppConfig['username']}@${this.xmppConfig['xmppHost']}/${this.config['roomName']}`,
        to: this.config['room'],
        type: 'set'
      }).c('x', {xmlns: 'http://jabber.org/protocol/muc#admin'})
        .c('item', {nick: username, role: 'visitor'})
      this.xmppConfig['connection'].sendIQ(iq)
    }
  }

  unmuteUser (username) {
    if (this.config['canEdit'] && this.config['myRole'] === 'moderator') {
      const iq = $iq({
        id: `unmute-${username}`,
        from: `${this.xmppConfig['username']}@${this.xmppConfig['xmppHost']}/${this.config['roomName']}`,
        to: this.config['room'],
        type: 'set'
      }).c('x', {xmlns: 'http://jabber.org/protocol/muc#admin'})
        .c('item', {nick: username, role: 'participant'})
      this.xmppConfig['connection'].sendIQ(iq)
    }
  }

  banUser (username) {
    if (this.config['canEdit'] && this.config['myAffiliation'] === 'admin') {
      const iq = $iq({
        id: `ban-${username}`,
        from: `${this.xmppConfig['username']}@${this.xmppConfig['xmppHost']}/${this.config['roomName']}`,
        to: this.config['room'],
        type: 'set'
      }).c('query', {xmlns: 'http://jabber.org/protocol/muc#admin'})
        .c('item', {jid: `${username}@${this.xmppConfig['xmppHost']}`, affiliation: 'outcast'})
      this.xmppConfig['connection'].sendIQ(iq)
    }
  }

  unbanUser (username) {
    if (this.config['canEdit'] && this.config['myAffiliation'] === 'admin') {
      const iq = $iq({
        id: `unban-${username}`,
        from: `${this.xmppConfig['username']}@${this.xmppConfig['xmppHost']}/${this.config['roomName']}`,
        to: this.config['room'],
        type: 'set'
      }).c('query', {xmlns: 'http://jabber.org/protocol/muc#admin'})
        .c('item', {jid: `${username}@${this.xmppConfig['xmppHost']}`, affiliation: 'none'})
      this.xmppConfig['connection'].sendIQ(iq)
    }
  }

  sendUnbanMessage (username, name) {
    this.xmppConfig['connection'].send(
      $msg({
        to: this.config['room'],
        type: 'groupchat'
      }).c('body').t('')
        .up()
        .c(
          'datas', {
            status: 'management',
            username: username,
            name: name,
            type: 'unban-user',
            value: true
          })
    )
  }

  registerPresence (status, username = this.xmppConfig['username'] , fullName = this.xmppConfig['fullName']) {
    const route = Routing.generate(
      'api_post_chat_room_presence_register',
      {
        chatRoom: this.config.chatRoom.id,
        username: username,
        fullName: fullName,
        status: status
      }
    )
    this.$http.post(route)
  }

  registerMessage (message, username = this.xmppConfig['username'] , fullName = this.xmppConfig['fullName']) {
    const route = Routing.generate(
      'api_post_chat_room_message_register',
      {
        chatRoom: this.config.chatRoom.id,
        username: username,
        fullName: fullName
      }
    )
    return this.$http.post(route, {message: message}).then(
      () => {
        return 'ok'},
      d => {
        if (d['status'] === 403) {
          const route = Routing.generate('claro_resource_open_short', {node: this.config['resourceId']})
          window.location = route
        }
      }
    )
  }

  manageKickedStatus () {
    this.registerPresence('kicked')
    this.config['messageType'] = 'warning'
    this.config['message'] = Translator.trans('kicked_msg', {}, 'chat')
    this.config['myRole'] = 'none'
    this.config['myAffiliation'] = null
    this.config['adminConnected'] = false
    this.config['connected'] = false
  }

  manageBannedStatus () {
    this.registerPresence('banned')
    this.config['messageType'] = 'danger'
    this.config['message'] = Translator.trans('banned_msg', {}, 'chat')
    this.config['myRole'] = 'none'
    this.config['myAffiliation'] = 'outcast'
    this.config['adminConnected'] = false
    this.config['connected'] = false
  }

  manageBannedUsers (bannedUsernames) {
    const route = Routing.generate('api_post_chat_users_infos', {chatRoom: this.config.chatRoom.id})
    this.$http.post(route, {usernames: bannedUsernames}).then(
      d => {
        const usersDatas = d['data']
        bannedUsernames.forEach(username => {
          const name = usersDatas[username] ? `${usersDatas[username]['firstName']} ${usersDatas[username]['lastName']}` : username
          const color = usersDatas[username] ? usersDatas[username]['color'] : null
          this.UserService.addBannedUser(username, name, color)
        })
      }
    )
  }

  manageManagementMessage (type, username, name, value) {
    if (type === 'unban-user') {
      this.UserService.removeBannedUser(username)
      this.messages.push({name: name, status: 'unbanned', type: 'presence'})
      this.refreshScope()
    } else {
      switch (type) {
      case 'close_room':
        this._onRoomClose()
        break
      case 'change_room_type':
        this._onChangeRoomType(value)
        break
      default:
        this._managementCallback(type, username, name, value)
      }
    }
  }

  close () {
    this.xmppConfig['connection'].send(
      $msg({
        to: this.config['room'],
        type: 'groupchat'
      }).c('body').t('')
        .up()
        .c(
          'datas', {
            status: 'management',
            type: 'close_room',
            value: true
          })
    )
  }

  changeRoomType (type) {
    this.xmppConfig['connection'].send(
      $msg({
        to: this.config['room'],
        type: 'groupchat'
      }).c('body').t('')
        .up()
        .c(
          'datas', {
            status: 'management',
            type: 'change_room_type',
            value: type
          })
    )
  }

  _onRoomClose () {
    // closed
    this.config.chatRoom.room_status = ChatRoom.CLOSED
    this.config.chatRoom.room_status_text = 'closed'
    this._closeCallback()
  }

  _onChangeRoomType (type) {
    this.config.chatRoom.room_type = ChatRoom[type.toUpperCase()]
    this.config.chatRoom.room_type_text = type
    this._changeRoomTypeCallback(type)
  }

  _onRoomAdminPresenceInit (presence) {
    let response = true
    const from = $(presence).attr('from')
    const roomName = Strophe.getBareJidFromJid(from)
    const status = $(presence).find('status')
    const statusCode = status.attr('code')

    if (roomName.toLowerCase() === this.config['room'].toLowerCase()) {
      const username = Strophe.getResourceFromJid(from)

      if (username === this.config['adminUsername'] && statusCode === '110') {
        this.$log.log('admin is connected to chat room')
        this.configureRoom()
        response = false
      }
    }

    return response
  }

  _onRoomAdminPresence (presence) {
    let response = true
    const from = $(presence).attr('from')
    const roomName = Strophe.getBareJidFromJid(from)
    const status = $(presence).find('status')
    const statusCode = status.attr('code')

    if (roomName.toLowerCase() === this.config['room'].toLowerCase()) {
      const username = Strophe.getResourceFromJid(from)

      if (username === this.config['adminUsername'] && statusCode === '110') {
        this.$log.log('admin is connected to chat room')
        this.connectUserToRoom()
        response = false
      }
    }

    return response
  }

  _onRoomPresence (presence) {
    const from = $(presence).attr('from')
    const username = Strophe.getResourceFromJid(from)
    const roomName = Strophe.getBareJidFromJid(from)
    const status = $(presence).find('status')
    const statusCode = status.attr('code')
    const error = $(presence).find('error')
    const errorCode = error.attr('code')

    if (statusCode) {
      this.$log.log('##### STATUS = ' + statusCode + ' ####')
    }

    if (errorCode) {
      this.$log.error('##### ERROR = ' + errorCode + ' ####')
    }

    if (roomName.toLowerCase() === this.config['room'].toLowerCase() && username !== this.config['adminUsername']) {
      const type = $(presence).attr('type')
      const datas = $(presence).find('datas')
      const firstName = datas.attr('firstName')
      const lastName = datas.attr('lastName')
      let color = datas.attr('color')
      const item = $(presence).find('item')
      const affiliation = item.attr('affiliation')
      const role = item.attr('role')
      color = (color === undefined) ? null : color

      let name = (firstName !== undefined && lastName !== undefined) ? `${firstName} ${lastName}` : username
      name = (name === username) ? this.UserService.getUserFullName(name) : name

      if (errorCode === '403') {
        this.$log.log('Forbidden')
        this.config['message'] = Translator.trans('not_authorized_msg', {}, 'chat')
        this.config['messageType'] = 'danger'
        this.refreshScope()
      } else {
        if (username === this.xmppConfig['username']) {
          this.config['myRole'] = role
          this.config['myAffiliation'] = affiliation

          if (statusCode === '110') {
            if (type === 'unavailable') {
              this.$log.error('Something went wrong. OnRoomPresence stanza type = "unavailable"')
            } else {
              this.config['connected'] = true
              this.config['myUsername'] = username
              this.config['messageType'] = null
              this.config['message'] = null
              this.registerPresence('connection')
              this.initializeRoleAndAffiliation()

              if (this.config['canEdit'] && this.config['myAffiliation'] === 'admin') {
                this.requestOutcastList()
              }
              this._connectedCallback()
              this.refreshScope()
            }
          } else if (statusCode === '301') {
            this.manageBannedStatus()
          } else if (statusCode === '307') {
            this.manageKickedStatus()
          }
        } else {
          if (statusCode === '301') {
            const userDatas = this.UserService.getUserDatas(username)
            const userDatasName = userDatas['name'] ? userDatas['name'] : username
            const userDatasColor = userDatas['color'] ? userDatas['color'] : null
            this.UserService.addBannedUser(username, userDatasName, userDatasColor)
            this.messages.push({name: name, status: 'banned', type: 'presence'})
          } else if (statusCode === '307') {
            this.messages.push({name: name, status: 'kicked', type: 'presence'})
          }
        }

        // move this above ?
        if (type === 'unavailable') {
          this.$log.log(`****************** ${username} => disconnected`)
          this._userDisconnectedCallback(username)
          this.UserService.removeUser(username, statusCode)
          this.refreshScope()
        } else {
          this.UserService.addUser(username, name, color, affiliation, role)
          this.refreshScope()
        }
      }
    }

    this.config['busy'] = false

    return true
  }

  _onRoomMessage (message) {
    const from = $(message).attr('from')
    const type = $(message).attr('type')
    const roomName = Strophe.getBareJidFromJid(from)
    const username = Strophe.getResourceFromJid(from)

    if (type === 'groupchat' && roomName.toLowerCase() === this.config['room'].toLowerCase()) {
      const delayElement = $(message).find('delay')

      if (delayElement === undefined || delayElement[0] === undefined) {
        let body = $(message).find('html > body').html()
        const statusElement = $(message).find('status')

        if (statusElement === undefined || statusElement.attr('code') !== '104') {
          if (body === undefined) {
            body = $(message).find('body').text()
          }

          const datas = $(message).find('datas')
          const status = datas.attr('status')

          if (status === 'management') {
            const type = datas.attr('type')
            const user = datas.attr('username')
            const userFullName = datas.attr('name')
            const value = datas.attr('value')
            this.manageManagementMessage(type, user, userFullName, value)
          } else if (username !== this.config['myUsername']) {
            const firstName = datas.attr('firstName')
            const lastName = datas.attr('lastName')
            let color = datas.attr('color')
            color = (color === undefined) ? null : color
            const sender = (firstName !== undefined && lastName !== undefined) ? `${firstName} ${lastName}` : username
            this.messages.push({sender: sender, message: body, color: color, type: 'message'})
            this.refreshScope()
          }
        }
      }
    }

    return true
  }

  _onIQStanzaInit (iq) {
    const type = $(iq).attr('type')
    const id = $(iq).attr('id')

    if (type === 'result') {
      if (id === 'room-config-submit') {
        this.$log.log('Room configured')
        this.openRoom()
      }
    }

    return true
  }

  _onIQStanza (iq) {
    // this.$log.log(iq)
    const type = $(iq).attr('type')
    const id = $(iq).attr('id')

    if (type === 'result') {
      if (id === 'room-outcast-list') {
        const items = $(iq).find('item')
        let bannedUsernames = []

        for (let i = 0; i < items.length; i++) {
          const jid = $(items[i]).attr('jid')
          const username = Strophe.getNodeFromJid(jid)
          bannedUsernames.push(username)
        }
        this.manageBannedUsers(bannedUsernames)
        this.refreshScope()
      } else if (id.substring(0, 4) === 'ban-') {
        const username = id.substring(4, id.length)
        const userDatas = this.UserService.getUserDatas(username)
        const userDatasName = userDatas['name'] ? userDatas['name'] : username
        const userDatasColor = userDatas['color'] ? userDatas['color'] : null
        this.UserService.addBannedUser(username, userDatasName, userDatasColor)
        this.refreshScope()
      } else if (id.substring(0, 6) === 'unban-') {
        const username = id.substring(6, id.length)
        const name = this.UserService.getBannedUserFullName(username)
        this.registerPresence('unbanned', username, name)
        this.sendUnbanMessage(username, name)
      }
    }

    return true
  }

  _fullConnection () {
    this.connectToRoom()
  }

  static _getGlobal (name) {
    if (typeof window[name] === 'undefined') {
      throw new Error(
        `Expected ${name} to be exposed in a window.${name} variable`
      )
    }

    return window[name]
  }

  refreshScope () {
    this.$rootScope.$apply()
  }
}
