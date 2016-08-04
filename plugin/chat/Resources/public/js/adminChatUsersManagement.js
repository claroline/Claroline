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
 /* global Routing */
 /* global UserPicker */
 /* global Translator */

(function () {
  'use strict'

  var connection
  var protocol
  var xmppHost
  var boshPort
  var boshService
  var userDatas = null
  var username = ''
  var password = ''
  var userId = -1

  function xmppConnect () {
    xmppHost = $('#management-datas-box').data('xmpp-host')
    boshPort = $('#management-datas-box').data('bosh-port')
    protocol = $('#management-datas-box').data('xmpp-ssl') ? 'https' : 'http'
    boshService = protocol + '://' + xmppHost + ':' + boshPort + '/http-bind'
    connection = new Strophe.Connection(boshService)
  }

  function xmppRegistration () {
    connection.register.connect(
      xmppHost,
      registrationCallBack
    )
  }

  $('#search-chat-users-btn').on('click', function () {
    var search = $('#search-chat-users-input').val()
    var orderedBy = $('#management-datas-box').data('ordered-by')
    var order = $('#management-datas-box').data('order')
    var max = $('#management-datas-box').data('max')
    var route = Routing.generate(
      'claro_chat_users_admin_management',
      {
        'show': 1,
        'search': search,
        'max': max,
        'orderedBy': orderedBy,
        'order': order
      }
    )
    window.location.href = route
  })

  $('#search-chat-users-input').keypress(function (e) {
    if (e.keyCode === 13) {
      var search = $(this).val()
      var orderedBy = $('#management-datas-box').data('ordered-by')
      var order = $('#management-datas-box').data('order')
      var max = $('#management-datas-box').data('max')
      var route = Routing.generate(
        'claro_chat_users_admin_management',
        {
          'show': 1,
          'search': search,
          'max': max,
          'orderedBy': orderedBy,
          'order': order
        }
      )
      window.location.href = route
    }
  })

  $('#max-select').on('change', function () {
    var search = $('#management-datas-box').data('search')
    var orderedBy = $('#management-datas-box').data('ordered-by')
    var order = $('#management-datas-box').data('order')
    var max = $(this).val()
    var route = Routing.generate(
      'claro_chat_users_admin_management',
      {
        'show': 1,
        'search': search,
        'max': max,
        'orderedBy': orderedBy,
        'order': order
      }
    )
    window.location.href = route
  })

  $('.create-chat-users-btn').on('click', function () {
    var blackList = []

    $.ajax({
      url: Routing.generate('claro_chat_users_list', {type: 'id'}),
      type: 'GET',
      async: false,
      success: function (datas) {
        blackList = datas
      }
    })

    var userPicker = new UserPicker()
    var config = {
      multiple: false,
      picker_name: 'chat_users_selections',
      picker_title: Translator.trans(
        'select_users_for_chat_account_generation',
        {},
        'chat'
      ),
      show_all_users: true,
      blacklist: blackList,
      return_datas: true
    }
    userPicker.configure(config, registerUsers)
    userPicker.open()
  })

  var registerUsers = function (datas) {
    xmppConnect()

    for (var i = 0; i < datas.length; i++) {
      userDatas = datas[i]
      xmppRegistration()
    }
  }

  var registrationCallBack = function (status) {
    if (status === Strophe.Status.REGISTER) {
      if (userDatas !== null) {
        username = userDatas['username']
        password = userDatas['guid']
        userId = userDatas['id']
        connection.register.fields.username = username
        connection.register.fields.password = password
        connection.register.fields.name = userDatas['firstName'] +
        ' ' +
        userDatas['lastName']
        // calling submit will continue the registration process
        connection.register.submit()
      }
    } else if (status === Strophe.Status.REGISTERED) {
      $.ajax({
        url: Routing.generate(
          'claro_chat_user_create',
          {user: userId, username: username, password: password}
        ),
        type: 'POST',
        success: function () {
        }
      })
    }
  }

  $('.chat-user-edit-btn').on('click', function () {
    var chatUserId = $(this).data('chat-user-id')

    window.Claroline.Modal.displayForm(
      Routing.generate(
        'claro_chat_user_edit_form',
        {'chatUser': chatUserId}
      ),
      function () {
        window.location.reload()
      },
      function () {}
    )
  })

  $('body').on('focus', '#chat_user_edition_form_color', function () {
    $(this).colorpicker()
  })
})()
