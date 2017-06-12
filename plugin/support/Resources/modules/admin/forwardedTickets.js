/*global Routing*/
/*global Translator*/
import $ from 'jquery'

$('#forwarded-tickets-tab').on('click', '.cancel-ticket-btn', function () {
  const ticketId = $(this).data('ticket-id')

  $.ajax({
    url: Routing.generate('formalibre_admin_forwarded_ticket_remove', {'ticket': ticketId}),
    type: 'DELETE',
    success: function (ticketId) {
      removeTicket(ticketId)
    }
  })
})

$('#create-ticket-btn').on('click', function () {
  window.Claroline.Modal.displayForm(
    Routing.generate('formalibre_admin_ticket_create_form'),
    addTicket,
    () => {}
  )
})

const addTicket = function (data) {
  const ticket = `
    <tr id="row-ticket-${data['id']}">
        <td>
            <a href="${Routing.generate('formalibre_admin_ticket_open', {ticket: data['id']})}">
                <span class="ticket-title">
                    ${data['title']}
                </span>
            </a>
        </td>
        <td>
          ${data['user']['firstName']}
          ${data['user']['lastName']}
        </td>
        <td>
            ${data['creationDate']}
        </td>
        <td class="ticket-type">
            ${Translator.trans(data['typeName'], {}, 'support')}
            ${data['typeDescription'] ? `
              <i class="fa fa-info-circle pointer-hand"
                 data-toggle="tooltip"
                 data-container="body"
                 data-placement="top"
                 data-html="true"
                 title="${data['typeDescription']}"
              >
              </i>` :
              ''
            }
        </td>
        <td class="ticket-status">
            ${Translator.trans(data['statusName'], {}, 'support')}
            ${data['statusDescription'] ? `
              <i class="fa fa-info-circle pointer-hand"
                 data-toggle="tooltip"
                 data-container="body"
                 data-placement="top"
                 data-html="true"
                 title="${data['statusDescription']}"
              >
              </i>` :
              ''
            }
        </td>
        <td class="text-center">
            <button class="btn btn-default btn-sm disabled"
                    data-toggle="tooltip"
                    title="${Translator.trans('no_linked_ticket', {}, 'support')}"
            >
                <i class="fa fa-asterisk"></i>
            </button>
            <button class="btn btn-default cancel-ticket-btn btn-sm"
                    data-ticket-id="${data['id']}"
                    data-toggle="tooltip"
                    title="${Translator.trans('cancel_support_request', {}, 'support')}"
            >
                <i class="fa fa-times-circle"></i>
            </button>
        </td>
    </tr>
  `
  $('#no-forwarded-ticket-alert').hide('slow')
  $('#forwarded-tickets-list').append(ticket)
  $('#forwarded-ticket-tab-body').removeClass('hidden')
  const nbForwarded = parseInt($('#forwarded-tickets-tab-badge').html())
  $('#forwarded-tickets-tab-badge').html(nbForwarded + 1)
}

const removeTicket = function (ticketId) {
  const nbForwardedTickets = parseInt($('#forwarded-tickets-tab-badge').html())
  $('#forwarded-tickets-tab-badge').html(nbForwardedTickets - 1)
  $(`#row-ticket-${ticketId}`).remove()
  $(`#ticket-tab-${ticketId}`).remove()
}