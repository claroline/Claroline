/*global Routing*/
import $ from 'jquery'

$('.close-ticket-tab-btn').on('click', function (e) {
  e.preventDefault()
  const ticketUserId = $(this).data('ticket-user-id')
  window.location.href = Routing.generate('formalibre_admin_ticket_user_close', {ticketUser: ticketUserId})
})