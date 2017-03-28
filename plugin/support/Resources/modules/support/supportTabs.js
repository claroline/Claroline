/*global Routing*/
import $ from 'jquery'

$('.close-ticket-tab-btn').on('click', function (e) {
  e.preventDefault()
  const ticketId = $(this).data('ticket-id')
  window.location.href = Routing.generate('formalibre_ticket_tab_close', {ticket: ticketId})
})