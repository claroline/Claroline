/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

export default class ChatRoomService {
  constructor ($rootScope, $http, XmppService, UserService, MessageService) {
    this.$rootScope = $rootScope
    this.$http = $http
    this.XmppService = XmppService
    this.UserService = UserService
    this.MessageService = MessageService
    this.xmppConfig = XmppService.getConfig()
    this.config = {
      connected: false,
      busy: false,
      resourceId: ChatRoomService._getGlobal('resourceId'),
      room: `${ChatRoomService._getGlobal('roomName')}@${ChatRoomService._getGlobal('xmppMucHost')}`,
      roomId: ChatRoomService._getGlobal('roomId'),
      roomName: ChatRoomService._getGlobal('roomName'),
      roomStatus: ChatRoomService._getGlobal('roomStatus'),
      roomType: ChatRoomService._getGlobal('roomType'),
      canChat: ChatRoomService._getGlobal('canChat'),
      canEdit: ChatRoomService._getGlobal('canEdit'),
      xmppMucHost: ChatRoomService._getGlobal('xmppMucHost'),
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
    this._connectedCallback = () => {}
    this._userDisconnectedCallback = () => {}
    this._managementCallback = () => {}
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
    return this.MessageService.getMessages()
  }

  getOldMessages () {
    return this.MessageService.getOldMessages()
  }

  setConnectedCallback (callback) {
    this._connectedCallback = callback
  }

  setUserDisconnectedCallback (callback) {
    this._userDisconnectedCallback = callback
  }

  setManagementCallback (callback) {
    this._managementCallback = callback
  }

  connect () {
    this.XmppService.connectWithAdmin()
  }

  connectToRoom () {
    if (this.xmppConfig['connected'] && this.xmppConfig['adminConnected'])  {
      console.log('Connecting to room...')
      this.connectAdminToRoom()
    } else {
      console.log('Not connected to XMPP')
      this.XmppService.setConnectedCallback(this._fullConnection)
      this.XmppService.connectWithAdmin()
    }
  }

  connectAdminToRoom () {
    if (this.xmppConfig['adminConnected'])  {
      console.log(`Connecting ${this.config['adminUsername']} to room...`)
      this.xmppConfig['adminConnection'].addHandler(this._onRoomAdminPresence, null, 'presence')
      this.xmppConfig['adminConnection'].send($pres({to: `${this.config['room']}/${this.config['adminUsername']}`}))
    } else {
      console.log(`${this.config['adminUsername']} is not connected`)
    }
  }

  connectUserToRoom () {
    if (this.xmppConfig['connected'])  {
      console.log('Connecting user to room...')
      this.config['busy'] = true
      this.xmppConfig['connection'].addHandler(this._onRoomPresence, null, 'presence')
      this.xmppConfig['connection'].addHandler(this._onRoomMessage, null, 'message', 'groupchat')
      this.xmppConfig['connection'].addHandler(this._onIQStanza, null, 'iq')
      console.log(`${this.config['room']}/${this.xmppConfig['username']}`)
      this.xmppConfig['connection'].send(
        $pres({to: `${this.config['room']}/${this.xmppConfig['username']}`})
        .c(
          'datas',
          {
            firstName: this.xmppConfig['firstName'],
            lastName: this.xmppConfig['lastName'],
            color: this.xmppConfig['color']
          }
        )
      )
    } else {
      console.log('Not connected to XMPP')
    }
  }

  disconnectFromRoom () {
    if (this.config['connected']) {
      const presence = $pres({
        from: `${this.xmppConfig['username']}@${this.xmppConfig['xmppHost']}/${this.config['roomName']}`,
        to: `${this.config['room']}/${this.config['myUsername']}`,
        type: 'unavailable'
      })
      this.xmppConfig['connection'].send(presence)
      //this.xmppConfig['connection'].flush()
      //this.xmppConfig['connection'].disconnect()
    }
  }

  resetChatRoomDatas () {
    this.config['myRole'] = null
    this.config['myAffiliation'] = null
    this.config['adminConnected'] = false
    this.config['connected'] = false
    this.MessageService.emptyMessages()
    this.MessageService.emptyOldMessages()
    this.UserService.emptyUsers()
    this.UserService.emptyBannedUsers()
    console.log('Disconnected')
  }

  initializeRoom () {
    if (this.xmppConfig['adminConnected'])  {
      console.log(`${this.config['adminUsername']} is connected`)
      this.xmppConfig['adminConnection'].addHandler(this._onRoomAdminPresenceInit, null, 'presence')
      this.xmppConfig['adminConnection'].addHandler(this._onIQStanzaInit, null, 'iq')
      this.xmppConfig['adminConnection'].send($pres({to: `${this.config['room']}/${this.config['adminUsername']}`}))
    } else {
      console.log(`${this.config['adminUsername']} is not connected`)
    }
  }

  configureRoom () {
    console.log('configure room')
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

    //this.getUsers().forEach(u => {
    //  const iq = $iq({
    //    id: `mute-${u['username']}`,
    //    from: `${this.xmppConfig['username']}@${this.xmppConfig['xmppHost']}/${this.config['roomName']}`,
    //    to: this.config['room'],
    //    type: 'set'
    //  }).c('x', {xmlns: 'http://jabber.org/protocol/muc#admin'})
    //  .c('item', {nick: this.xmppConfig['username'], role: 'participant'})
    //  this.xmppConfig['connection'].sendIQ(iq)
    //})
    //
    //const route = Routing.generate('api_put_room_status', {chatRoom: this.config['roomId'], roomStatus: 1})
    //this.$http.put(route).then(datas => {
    //  if (datas['status'] === 200) {
    //    this.config['roomStatus'] = datas['data']['roomStatusText']
    //  }
    //})

    //var message = Translator.trans('chat_room_open_msg', {}, 'chat');
    //
    //XmppService.getConnection().send(
    //    $msg({
    //        to: room,
    //        type: "groupchat"
    //    }).c('body').t(message)
    //    .up()
    //    .c(
    //        'datas',
    //        {
    //            firstName:  XmppService.getFirstName(),
    //            lastName: XmppService.getLastName(),
    //            color: XmppService.getColor(),
    //            status: 'raw'
    //        }
    //    )
    //);
    //
    //var route = Routing.generate(
    //    'claro_chat_room_status_register',
    //    {
    //        chatRoom: roomId,
    //        username: XmppService.getUsername(),
    //        fullName: XmppService.getFullName(),
    //        status: 'open'
    //    }
    //);
    //$http.post(route);
  }

  openRoom () {
    const route = Routing.generate('api_put_room_status', {chatRoom: this.config['roomId'], roomStatus: 1})
    this.$http.put(route).then(datas => {
      if (datas['status'] === 200) {
        this.config['roomStatus'] = datas['data']['roomStatusText']
      }
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
      this.MessageService.addMessage(this.xmppConfig['fullName'], message, this.xmppConfig['color'])
      this.registerMessage(message, this.config['myUsername'], this.xmppConfig['fullName']).then(
        (d) => {
          if (d === 'ok') {
            this.xmppConfig['connection'].send(
              $msg({
                to: this.config['room'],
                type: "groupchat"
              }).c('body').t(message)
              .up()
              .c(
                'datas',
                {
                  firstName:  this.xmppConfig['firstName'],
                  lastName: this.xmppConfig['lastName'],
                  color: this.xmppConfig['color']
                }
              )
            )
          }
        }
      )
    }
  }

  getRegisteredMessages () {
    const route = Routing.generate('api_get_registered_messages' , {chatRoom: this.config['roomId']})
    this.$http.get(route).then(d => {
      d['data'].forEach(m => {
        this.MessageService.addOldMessage(m['userFullName'], m['content'], m['color'], m['type'], m['creationDate'])
      })
    })
  }

  initializeRoleAndAffiliation () {
    console.log('initialize role & affiliation...')

    if (this.config['myAffiliation'] !== 'owner' && this.config['myAffiliation'] !== 'outcast' && this.config['myRole'] !== 'visitor') {
      if (this.config['canEdit']) {
        if (this.config['myAffiliation'] !== 'admin') {
          console.log('Granting ADMIN affiliation...')
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
          console.log('Granting MODERATOR role...')
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
          console.log('Granting NONE affiliation...')
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
          console.log('Granting PARTICIPANT role...')
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
        'datas',
        {
          status: 'management',
          username: username,
          name: name,
          type: 'unban-user',
          value: true
        }
      )
    )
  }

  registerPresence (status, username = this.xmppConfig['username'], fullName = this.xmppConfig['fullName']) {
    const route = Routing.generate(
      'api_post_chat_room_presence_register',
      {
        chatRoom: this.config['roomId'],
        username: username,
        fullName: fullName,
        status: status
      }
    )
    this.$http.post(route)
  }

  registerMessage (message, username = this.xmppConfig['username'], fullName = this.xmppConfig['fullName']) {
    const route = Routing.generate(
      'api_post_chat_room_message_register',
      {
        chatRoom: this.config['roomId'],
        username: username,
        fullName: fullName
      }
    )
    return this.$http.post(route, {message: message}).then(
      d => {return 'ok'},
      d =>  {
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
    const route = Routing.generate('api_post_chat_users_infos')
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
      this.MessageService.addPresenceMessage(name, 'unbanned')
      this.refreshScope()
    } else {
      this._managementCallback(type, username, name, value)
    }
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
        console.log('admin is connected to chat room')
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
        console.log('admin is connected to chat room')
        this.connectUserToRoom()
        response = false
      }
    }

    return response
  }

  _onRoomPresence (presence) {
    console.log(presence)
    const from = $(presence).attr('from')
    const username = Strophe.getResourceFromJid(from)
    const roomName = Strophe.getBareJidFromJid(from)
    const status = $(presence).find('status')
    const statusCode = status.attr('code')
    const error = $(presence).find('error')
    const errorCode = error.attr('code')
    console.log('##### STATUS = ' + statusCode + ' ####')
    console.log('##### ERROR = ' + errorCode + ' ####')

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
        console.log('Forbidden')
        this.config['message'] = Translator.trans('not_authorized_msg', {}, 'chat')
        this.config['messageType'] = 'danger'
        this.refreshScope()
      } else {
        if (username === this.xmppConfig['username']) {
          this.config['myRole'] = role
          this.config['myAffiliation'] = affiliation

          if (statusCode === '110') {

            if (type === 'unavailable') {
              this.resetChatRoomDatas()
              this.registerPresence('disconnection')
            } else {
              this.getRegisteredMessages()
              this.config['connected'] = true
              this.config['busy'] = false
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
            const userDatasName = userDatas['name'] ?  userDatas['name'] : username
            const userDatasColor = userDatas['color'] ?  userDatas['color'] : null
            this.UserService.addBannedUser(username, userDatasName, userDatasColor)
            this.MessageService.addPresenceMessage(name, 'banned')
          } else if (statusCode === '307') {
            this.MessageService.addPresenceMessage(name, 'kicked')
          }
        }

        if (type === 'unavailable') {
          console.log(`****************** ${username} => disconnected`)
          this._userDisconnectedCallback(username)
          this.UserService.removeUser(username, statusCode)
          this.refreshScope()
        } else {
          this.UserService.addUser(username, name, color, affiliation, role)
          this.refreshScope()
        }
      }
    }

    return true
  }

  _onRoomMessage (message) {
    console.log(message)
    const from = $(message).attr('from')
    const type = $(message).attr('type')
    const roomName = Strophe.getBareJidFromJid(from)
    const username = Strophe.getResourceFromJid(from)

    if (type === 'groupchat' && roomName.toLowerCase() === this.config['room'].toLowerCase()) {
      const delayElement = $(message).find('delay')

      if (delayElement === undefined || delayElement[0] === undefined) {
        let body = $(message).find('html > body').html()
        const statusElement  = $(message).find('status')

        if (statusElement === undefined || statusElement.attr('code') !== '104') {

          if (body === undefined) {
            body = $(message).find('body').text()
          }
          const datas = $(message).find('datas')
          const status = datas.attr('status')

          if (status === 'raw') {
            console.log('RAW MESSAGE')
          //  $rootScope.$broadcast('rawRoomMessageEvent', {message: body});
          } else if (status === 'management') {
            const type =  datas.attr('type')
            const user = datas.attr('username')
            const userFullName = datas.attr('name')
            const value =  datas.attr('value');
            this.manageManagementMessage(type, user, userFullName, value)
          } else if (username !== this.config['myUsername']) {
            const firstName = datas.attr('firstName')
            const lastName = datas.attr('lastName')
            let color = datas.attr('color')
            color = (color === undefined) ? null : color
            const sender = (firstName !== undefined && lastName !== undefined) ? `${firstName} ${lastName}` : username
            this.MessageService.addMessage(sender, body, color)
            this.refreshScope()
          }
        }
      }
    }

    return true
  }

  _onIQStanzaInit (iq) {
    //console.log(iq)
    let response = true
    const type = $(iq).attr('type')
    const id = $(iq).attr('id')

    if (type === 'result') {
      if (id === 'room-config-submit') {
        console.log('Room configured')
        this.openRoom()
        response = false
      }
    }

    return response
  }

  _onIQStanza (iq) {
    //console.log(iq)
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
        const userDatasName = userDatas['name'] ?  userDatas['name'] : username
        const userDatasColor = userDatas['color'] ?  userDatas['color'] : null
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