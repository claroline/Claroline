/*global Routing*/
/*global Translator*/
import $ from 'jquery'

const userId = parseInt($('#user-sessions-datas-box').data('user-id'))
let sourceId
let documentId
let documentData = []

function initializeSelect(type)
{
  documentId = null
  $('#document-selection-select').empty()
  $('#document-selection-details').empty()
  $('#submit-document-selection').prop('disabled', true)
  $.ajax({
    url: Routing.generate('api_get_cursus_populated_document_models_by_type_for_user', {user: userId, type: type, sourceId: sourceId}),
    type: 'GET',
    success: function (data) {
      $('#document-selection-select').append('<option value="0" selected="selected"></option>')
      documentData = data
      data.forEach(d => {
        $('#document-selection-select').append(`<option value="${d['id']}">${d['name']}</option>`)
      })
      $('#document-selection-modal').modal('show')
    }
  })
}

$('#user-sessions-managament-body').on('click', '.delete-session-user-btn', function () {
  const sessionUserId = $(this).data('session-user-id')

  window.Claroline.Modal.confirmRequest(
    Routing.generate('api_delete_session_user', {'sessionUser': sessionUserId}),
    removeSessionRow,
    sessionUserId,
    Translator.trans('unregister_user_from_session_message', {}, 'cursus'),
    Translator.trans('unregister_user_from_session', {}, 'cursus')
  )
})

$('#user-sessions-managament-body').on('click', '.delete-session-event-user-btn', function () {
  const sessionEventUserId = $(this).data('session-event-user-id')

  window.Claroline.Modal.confirmRequest(
    Routing.generate('claro_cursus_session_event_unregister_user', {'sessionEventUser': sessionEventUserId}),
    removeSessionEventRow,
    sessionEventUserId,
    Translator.trans('unregister_user_from_session_event_message', {}, 'cursus'),
    Translator.trans('unregister_user_from_session_event', {}, 'cursus')
  )
})

$('#user-sessions-managament-body').on('click', '.send-session-invitation-btn', function () {
  sourceId = parseInt($(this).data('session-id'))
  $('#document-selection-title').html(Translator.trans('session_invitation', {}, 'cursus'))
  initializeSelect(0)
})

$('#user-sessions-managament-body').on('click', '.generate-session-certificate-btn', function () {
  sourceId = parseInt($(this).data('session-id'))
  $('#document-selection-title').html(Translator.trans('session_certificate', {}, 'cursus'))
  initializeSelect(2)
})

$('#user-sessions-managament-body').on('click', '.send-session-event-invitation-btn', function () {
  sourceId = parseInt($(this).data('session-event-id'))
  $('#document-selection-title').html(Translator.trans('session_event_invitation', {}, 'cursus'))
  initializeSelect(1)
})

$('#user-sessions-managament-body').on('click', '.generate-session-event-certificate-btn', function () {
  sourceId = parseInt($(this).data('session-event-id'))
  $('#document-selection-title').html(Translator.trans('session_event_certificate', {}, 'cursus'))
  initializeSelect(3)
})

$('#document-selection-select').on('change', function () {
  documentId = parseInt($(this).val())
  $('#document-selection-details').empty()
  $('#submit-document-selection').prop('disabled', true)

  if (documentId) {
    const documentModel = documentData.find(d => d['id'] === documentId)
    $('#document-selection-details').html(documentModel['content'])
    $('#submit-document-selection').prop('disabled', false)
  }
})

$('#submit-document-selection').on('click', function () {
  if (documentId > 0) {
    $.ajax({
      url: Routing.generate('api_post_cursus_document_for_user_send', {documentModel: documentId, user: userId, sourceId: sourceId}),
      type: 'POST',
      success: function () {
        $('#document-selection-modal').modal('hide')
      }
    })
  }
})

$('.session-event-registration-btn').on('click', function () {
  const sessionId = parseInt($(this).data('session-id'))
  const sessionName = $(this).data('session-name')
  $('#session-event-registration-title').html(sessionName)
  $.ajax({
    url: Routing.generate('claro_cursus_session_events_registration_management', {user: userId, session: sessionId}),
    type: 'GET',
    success: function (data) {
      $('#session-event-registration-body').html(data)
      $('#session-event-registration-modal').modal('show')
    }
  })
})

$('#session-event-registration-modal').on('click', '.register-user-to-session-event-btn', function () {
  const sessionEventId = parseInt($(this).data('session-event-id'))
  $.ajax({
    url: Routing.generate('api_post_session_event_user_registration', {sessionEvent: sessionEventId, user: userId}),
    type: 'POST',
    success: function (data) {
      if (data['status'] === 'success') {
        const successLabel = `<label class="label label-success">${Translator.trans('registered', {}, 'platform')}</label>`
        $(`.registration-button-${sessionEventId}`).html(successLabel)
        const eventRow = `
          <li id="event-row-${data['datas']['id']}">
              ${data['datas']['sessionEventName']}
              &nbsp;
              <span class="label label-primary pointer-hand send-session-event-invitation-btn"
                    data-session-event-id="${data['datas']['sessionEventId']}"
                    data-toggle="tooltip"
                    data-placement="top"
                    title="${Translator.trans('invite_to_session_event', {}, 'cursus')}"
              >
                  <i class="fa fa-plus-square"></i>
              </span>
              &nbsp;
              <span class="label label-primary pointer-hand generate-session-event-certificate-btn"
                    data-session-event-id="${data['datas']['sessionEventId']}"
                    data-toggle="tooltip"
                    data-placement="top"
                    title="${Translator.trans('generate_event_certificate', {}, 'cursus')}"
              >
                  <i class="fa fa-graduation-cap"></i>
              </span>
              &nbsp;
              <span class="label label-danger pointer-hand delete-session-event-user-btn"
                    data-session-event-user-id="${data['datas']['id']}"
                    data-toggle="tooltip"
                    data-placement="top"
                    title="${Translator.trans('unregister_user_from_session_event', {}, 'cursus')}"
              >
                  <i class="fa fa-times"></i>
              </span>
          </li>
        `
        $(`#events-0-${data['datas']['sessionId']}`).append(eventRow)
      } else if (data['status'] === 'failed') {
        const failedLabel = `<label class="label label-danger">${Translator.trans('no_more_place', {}, 'cursus')}</label>`
        $(`.registration-button-${sessionEventId}`).html(failedLabel)
      }
    }
  })
})

var removeSessionRow = function (event, sessionUserId) {
  $(`#session-row-${sessionUserId}`).remove()
}

var removeSessionEventRow = function (event, sessionEventUserId) {
  $(`#event-row-${sessionEventUserId}`).remove()
}