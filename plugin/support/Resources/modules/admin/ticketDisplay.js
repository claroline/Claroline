/*global Routing*/
/*global Translator*/
import $ from 'jquery'
import {asset} from '#/main/core/asset'

let currentForwardBtn = null

$('#ticket-comment-form-box').on('click', '#add-comment-btn', function (e) {
  e.stopImmediatePropagation()
  e.preventDefault()

  const form = document.getElementById('comment-create-form')
  const action = form.getAttribute('action')
  const formData = new FormData(form)

  $.ajax({
    url: action,
    data: formData,
    type: 'POST',
    processData: false,
    contentType: false,
    success: function (data, textStatus, jqXHR) {
      switch (jqXHR.status) {
        case 201:
          addComment(data)
          break
        default:
          $('#ticket-comment-form-box').html(data)
      }
    },
    error: (response) => {
      const errorAlert = `
        <div class="alert alert-danger">
            ${response.responseText}
        </div>
      `
      $('#ticket-comment-form-box').html(errorAlert)
    }
  })
})

$('#public-comments-box, #private-comments-box').on('click', '.edit-comment-btn', function () {
  const commentId = $(this).data('comment-id')
  const commentType = $(this).data('comment-type')

  window.Claroline.Modal.displayForm(
    Routing.generate(
      'formalibre_admin_ticket_comment_edit_form',
      {comment: commentId, type: commentType}
    ),
    updateComment,
    () => {}
  )
})

$('#public-comments-box').on('click', '.delete-comment-btn', function () {
  const commentId = $(this).data('comment-id')

  window.Claroline.Modal.confirmRequest(
    Routing.generate(
      'formalibre_admin_ticket_comment_delete',
      {comment: commentId}
    ),
    removeCommentRow,
    commentId,
    Translator.trans('message_deletion_confirm_message', {}, 'support'),
    Translator.trans('message_deletion', {}, 'support')
  )
})

$('#private-comments-box').on('click', '.delete-comment-btn', function () {
  const commentId = $(this).data('comment-id')

  window.Claroline.Modal.confirmRequest(
    Routing.generate(
      'formalibre_admin_ticket_comment_delete',
      {comment: commentId}
    ),
    removeCommentRow,
    commentId,
    Translator.trans('note_deletion_confirm_message', {}, 'support'),
    Translator.trans('note_deletion', {}, 'support')
  )
})

$('#ticket-private-comment-form-box').on('click', '#add-private-comment-btn', function (e) {
  e.stopImmediatePropagation()
  e.preventDefault()

  const form = document.getElementById('private-comment-create-form')
  const action = form.getAttribute('action')
  const formData = new FormData(form)

  $.ajax({
    url: action,
    data: formData,
    type: 'POST',
    processData: false,
    contentType: false,
    success: function (data, textStatus, jqXHR) {
      switch (jqXHR.status) {
        case 201:
          addPrivateComment(data)
          break
        default:
          $('#ticket-private-comment-form-box').html(data)
      }
    }
  })
})

$('#ticket-edition-btn').on('click', function () {
  const ticketId = $(this).data('ticket-id')
  window.Claroline.Modal.displayForm(
    Routing.generate('formalibre_admin_support_ticket_intervention_create_form', {ticket: ticketId}),
    updateTicket,
    () => {}
  )
})

$('#informations-heading').on('click', '.forward-ticket-btn', function () {
  currentForwardBtn = $(this)
  const ticketId = $(this).data('ticket-id')
  window.Claroline.Modal.displayForm(
    Routing.generate('formalibre_admin_forwarded_ticket_create_form', {ticket: ticketId}),
    addForwardedTicket,
    () => {}
  )
})

const removeCommentRow = function (event, commentId) {
  $(`#row-comment-${commentId}`).remove()
}

const addComment = function (data) {
  $('#comment_form_content').html('')
  $('#comment_edit_form_content').html('')

  const picture = data['user']['picture'] ?
    `
      <img src="${asset('uploads/pictures/' + data['user']['picture'])}"
           class="media-object comment-picture"
           alt="${data['user']['firstName']} ${data['user']['lastName']}"
      >
    ` :
    `
      <h1 class="profile_picture_placeholder">
          <i class="fa fa-user"></i>
      </h1>
    `
  const actionButtons = data['editable'] ?
    `
      <br>
      <button class="btn btn-default edit-comment-btn btn-sm"
              data-comment-id="${data['comment']['id']}"
              data-comment-type="${data['comment']['type']}"
      >
          <i class="fa fa-edit"></i>
      </button>
      <button class="btn btn-danger delete-comment-btn btn-sm"
              data-comment-id="${data['comment']['id']}"
      >
          <i class="fa fa-trash"></i>
      </button>
    ` :
    ''
  const comment = `
    <div class="media comment-row" id="row-comment-${data['comment']['id']}">
        <div class="comment-content col-md-10 col-sm-10 comment-content-left">
            <div id="comment-content-${data['comment']['id']}">
                ${data['comment']['content']}
            </div>
        </div>
        <div class="comment-contact col-md-2 col-sm-2 text-center comment-contact-right">
            ${picture}
            ${data['user']['firstName']}
            ${data['user']['lastName']}
            <br>
            ${data['comment']['creationDate']}
            ${actionButtons}
        </div>
    </div>
  `
  $('#public-comments-list').prepend('<hr>')
  $('#public-comments-list').prepend(comment)
}

const addPrivateComment = function (data) {
  $('#private_comment_form_content').html('')
  $('#private_comment_edit_form_content').html('')

  const picture = data['user']['picture'] ?
    `
      <img src="${asset('uploads/pictures/' + data['user']['picture'])}"
           class="media-object comment-picture"
           alt="${data['user']['firstName']} ${data['user']['lastName']}"
      >
    ` :
    `
      <h1 class="profile_picture_placeholder">
          <i class="fa fa-user"></i>
      </h1>
    `

  const comment = `
    <div class="media comment-row" id="row-comment-${data['comment']['id']}">
        <div class="comment-contact col-md-2 col-sm-2 text-center comment-contact-left">
            ${picture}
            ${data['user']['firstName']}
            ${data['user']['lastName']}
            <br>
            ${data['comment']['creationDate']}
            <br>
            <button class="btn btn-default edit-comment-btn btn-sm"
                    data-comment-id="${data['comment']['id']}"
                    data-comment-type="${data['comment']['type']}"
            >
                <i class="fa fa-edit"></i>
            </button>
            <button class="btn btn-danger delete-comment-btn btn-sm"
                    data-comment-id="${data['comment']['id']}"
            >
                <i class="fa fa-trash"></i>
            </button>
        </div>
        <div class="comment-content col-md-10 col-sm-10 comment-content-right">
            <div id="comment-content-${data['comment']['id']}">
                ${data['comment']['content']}
            </div>
        </div>
    </div>
  `
  $('#private-comments-list').prepend('<hr>')
  $('#private-comments-list').prepend(comment)
}

const updateComment = function (data) {
  if (data['type'] === 0) {
    $('#comment_form_content').html('')
    $('#comment_edit_form_content').html('')
    $(`#public-comments-list #comment-content-${data['id']}`).html(data['content'])
  } else {
    $('#private_comment_form_content').html('')
    $('#private_comment_edit_form_content').html('')
    $(`#private-comments-list #comment-content-${data['id']}`).html(data['content'])
  }
}

const updateTicket = function (data) {
  if (data['type']) {
    const type = `
      ${Translator.trans(data['type']['name'], {}, 'support')}
      ${data['type']['description'] ? `
        <i class="fa fa-info-circle pointer-hand"
           data-toggle="tooltip"
           data-container="body"
           data-placement="top"
           data-html="true"
           title="${data['type']['description']}"
        >
        </i>` :
        ''
      }
    `
    $('#ticket-type-column').html(type)
  }
  if (data['status']) {
    const status = `
      <li>
          ${Translator.trans(data['status']['name'], {}, 'support')}
          (${data['status']['date']})
          ${data['status']['description'] ? `
            <i class="fa fa-info-circle pointer-hand"
               data-toggle="tooltip"
               data-container="body"
               data-placement="top"
               data-html="true"
               title="${data['status']['description']}"
            >
            </i>` :
            ''
          }
      </li>
    `
    $('#interventions-list').append(status)
  }
  if (data['publicComment']) {
    addComment(data['publicComment'])
  }
  if (data['privateComment']) {
    addPrivateComment(data['privateComment'])
  }
}

const addForwardedTicket = function (data) {
  const nbForwarded = parseInt($('#forwarded-tickets-tab-badge').html())
  $('#forwarded-tickets-tab-badge').html(nbForwarded + 1)
  const url = Routing.generate('formalibre_admin_ticket_open', {ticket: data['forwardedId']})
  const linkBtn = `
    <a href="${url}"
       class="btn btn-default"
       data-toggle="tooltip"
       title="${Translator.trans('forwarded_ticket', {}, 'support')}"
    >
        <i class="fa fa-asterisk"></i>
    </a>
  `
  currentForwardBtn.after(linkBtn)
  currentForwardBtn.remove()
  let forwardStatusDescription = ''

  if (data['status_description']) {
    forwardStatusDescription = `
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
  let forwardStatus = `
    <li>
      ${Translator.trans(data['status_name'], {}, 'support')}
      ${forwardStatusDescription}
    </li>
  `
  $('#interventions-list').append(forwardStatus)
}