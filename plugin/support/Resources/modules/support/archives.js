/*global Routing*/
/*global Translator*/
import $ from 'jquery'

$('#archives-tab').on('click', '.delete-ticket-btn', function () {
  const ticketId = $(this).data('ticket-id')

  window.Claroline.Modal.confirmRequest(
    Routing.generate('formalibre_ticket_delete', {'ticket': ticketId}),
    removeTicket,
    ticketId,
    Translator.trans('ticket_deletion_confirm_message', {}, 'support'),
    Translator.trans('ticket_deletion', {}, 'support')
  )
})

const removeTicket = function (event, ticketId) {
  const nbArchives = parseInt($('#archives-tab-badge').html())
  $('#archives-tab-badge').html(nbArchives - 1)
  $(`#row-ticket-${ticketId}`).remove()
  $(`#ticket-tab-${ticketId}`).remove()
}