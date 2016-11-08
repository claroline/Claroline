/*global Routing*/
/*global Translator*/
import $ from 'jquery'

$('.delete-session-group-btn').on('click', function () {
  const sessionGroupId = $(this).data('session-group-id')

  window.Claroline.Modal.confirmRequest(
    Routing.generate('claro_cursus_course_session_unregister_group', {'sessionGroup': sessionGroupId}),
    removeSessionRow,
    sessionGroupId,
    Translator.trans('unregister_group_from_session_message', {}, 'cursus'),
    Translator.trans('unregister_group_from_session', {}, 'cursus')
  )
})

var removeSessionRow = function (event, sessionGroupId) {
  $('#session-row-' + sessionGroupId).remove()
}