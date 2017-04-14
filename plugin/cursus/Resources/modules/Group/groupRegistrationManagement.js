/*global Routing*/
import $ from 'jquery'
    
var groupId = parseInt($('#registration-datas-box').data('group-id'))
var type = parseInt($('#registration-datas-box').data('type'))
var registeredSessionsIdsTxt = '' + $('#registration-datas-box').data('sessions-ids')
registeredSessionsIdsTxt = registeredSessionsIdsTxt.trim()
var registeredSessionsIds = (registeredSessionsIdsTxt === 'undefined' || registeredSessionsIdsTxt === '') ?
  [] :
  registeredSessionsIdsTxt.split(',')
var sessionsIds = []

function activateSubmitButton()
{
  if (sessionsIds.length > 0) {
    $('#register-sessions-btn').removeClass('disabled')
  } else {
    $('#register-sessions-btn').addClass('disabled')
  }
}

function checkRegisteredSessions()
{
  for (var i = 0; i < registeredSessionsIds.length; i++) {
    var sessionId = parseInt(registeredSessionsIds[i])
    $('#session-chk-'+ sessionId).prop('checked', true)
    $('#session-chk-'+ sessionId).prop('disabled', true)
  }
}

function checkSelection()
{
  for (var i = 0; i < sessionsIds.length; i++) {
    $('#session-chk-'+ sessionsIds[i]).prop('checked', true)
  }
}

function uncheckSession(sessionId)
{
  $('#session-chk-'+ sessionId).prop('checked', false)
}

function addSession(sessionId, sessionName, courseTitle, courseCode)
{
  for (var i = 0; i < sessionsIds.length; i++) {
    if (sessionsIds[i] === sessionId) {
      return false
    }
  }
  sessionsIds.push(sessionId)
  var element = '<div id="selected-session-element-' + sessionId + '">' +
    courseTitle + ' <small>[' +  courseCode + ']</small> - ' + sessionName +
    '&nbsp;&nbsp;<i class="fa fa-trash pointer-hand remove-session-btn" data-session-id="' + sessionId + '"></i>' +
    '</div>'
  $('#selected-sessions').append(element)
}

function removeSession(sessionId)
{
  for (var i = 0; i < sessionsIds.length; i++) {
    if (sessionsIds[i] === sessionId) {
      sessionsIds.splice(i, 1)
      break
    }
  }
  uncheckSession(sessionId)
  $('#selected-session-element-'+ sessionId).remove()
}

function refreshSessionsList(url)
{
  $.ajax({
    url: url,
    type: 'GET',
    success: function (result) {
      $('#sessions-list-box').html(result)
      checkRegisteredSessions()
      checkSelection()
    }
  })
}

$('#sessions-list-box').on('click', '.pagination a', function (event) {
  event.preventDefault()
  event.stopPropagation()
  var element = event.currentTarget
  var url = $(element).attr('href')
  refreshSessionsList(url)
})

$('#sessions-list-box').on('click', '.session-chk', function () {
  var sessionId = parseInt($(this).val())
  var courseTitle = $(this).data('course-title')
  var courseCode = $(this).data('course-code')
  var sessionName = $(this).data('session-name')
  var checked = $(this).prop('checked')

  if (checked) {
    addSession(sessionId, sessionName, courseTitle, courseCode)
  } else {
    removeSession(sessionId)
  }
  activateSubmitButton()
})

$('#search-sessions-btn').on('click', function () {
  var search = $('#search-sessions-input').val()
  var url = Routing.generate(
    'claro_cursus_sessions_datas_list',
    {'search': search}
  )
  refreshSessionsList(url)
})

$('#search-sessions-input').keypress(function (e) {
  if (e.keyCode === 13) {
    var search = $(this).val()
    var url = Routing.generate('claro_cursus_sessions_datas_list', {'search': search})
    refreshSessionsList(url)
  }
})

$('#register-sessions-btn').on('click', function () {
  var url = Routing.generate('claro_cursus_sessions_register_group', {group: groupId, type: type})
  var parameters = {}
  parameters.sessionsIds = sessionsIds
  url += '?' + $.param(parameters)
  window.location = url
})

$('#selected-sessions').on('click', '.remove-session-btn', function () {
  var sessionId = parseInt($(this).data('session-id'))
  removeSession(sessionId)
})

checkRegisteredSessions()