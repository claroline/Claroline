/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

export default class ChatRoomService {
  constructor ($http, XmppService, UserService, MessageService) {
    this.$http = $http
    this.XmppService = XmppService
    this.UserService = UserService
    this.MessageService = MessageService
    this.xmppConfig = XmppService.getConfig()
    this.config = {
      connected: false,
      busy: false,
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
      adminUsername: ChatRoomService._getGlobal('chatAdminUsername'),
      adminPassword: ChatRoomService._getGlobal('chatAdminPassword'),
    }
    this._onRoomMessage = this._onRoomMessage.bind(this)
    this._onRoomPresence = this._onRoomPresence.bind(this)
    this._onIQStanza = this._onIQStanza.bind(this)
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

  connect () {
    this.XmppService.connect()
  }

  connectAdminToRoom () {
    if (this.xmppConfig['adminConnected'])  {
      console.log(`${this.config['adminUsername']} is connected`)
    } else {
      console.log(`${this.config['adminUsername']} is not connected`)
    }
  }

  connectToRoom () {
    if (this.xmppConfig['connected'])  {
      console.log('Connecting to room...')
      console.log(this.xmppConfig)
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
      this.xmppConfig['connection'].send(
        $pres({
            to: `${this.config['room']}/${this.xmppConfig['username']}`,
            from: `${this.xmppConfig['username']}@${this.xmppConfig['xmppHost']}/${this.config['roomName']}`,
            type: 'unavailable'
        })
      )
      this.xmppConfig['connection'].flush()
      this.xmppConfig['connection'].disconnect()
      this.config['connected'] = false

      // TODO : Log disconnection
    }
  }

  initializeRoom () {
    //const iq = $iq({
    //  id: 'room-config-submit',
    //  from: `${this.xmppConfig['username']}@${this.xmppConfig['xmppHost']}/${this.config['roomName']}`,
    //  to: this.config['room'],
    //  type: 'set'
    //}).c('query', {xmlns: 'http://jabber.org/protocol/muc#owner'})
    //.c('x', {xmlns: 'jabber:x:data', type: 'submit'})
    //.c('field', {var: 'FORM_TYPE'})
    //.c('value').t('http://jabber.org/protocol/muc#roomconfig')
    //.up()
    //.up()
    //.c('field', {var: 'muc#roomconfig_persistentroom'})
    //.c('value').t(1)
    //.up()
    //.up()
    //.c('field', {var: 'muc#roomconfig_moderatedroom'})
    //.c('value').t(0)
    //.up()
    //.up()
    //.c('field', {var: 'muc#roomconfig_whois'})
    //.c('value').t('moderators')
    //this.xmppConfig['connection'].sendIQ(iq)
    //
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

  isAdmin () {
    return this.config['myAffiliation'] === 'admin' || this.config['myAffiliation'] === 'owner'
  }

  isModerator () {
    return this.config['myRole'] === 'moderator'
  }

  canParticipate () {
    return this.config['myRole'] !== 'none' && this.config['myRole'] !== 'visitor'
  }

  sendMessage (message) {
    if (message !== '') {
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
      );
      // TODO : Register message

      //var route = Routing.generate(
      //    'claro_chat_room_message_register',
      //    {
      //        chatRoom: roomId,
      //        username: XmppService.getUsername(),
      //        fullName: XmppService.getFullName(),
      //        message: message
      //    }
      //);
      //$http.post(route);
      console.log(message)
    }
    //this.MessageService.
  }

  static _getGlobal (name) {
    if (typeof window[name] === 'undefined') {
      throw new Error(
        `Expected ${name} to be exposed in a window.${name} variable`
      )
    }

    return window[name]
  }

  _onRoomMessage (message) {
    console.log(message)
    const from = $(message).attr('from')
    const type = $(message).attr('type')
    const roomName = Strophe.getBareJidFromJid(from)

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
            console.log('MANAGEMENT MESSAGE')
          //  var type =  datas.attr('type');
          //  var username = datas.attr('username');
          //  var value =  datas.attr('value');
          //  $rootScope.$broadcast('managementEvent', {type: type, username: username, value: value});
          } else {
            const firstName = datas.attr('firstName');
            const lastName = datas.attr('lastName');
            let color = datas.attr('color');
            color = (color === undefined) ? null : color;
            const sender = (firstName !== undefined && lastName !== undefined) ?
                firstName + ' ' + lastName :
                Strophe.getResourceFromJid(from);
            this.MessageService.addMessage(sender, body, color)
          }
        }
      }
    }

    return true
  }

  _onRoomPresence (presence) {
    console.log(presence)
    const from = $(presence).attr('from')
    const roomName = Strophe.getBareJidFromJid(from)
    const status = $(presence).find('status')
    const statusCode = status.attr('code')
    const error = $(presence).find('error')
    const errorCode = error.attr('code')
    console.log('##### STATUS = ' + statusCode + ' ####')
    console.log('##### ERROR = ' + errorCode + ' ####')

    if (roomName.toLowerCase() === this.config['room'].toLowerCase()) {
      const username = Strophe.getResourceFromJid(from)
      console.log('##### USERNAME = ' + username + ' ####')
      const type = $(presence).attr('type')
      const datas = $(presence).find('datas')
      const firstName = datas.attr('firstName')
      const lastName = datas.attr('lastName')
      let color = datas.attr('color')
      const item = $(presence).find('item')
      const affiliation = item.attr('affiliation')
      const role = item.attr('role')
      color = (color === undefined) ? null : color

      const name = (firstName !== undefined && lastName !== undefined) ?
        firstName + ' ' + lastName :
        username

      //if (errorCode === '403') {
      //  $rootScope.$broadcast('xmppMucForbiddenConnectionEvent');
      //
      //  return true;
      //}

      if (username === this.xmppConfig['username']) {
        this.config['myRole'] = role
        this.config['myAffiliation'] = affiliation

        if (statusCode === '110') {
          this.config['connected'] = true
          this.config['busy'] = false
          // TODO : Log connection
          //this.$state.transitionTo(
          //  'registration_cursus_management',
          //  {cursusId: this.cursusId},
          //  { reload: true, inherit: true, notify: true }
          //)
          this.config['myUsername'] = username
          //$rootScope.$broadcast('xmppMucConnectedEvent');
          //$rootScope.$broadcast('myPresenceConfirmationEvent');
          //
          //var route = Routing.generate(
          //  'claro_chat_room_presence_register',
          //  {
          //    chatRoom: roomId,
          //    username: XmppService.getUsername(),
          //    fullName: XmppService.getFullName(),
          //    status: 'connection'
          //  }
          //);
          //$http.post(route);
          //
          //if (vm.isAdmin()) {
          //  vm.requestOutcastList();
          //}
        }
        //else if (statusCode === '301') {
        //  $rootScope.$broadcast('xmppMucBannedEvent');
        //  var route = Routing.generate(
        //    'claro_chat_room_presence_register',
        //    {
        //      chatRoom: roomId,
        //      username: XmppService.getUsername(),
        //      fullName: XmppService.getFullName(),
        //      status: 'banned'
        //    }
        //  );
        //  $http.post(route);
        //} else if (statusCode === '307') {
        //  $rootScope.$broadcast('xmppMucKickedEvent');
        //  var route = Routing.generate(
        //    'claro_chat_room_presence_register',
        //    {
        //      chatRoom: roomId,
        //      username: XmppService.getUsername(),
        //      fullName: XmppService.getFullName(),
        //      status: 'kicked'
        //    }
        //  );
        //  $http.post(route);
        //}
      }

      if (type === 'unavailable') {
        this.UserService.removeUser(username, statusCode)
      } else {
        this.UserService.addUser(username, name, color, affiliation, role)
      }
    }

    return true
  }

  _onIQStanza (iq) {
    console.log(iq)
    const type = $(iq).attr('type')
    const id = $(iq).attr('id')

    if (type === 'result') {
      if (id === 'room-outcast-list') {
        const items = $(iq).find('item')
        items.forEach(item => {
          const jid = item.attr('jid')
          const username = Strophe.getNodeFromJid(jid)
          this.UserService.addBannedUser(username)
        })
      } else if (id.substring(0, 4) === 'ban-') {
        const username = id.substring(4, id.length)
        this.UserService.addBannedUser(username)
      } else if (id.substring(0, 6) === 'unban-') {
        const username = id.substring(6, id.length)
        this.UserService.removeBannedUser(username)
      }
    }

    return true
  }
}