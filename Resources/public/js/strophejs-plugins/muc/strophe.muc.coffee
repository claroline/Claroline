###
 *Plugin to implement the MUC extension.
   http://xmpp.org/extensions/xep-0045.html
 *Previous Author:
    Nathan Zorn <nathan.zorn@gmail.com>
 *Complete CoffeeScript rewrite:
    Andreas Guth <guth@dbis.rwth-aachen.de>
###

Strophe.addConnectionPlugin 'muc',
  _connection: null
  rooms: {}
  roomNames: []

  ###Function
  Initialize the MUC plugin. Sets the correct connection object and
  extends the namesace.
  ###
  init: (conn) ->
    @_connection = conn
    @_muc_handler = null
    # extend name space
    #   NS.MUC - XMPP Multi-user chat namespace from XEP 45.
    Strophe.addNamespace 'MUC_OWNER',     Strophe.NS.MUC+"#owner"
    Strophe.addNamespace 'MUC_ADMIN',     Strophe.NS.MUC+"#admin"
    Strophe.addNamespace 'MUC_USER',      Strophe.NS.MUC+"#user"
    Strophe.addNamespace 'MUC_ROOMCONF',  Strophe.NS.MUC+"#roomconfig"
    Strophe.addNamespace 'MUC_REGISTER', "jabber:iq:register"

  ###Function
  Join a multi-user chat room
  Parameters:
  (String) room - The multi-user chat room to join.
  (String) nick - The nickname to use in the chat room. Optional
  (Function) msg_handler_cb - The function call to handle messages from the
  specified chat room.
  (Function) pres_handler_cb - The function call back to handle presence
  in the chat room.
  (Function) roster_cb - The function call to handle roster info in the chat room
  (String) password - The optional password to use. (password protected
  rooms only)
  (Object) history_attrs - Optional attributes for retrieving history
  (XML DOM Element) extended_presence - Optional XML for extending presence
  ###
  join: (room, nick, msg_handler_cb, pres_handler_cb, roster_cb, password, history_attrs, extended_presence) ->
    room_nick = @test_append_nick(room, nick)
    msg = $pres(
      from: @_connection.jid
      to: room_nick )
    .c("x", xmlns: Strophe.NS.MUC)

    if history_attrs?
      msg = msg.c("history", history_attrs).up()

    if password?
      msg.cnode Strophe.xmlElement("password", [], password)

    if extended_presence?
      msg.up().cnode extended_presence

    # One handler for all rooms that dispatches to room callbacks
    @_muc_handler ?=  @_connection.addHandler (stanza) =>
      from = stanza.getAttribute 'from'
      return true unless from
      roomname = from.split("/")[0]

      # Abort if the stanza is not for a known MUC
      return true unless @rooms[roomname]
      room = @rooms[roomname]

      handlers = {}

      #select the right handlers
      if stanza.nodeName is "message"
        handlers = room._message_handlers
      else if stanza.nodeName is "presence"
        xquery = stanza.getElementsByTagName "x"
        if xquery.length > 0
          # Handle only MUC user protocol
          for x in xquery
            xmlns = x.getAttribute "xmlns"
            if xmlns and xmlns.match Strophe.NS.MUC
              handlers = room._presence_handlers
              break

      # loop over selected handlers (if any) and remove on false
      for id, handler of handlers
        delete handlers[id] unless handler stanza, room

      return true

    unless @rooms.hasOwnProperty(room)
      @rooms[room] = new XmppRoom(@, room, nick, password )
      @rooms[room].addHandler 'presence', pres_handler_cb if pres_handler_cb
      @rooms[room].addHandler 'message', msg_handler_cb if msg_handler_cb
      @rooms[room].addHandler 'roster', roster_cb if roster_cb
      @roomNames.push room

    @_connection.send msg

  ###Function
  Leave a multi-user chat room
  Parameters:
  (String) room - The multi-user chat room to leave.
  (String) nick - The nick name used in the room.
  (Function) handler_cb - Optional function to handle the successful leave.
  (String) exit_msg - optional exit message.
  Returns:
  iqid - The unique id for the room leave.
  ###
  leave: (room, nick, handler_cb, exit_msg) ->
    id = @roomNames.indexOf room
    delete @rooms[room]
    if id >=0
      @roomNames.splice id, 1
      if @roomNames.length is 0
        @_connection.deleteHandler @_muc_handler
        @_muc_handler = null
    room_nick = @test_append_nick room, nick
    presenceid = @_connection.getUniqueId()
    presence = $pres (
      type: "unavailable"
      id: presenceid
      from: @_connection.jid
      to: room_nick )

    presence.c "status", exit_msg if exit_msg?

    if handler_cb?
      @_connection.addHandler(
        handler_cb
        null
        "presence"
        null
        presenceid )

    @_connection.send presence
    return presenceid

  ###Function
  Parameters:
  (String) room - The multi-user chat room name.
  (String) nick - The nick name used in the chat room.
  (String) message - The plaintext message to send to the room.
  (String) html_message - The message to send to the room with html markup.
  (String) type - "groupchat" for group chat messages o
                  "chat" for private chat messages
  Returns:
  msgiq - the unique id used to send the message
  ###
  message: (room, nick, message, html_message, type, msgid) ->
    room_nick = @test_append_nick(room, nick)
    type = type or if nick? then "chat" else "groupchat"
    msgid = msgid or @_connection.getUniqueId()
    msg = $msg(
      to: room_nick
      from: @_connection.jid
      type: type
      id: msgid )
    .c("body")
    .t(message)
    msg.up()
    if html_message?
      msg.c("html", xmlns: Strophe.NS.XHTML_IM)
      .c("body", xmlns: Strophe.NS.XHTML)
      .h(html_message)
      if msg.node.childNodes.length is 0
        # html creation or import failed somewhere; fallback to plaintext
        parent = msg.node.parentNode
        msg.up().up()
        # get rid of the empty html element if we got invalid html
        #so we don't send an empty message
        msg.node.removeChild parent
      else
        msg.up().up()
    msg.c("x", xmlns: "jabber:x:event").c("composing")
    @_connection.send msg
    return msgid

  ###Function
  Convenience Function to send a Message to all Occupants
  Parameters:
  (String) room - The multi-user chat room name.
  (String) message - The plaintext message to send to the room.
  (String) html_message - The message to send to the room with html markup.
  (String) msgid - Optional unique ID which will be set as the 'id' attribute of the stanza
  Returns:
  msgiq - the unique id used to send the message
  ###
  groupchat: (room, message, html_message, msgid) ->
    @message room, null, message, html_message, undefined, msgid

  ###Function
  Send a mediated invitation.
  Parameters:
  (String) room - The multi-user chat room name.
  (String) receiver - The invitation's receiver.
  (String) reason - Optional reason for joining the room.
  Returns:
  msgiq - the unique id used to send the invitation
  ###
  invite: (room, receiver, reason) ->
    msgid = @_connection.getUniqueId()
    invitation = $msg(
      from: @_connection.jid
      to: room
      id: msgid )
    .c('x', xmlns: Strophe.NS.MUC_USER)
    .c('invite', to: receiver)
    invitation.c 'reason', reason if reason?
    @_connection.send invitation
    return msgid

  ###Function
  Send a mediated multiple invitation.
  Parameters:
  (String) room - The multi-user chat room name.
  (Array) receivers - The invitation's receivers.
  (String) reason - Optional reason for joining the room.
  Returns:
  msgiq - the unique id used to send the invitation
  ###
  multipleInvites: (room, receivers, reason) ->
    msgid = @_connection.getUniqueId()
    invitation = $msg(
      from: @_connection.jid
      to: room
      id: msgid )
    .c('x', xmlns: Strophe.NS.MUC_USER)

    for receiver in receivers
      invitation.c 'invite', to: receiver
      if reason?
        invitation.c 'reason', reason
        invitation.up()
      invitation.up()

    @_connection.send invitation
    return msgid

  ###Function
  Send a direct invitation.
  Parameters:
  (String) room - The multi-user chat room name.
  (String) receiver - The invitation's receiver.
  (String) reason - Optional reason for joining the room.
  (String) password - Optional password for the room.
  Returns:
  msgiq - the unique id used to send the invitation
  ###
  directInvite: (room, receiver, reason, password) ->
    msgid = @_connection.getUniqueId()
    attrs =
      xmlns: 'jabber:x:conference'
      jid: room
    attrs.reason = reason if reason?
    attrs.password = password if password?
    invitation = $msg(
      from: @_connection.jid
      to: receiver
      id: msgid )
    .c('x', attrs)
    @_connection.send invitation
    return msgid

  ###Function
  Queries a room for a list of occupants
  (String) room - The multi-user chat room name.
  (Function) success_cb - Optional function to handle the info.
  (Function) error_cb - Optional function to handle an error.
  Returns:
  id - the unique id used to send the info request
  ###
  queryOccupants: (room, success_cb, error_cb) ->
    attrs = xmlns: Strophe.NS.DISCO_ITEMS
    info = $iq(
      from:this._connection.jid
      to:room
      type:'get' )
    .c('query', attrs)
    @_connection.sendIQ info, success_cb, error_cb

  ###Function
  Start a room configuration.
  Parameters:
  (String) room - The multi-user chat room name.
  (Function) handler_cb - Optional function to handle the config form.
  Returns:
  id - the unique id used to send the configuration request
  ###
  configure: (room, handler_cb, error_cb) ->
    # send iq to start room configuration
    config = $iq(
      to:room
      type: "get" )
    .c("query", xmlns: Strophe.NS.MUC_OWNER)
    stanza = config.tree()
    @_connection.sendIQ stanza, handler_cb, error_cb

  ###Function
  Cancel the room configuration
  Parameters:
  (String) room - The multi-user chat room name.
  Returns:
  id - the unique id used to cancel the configuration.
  ###
  cancelConfigure: (room) ->
    #send iq to start room configuration
    config = $iq(
      to: room
      type: "set" )
    .c("query", xmlns: Strophe.NS.MUC_OWNER)
    .c("x", xmlns: "jabber:x:data", type: "cancel")
    stanza = config.tree()
    @_connection.sendIQ stanza

  ###Function
  Save a room configuration.
  Parameters:
  (String) room - The multi-user chat room name.
  (Array) config- Form Object or an array of form elements used to configure the room.
  Returns:
  id - the unique id used to save the configuration.
  ###
  saveConfiguration: (room, config, success_cb, error_cb) ->
    iq = $iq(
      to: room
      type: "set" )
    .c("query", xmlns: Strophe.NS.MUC_OWNER)
    if typeof Strophe.x isnt "undefined" and typeof Strophe.x.Form isnt "undefined" and config instanceof Strophe.x.Form
      config.type = "submit"
      iq.cnode config.toXML()
    else
      iq.c("x", xmlns: "jabber:x:data", type: "submit")
      iq.cnode(conf).up() for conf in config
    stanza = iq.tree()
    @_connection.sendIQ stanza, success_cb, error_cb

  ###Function
  Parameters:
  (String) room - The multi-user chat room name.
  Returns:
  id - the unique id used to create the chat room.
  ###
  createInstantRoom: (room, success_cb, error_cb) ->
    roomiq = $iq(
      to: room
      type: "set" )
    .c("query", xmlns: Strophe.NS.MUC_OWNER)
    .c("x", xmlns: "jabber:x:data", type: "submit")
    @_connection.sendIQ roomiq.tree(), success_cb, error_cb

  ###Function
  Parameters:
  (String) room - The multi-user chat room name.
  (Object) config - the configuration. ex: {"muc#roomconfig_publicroom": "0", "muc#roomconfig_persistentroom": "1"}
  Returns:
  id - the unique id used to create the chat room.
  ###
  createConfiguredRoom: (room, config, success_cb, error_cb) ->
    roomiq = $iq(
      to: room
      type: "set" )
    .c("query", xmlns: Strophe.NS.MUC_OWNER)
    .c("x", xmlns: "jabber:x:data", type: "submit")

    # Owner submits configuration form
    roomiq.c('field', { 'var': 'FORM_TYPE' }).c('value').t('http://jabber.org/protocol/muc#roomconfig').up().up();

    roomiq.c('field', { 'var': k}).c('value').t(v).up().up() for own k, v of config

    @_connection.sendIQ roomiq.tree(), success_cb, error_cb

  ###Function
  Set the topic of the chat room.
  Parameters:
  (String) room - The multi-user chat room name.
  (String) topic - Topic message.
  ###
  setTopic: (room, topic) ->
    msg = $msg(
      to: room
      from: @_connection.jid
      type: "groupchat" )
    .c("subject", xmlns: "jabber:client")
    .t(topic)
    @_connection.send msg.tree()

  ###Function
  Internal Function that Changes the role or affiliation of a member
  of a MUC room. This function is used by modifyRole and modifyAffiliation.
  The modification can only be done by a room moderator. An error will be
  returned if the user doesn't have permission.
  Parameters:
  (String) room - The multi-user chat room name.
  (Object) item - Object with nick and role or jid and affiliation attribute
  (String) reason - Optional reason for the change.
  (Function) handler_cb - Optional callback for success
  (Function) error_cb - Optional callback for error
  Returns:
  iq - the id of the mode change request.
  ###
  _modifyPrivilege: (room, item, reason, handler_cb, error_cb) ->
    iq = $iq(
      to: room
      type: "set" )
    .c("query", xmlns: Strophe.NS.MUC_ADMIN)
    .cnode(item.node)

    iq.c("reason", reason) if reason?

    @_connection.sendIQ iq.tree(), handler_cb, error_cb

  ###Function
  Changes the role of a member of a MUC room.
  The modification can only be done by a room moderator. An error will be
  returned if the user doesn't have permission.
  Parameters:
  (String) room - The multi-user chat room name.
  (String) nick - The nick name of the user to modify.
  (String) role - The new role of the user.
  (String) affiliation - The new affiliation of the user.
  (String) reason - Optional reason for the change.
  (Function) handler_cb - Optional callback for success
  (Function) error_cb - Optional callback for error
  Returns:
  iq - the id of the mode change request.
  ###
  modifyRole: (room, nick, role, reason, handler_cb, error_cb) ->
    item = $build("item"
      nick: nick
      role: role )

    @_modifyPrivilege room, item, reason, handler_cb, error_cb

  kick: (room, nick, reason, handler_cb, error_cb) ->
    @modifyRole room, nick, 'none', reason, handler_cb, error_cb

  voice: (room, nick, reason, handler_cb, error_cb) ->
    @modifyRole room, nick, 'participant', reason, handler_cb, error_cb

  mute: (room, nick, reason, handler_cb, error_cb) ->
    @modifyRole room, nick, 'visitor', reason, handler_cb, error_cb

  op: (room, nick, reason, handler_cb, error_cb) ->
    @modifyRole room, nick, 'moderator', reason, handler_cb, error_cb

  deop: (room, nick, reason, handler_cb, error_cb) ->
    @modifyRole room, nick, 'participant', reason, handler_cb, error_cb

  ###Function
  Changes the affiliation of a member of a MUC room.
  The modification can only be done by a room moderator. An error will be
  returned if the user doesn't have permission.
  Parameters:
  (String) room - The multi-user chat room name.
  (String) jid  - The jid of the user to modify.
  (String) affiliation - The new affiliation of the user.
  (String) reason - Optional reason for the change.
  (Function) handler_cb - Optional callback for success
  (Function) error_cb - Optional callback for error
  Returns:
  iq - the id of the mode change request.
  ###
  modifyAffiliation: (room, jid, affiliation, reason, handler_cb, error_cb) ->
    item = $build("item"
      jid: jid
      affiliation: affiliation )

    @_modifyPrivilege room, item, reason, handler_cb, error_cb

  ban: (room, jid, reason, handler_cb, error_cb) ->
    @modifyAffiliation room, jid, 'outcast', reason, handler_cb, error_cb

  member: (room, jid, reason, handler_cb, error_cb) ->
    @modifyAffiliation room, jid, 'member', reason, handler_cb, error_cb

  revoke: (room, jid, reason, handler_cb, error_cb) ->
    @modifyAffiliation room, jid, 'none', reason, handler_cb, error_cb

  owner: (room, jid, reason, handler_cb, error_cb) ->
    @modifyAffiliation room, jid, 'owner', reason, handler_cb, error_cb

  admin: (room, jid, reason, handler_cb, error_cb) ->
    @modifyAffiliation room, jid, 'admin', reason, handler_cb, error_cb

  ###Function
  Change the current users nick name.
  Parameters:
  (String) room - The multi-user chat room name.
  (String) user - The new nick name.
  ###
  changeNick: (room, user) ->
    room_nick = @test_append_nick room, user
    presence = $pres(
      from: @_connection.jid
      to: room_nick
      id: @_connection.getUniqueId() )
    @_connection.send presence.tree()

  ###Function
  Change the current users status.
  Parameters:
  (String) room - The multi-user chat room name.
  (String) user - The current nick.
  (String) show - The new show-text.
  (String) status - The new status-text.
  ###
  setStatus: (room, user, show, status) ->
    room_nick = @test_append_nick room, user
    presence = $pres(
      from: @_connection.jid
      to: room_nick )
    presence.c('show', show).up() if show?
    presence.c('status', status) if status?
    @_connection.send presence.tree()

  ###Function
  Registering with a room.
  @see http://xmpp.org/extensions/xep-0045.html#register
  Parameters:
  (String) room - The multi-user chat room name.
  (Function) handle_cb - Function to call for room list return.
  (Function) error_cb - Function to call on error.
  ###
  registrationRequest: (room, handle_cb, error_cb) ->
    iq = $iq(
        to: room,
        from: @_connection.jid,
        type: "get"
      )
    .c("query", xmlns: Strophe.NS.MUC_REGISTER)

    @_connection.sendIQ iq, (stanza) ->
      $fields = stanza.getElementsByTagName 'field'
      length = $fields.length
      fields =
        required: []
        optional: []

      for $field in $fields
        field =
          var: $field.getAttribute 'var'
          label: $field.getAttribute 'label'
          type: $field.getAttribute 'type'

        if $field.getElementsByTagName('required').length > 0
          fields.required.push field
        else
          fields.optional.push field

      handle_cb fields
    , error_cb

  ###Function
  Submits registration form.
  Parameters:
  (String) room - The multi-user chat room name.
  (Function) handle_cb - Function to call for room list return.
  (Function) error_cb - Function to call on error.
  ###
  submitRegistrationForm: (room, fields, handle_cb, error_cb) ->
    iq = $iq({
      to: room,
      type: "set"
    }).c("query", xmlns: Strophe.NS.MUC_REGISTER);
    iq.c("x",
      xmlns: "jabber:x:data",
      type: "submit"
    );
    iq.c('field', 'var': 'FORM_TYPE')
    .c('value')
    .t('http://jabber.org/protocol/muc#register')
    .up().up()

    for key, val of fields
      iq.c('field', 'var': key)
      .c('value')
      .t(val).up().up()

    @._connection.sendIQ iq, handle_cb, error_cb

  ###Function
  List all chat room available on a server.
  Parameters:
  (String) server - name of chat server.
  (String) handle_cb - Function to call for room list return.
  (String) error_cb - Function to call on error.
  ###
  listRooms: (server, handle_cb, error_cb) ->
    iq = $iq(
      to: server
      from: @_connection.jid
      type: "get" )
    .c("query", xmlns: Strophe.NS.DISCO_ITEMS)
    @_connection.sendIQ iq, handle_cb, error_cb

  test_append_nick: (room, nick) ->
    node = Strophe.escapeNode(Strophe.getNodeFromJid(room))
    domain = Strophe.getDomainFromJid(room)
    node + "@" + domain + if nick? then "/#{nick}" else ""

class XmppRoom


  constructor: (@client, @name, @nick, @password) ->
    @roster = {}
    @_message_handlers = {}
    @_presence_handlers = {}
    @_roster_handlers = {}
    @_handler_ids = 0
    @client = @client.muc if @client.muc
    @name = Strophe.getBareJidFromJid name
    @addHandler 'presence', @_roomRosterHandler

  join: (msg_handler_cb, pres_handler_cb, roster_cb) ->
    @client.join(@name, @nick, msg_handler_cb, pres_handler_cb, roster_cb, @password)

  leave: (handler_cb, message) ->
    @client.leave @name, @nick, handler_cb, message
    delete @client.rooms[@name]

  message: (nick, message, html_message, type) ->
    @client.message @name, nick, message, html_message, type

  groupchat: (message, html_message) ->
    @client.groupchat @name, message, html_message

  invite: (receiver, reason) ->
    @client.invite @name, receiver, reason

  multipleInvites: (receivers, reason) ->
    @client.invite @name, receivers, reason

  directInvite: (receiver, reason) ->
    @client.directInvite @name, receiver, reason, @password

  configure: (handler_cb) ->
    @client.configure @name, handler_cb

  cancelConfigure: ->
    @client.cancelConfigure @name

  saveConfiguration: (config) ->
    @client.saveConfiguration @name, config

  queryOccupants: (success_cb, error_cb) ->
    @client.queryOccupants @name, success_cb, error_cb

  setTopic: (topic) ->
    @client.setTopic @name, topic

  modifyRole: (nick, role, reason, success_cb, error_cb) ->
    @client.modifyRole @name, nick, role, reason, success_cb, error_cb

  kick: (nick, reason, handler_cb, error_cb) ->
    @client.kick @name, nick, reason, handler_cb, error_cb

  voice: (nick, reason, handler_cb, error_cb) ->
    @client.voice @name, nick, reason, handler_cb, error_cb

  mute: (nick, reason, handler_cb, error_cb) ->
    @client.mute @name, nick, reason, handler_cb, error_cb

  op: (nick, reason, handler_cb, error_cb) ->
    @client.op @name, nick, reason, handler_cb, error_cb

  deop: (nick, reason, handler_cb, error_cb) ->
    @client.deop @name, nick, reason, handler_cb, error_cb

  modifyAffiliation: (jid, affiliation, reason, success_cb, error_cb) ->
    @client.modifyAffiliation @name,
      jid, affiliation, reason,
      success_cb, error_cb

  ban: (jid, reason, handler_cb, error_cb) ->
    @client.ban @name, jid, reason, handler_cb, error_cb

  member: (jid, reason, handler_cb, error_cb) ->
    @client.member @name, jid, reason, handler_cb, error_cb

  revoke: (jid, reason, handler_cb, error_cb) ->
    @client.revoke @name, jid, reason, handler_cb, error_cb

  owner: (jid, reason, handler_cb, error_cb) ->
    @client.owner @name, jid, reason, handler_cb, error_cb

  admin: (jid, reason, handler_cb, error_cb) ->
    @client.admin @name, jid, reason, handler_cb, error_cb

  changeNick: (@nick) ->
    @client.changeNick @name, nick

  setStatus: (show, status) ->
    @client.setStatus @name, @nick, show, status

  ###Function
  Adds a handler to the MUC room.
    Parameters:
  (String) handler_type - 'message', 'presence' or 'roster'.
  (Function) handler - The handler function.
  Returns:
  id - the id of handler.
  ###
  addHandler: (handler_type, handler) ->
    id = @_handler_ids++
    switch handler_type
      when 'presence'
        @_presence_handlers[id] = handler
      when 'message'
        @_message_handlers[id] = handler
      when 'roster'
        @_roster_handlers[id] = handler
      else
        @_handler_ids--
        return null
    id

  ###Function
  Removes a handler from the MUC room.
  This function takes ONLY ids returned by the addHandler function
  of this room. passing handler ids returned by connection.addHandler
  may brake things!
    Parameters:
  (number) id - the id of the handler
  ###
  removeHandler: (id) ->
    delete @_presence_handlers[id]
    delete @_message_handlers[id]
    delete @_roster_handlers[id]

  ###Function
  Creates and adds an Occupant to the Room Roster.
    Parameters:
  (Object) data - the data the Occupant is filled with
  Returns:
  occ - the created Occupant.
  ###
  _addOccupant: (data) =>
    occ = new Occupant data, @
    @roster[occ.nick] = occ
    occ

  ###Function
  The standard handler that managed the Room Roster.
    Parameters:
  (Object) pres - the presence stanza containing user information
  ###
  _roomRosterHandler: (pres) =>
    data = XmppRoom._parsePresence pres
    nick = data.nick
    newnick = data.newnick or null
    switch data.type
      when 'error' then return true
      when 'unavailable'
        if newnick
          data.nick = newnick
          # If both Occupant Instances exist, switch the new one
          # with the old renamed one
          if @roster[nick] and @roster[newnick]
            @roster[nick].update @roster[newnick]
            @roster[newnick] = @roster[nick]
          # If the renamed Occupant doesn't exist yet but the old one does,
          # let the new one be the Same instance
          if @roster[nick] and not @roster[newnick]
            @roster[newnick] = @roster[nick].update data
          # If the old Occupant is already deleted, do nothing
          # unless @roster[newnick]
          #   tmp_occ = @roster[newnick]
          #   @roster[newnick].update(data).update(tmp_occ)
        delete @roster[nick]
      else
        if @roster[nick]
          @roster[nick].update data
        else
          @_addOccupant data
    for id, handler of @_roster_handlers
      delete @_roster_handlers[id] unless handler @roster, @
    true

  ###Function
  Parses a presence stanza
    Parameters:
  (Object) data - the data extracted from the presence stanza
  ###
  @_parsePresence: (pres) ->
    data = {}
    data.nick = Strophe.getResourceFromJid pres.getAttribute("from")
    data.type = pres.getAttribute("type")
    data.states = []
    for c in pres.childNodes
      switch c.nodeName
        when "status"
          data.status = c.textContent or null
        when "show"
          data.show = c.textContent or null
        when "x"
          if c.getAttribute("xmlns") is Strophe.NS.MUC_USER
            for c2 in c.childNodes
              switch c2.nodeName
                when "item"
                  data.affiliation = c2.getAttribute("affiliation")
                  data.role = c2.getAttribute("role")
                  data.jid = c2.getAttribute("jid")
                  data.newnick = c2.getAttribute("nick")
                when "status"
                  if c2.getAttribute("code")
                    data.states.push c2.getAttribute("code")
    data

class RoomConfig

  constructor: (info) ->
    @parse info if info?

  parse: (result) =>
    query = result.getElementsByTagName("query")[0].childNodes
    @identities =  []
    @features =  []
    @x = []
    for child in query
      attrs = child.attributes
      switch child.nodeName
        when "identity"
          identity = {}
          identity[attr.name] = attr.textContent for attr in attrs
          @identities.push identity
        when "feature"
          @features.push child.getAttribute("var")
        when "x"
          break if (
            (not child.childNodes[0].getAttribute("var") is 'FORM_TYPE') or
            (not child.childNodes[0].getAttribute("type") is 'hidden') )
          for field in child.childNodes when not field.attributes.type
            @x.push (
              var: field.getAttribute("var")
              label: field.getAttribute("label") or ""
              value: field.firstChild.textContent or "" )

    "identities": @identities, "features": @features, "x": @x

class Occupant
  constructor: (data, @room) ->
    @update data

  modifyRole: (role, reason, success_cb, error_cb) =>
    @room.modifyRole @nick, role, reason, success_cb, error_cb

  kick: (reason, handler_cb, error_cb) =>
    @room.kick @nick, reason, handler_cb, error_cb

  voice: (reason, handler_cb, error_cb) =>
    @room.voice @nick, reason, handler_cb, error_cb

  mute: (reason, handler_cb, error_cb) =>
    @room.mute @nick, reason, handler_cb, error_cb

  op: (reason, handler_cb, error_cb) =>
    @room.op @nick, reason, handler_cb, error_cb

  deop: (reason, handler_cb, error_cb) =>
    @room.deop @nick, reason, handler_cb, error_cb

  modifyAffiliation: (affiliation, reason, success_cb, error_cb) =>
    @room.modifyAffiliation @jid, affiliation, reason, success_cb, error_cb

  ban: (reason, handler_cb, error_cb) =>
    @room.ban @jid, reason, handler_cb, error_cb

  member: (reason, handler_cb, error_cb) =>
    @room.member @jid, reason, handler_cb, error_cb

  revoke: (reason, handler_cb, error_cb) =>
    @room.revoke @jid, reason, handler_cb, error_cb

  owner: (reason, handler_cb, error_cb) =>
    @room.owner @jid, reason, handler_cb, error_cb

  admin: (reason, handler_cb, error_cb) =>
    @room.admin @jid, reason, handler_cb, error_cb

  update: (data) =>
    @nick         = data.nick         or null
    @affiliation  = data.affiliation  or null
    @role         = data.role         or null
    @jid          = data.jid          or null
    @status       = data.status       or null
    @show         = data.show         or null
    @

