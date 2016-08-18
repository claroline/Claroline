/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/* global $ */
/* global Strophe */

/* global $pres */
/* global $msg */

(function () {
  'use strict'

  var xmppHost
  var boshPort
  var boshService
  var authenticatedUsername
  var password
  var username
  var connection
  var protocol

  function OnMessageStanza (stanza) {
    var from = $(stanza).attr('from')
    var sender = Strophe.getNodeFromJid(from)
    var body = $(stanza).find('body').text()
    var message = '<li><b class="received-message">' + sender + ' : </b>' + body + '</li>'
    $('#chat-content').append(message)
    var scrollHeight = $('#chat-content')[0].scrollHeight
    $('#chat-content').scrollTop(scrollHeight)

    return true
  }

  function OnPresenceStanza () {
    return true
  }

  function init () {
    xmppHost = $('#chat-datas-box').data('xmpp-host')
    boshPort = $('#chat-datas-box').data('bosh-port')
    protocol = $('#chat-datas-box').data('xmpp-ssl') ? 'https' : 'http'
    boshService = protocol + '://' + xmppHost + ':' + boshPort + '/http-bind'
    authenticatedUsername = $('#chat-datas-box').data('username')
    password = $('#chat-datas-box').data('password')
    username = $('#chat-datas-box').data('contact-username')

    connection = new Strophe.Connection(boshService)

    connection.connect(
      authenticatedUsername + '@' + xmppHost,
      password,
      connectionCallBack
    )
  }

  $('#send-msg-btn').on('click', function () {
    var msgContent = $('#msg-input').val()

    var message = $msg({
      to: username + '@' + xmppHost,
      from: authenticatedUsername + '@' + xmppHost,
      type: 'chat'
    }).c('body').t(msgContent)

    connection.send(message)
    $('#msg-input').val('')

    var display = '<li><b class="sent-message">' + authenticatedUsername + ' : </b>' + msgContent + '</li>'
    $('#chat-content').append(display)
    var scrollHeight = $('#chat-content')[0].scrollHeight
    $('#chat-content').scrollTop(scrollHeight)
  })

  $('#msg-input').on('keypress', function (e) {
    if (e.keyCode === 13) {
      var msgContent = $(this).val()

      var message = $msg({
        to: username + '@' + xmppHost,
        from: authenticatedUsername + '@' + xmppHost,
        type: 'chat'
      }).c('body').t(msgContent)

      connection.send(message)
      $('#msg-input').val('')

      var display = '<li><b class="sent-message">' + authenticatedUsername + ' : </b>' + msgContent + '</li>'
      $('#chat-content').append(display)
      var scrollHeight = $('#chat-content')[0].scrollHeight
      $('#chat-content').scrollTop(scrollHeight)
    }
  })

  var connectionCallBack = function (status) {
    if (status === Strophe.Status.CONNECTED) {
      connection.addHandler(OnPresenceStanza, null, 'presence')
      connection.addHandler(OnMessageStanza, null, 'message')
      connection.send($pres())
    } 
  }

  init()
})()
