/*global Routing*/
import $ from 'jquery'

$('#my-tickets-tab').on('click', '.delete-ticket-btn', function () {
  const ticketId = $(this).data('ticket-id')

  $.ajax({
    url: Routing.generate('formalibre_admin_my_ticket_remove', {'ticket': ticketId}),
    type: 'DELETE',
    success: function (ticketId) {
      removeTicket(ticketId)
    }
  })
})

$('#my-tickets-tab').on('click', '.archive-ticket-btn', function () {
  const ticketId = $(this).data('ticket-id')

  $.ajax({
    url: Routing.generate('formalibre_ticket_closing', {'ticket': ticketId}),
    type: 'POST',
    success: function (ticketId) {
      closeTicket(ticketId)
    }
  })
})

const removeTicket = function (ticketId) {
  const nbMyTickets = parseInt($('#my-tickets-tab-badge').html())
  $('#my-tickets-tab-badge').html(nbMyTickets - 1)
  $(`#row-ticket-${ticketId}`).remove()
  $(`#ticket-tab-${ticketId}`).remove()
}

const closeTicket = function (ticketId) {
  const nbMyTickets = parseInt($('#my-tickets-tab-badge').html())
  const nbArchives = parseInt($('#archives-tab-badge').html())
  $('#my-tickets-tab-badge').html(nbMyTickets - 1)
  $('#archives-tab-badge').html(nbArchives + 1)
  $(`#row-ticket-${ticketId}`).remove()
}