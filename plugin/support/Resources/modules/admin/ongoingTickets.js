/*global Routing*/
import $ from 'jquery'

$('#ongoing-tickets-tab').on('click', '.archive-ticket-btn', function () {
  const ticketId = $(this).data('ticket-id')

  $.ajax({
    url: Routing.generate('formalibre_ticket_closing', {'ticket': ticketId}),
    type: 'POST',
    success: function (ticketId) {
      closeTicket(ticketId)
    }
  })
})

const closeTicket = function (ticketId) {
  const nbOngoing = parseInt($('#ongoing-tickets-tab-badge').html())
  const nbArchives = parseInt($('#archives-tab-badge').html())
  $('#ongoing-tickets-tab-badge').html(nbOngoing - 1)
  $('#archives-tab-badge').html(nbArchives + 1)
  $(`#row-ticket-${ticketId}`).remove()
}