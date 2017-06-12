/*global Routing*/
/*global Translator*/
import $ from 'jquery'

let currentForwardBtn = null

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

$('#my-tickets-tab').on('click', '.forward-ticket-btn', function () {
  currentForwardBtn = $(this)
  const ticketId = $(this).data('ticket-id')
  window.Claroline.Modal.displayForm(
    Routing.generate('formalibre_admin_forwarded_ticket_create_form', {ticket: ticketId}),
    addForwardedTicket,
    () => {}
  )
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

const addForwardedTicket = function (data) {
  const nbForwarded = parseInt($('#forwarded-tickets-tab-badge').html())
  $('#forwarded-tickets-tab-badge').html(nbForwarded + 1)
  const url = Routing.generate('formalibre_admin_ticket_open', {ticket: data['forwardedId']})
  const linkBtn = `
    <a href="${url}"
       class="btn btn-default btn-sm"
       data-toggle="tooltip"
       title="${Translator.trans('forwarded_ticket', {}, 'support')}"
    >
        <i class="fa fa-asterisk"></i>
    </a>
  `
  currentForwardBtn.after(linkBtn)
  currentForwardBtn.remove()
  let forwardStatus = `${Translator.trans(data['status_name'], {}, 'support')}`

  if (data['status_description']) {
    forwardStatus += `
      <i class="fa fa-info-circle pointer-hand"
         data-toggle="tooltip"
         data-container="body"
         data-placement="top"
         data-html="true"
         title="${data['status_description']}"
      >
      </i>
    `
  }
  $(`#ticket-status-${data['id']}`).html(forwardStatus)
}