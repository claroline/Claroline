import $ from 'jquery'
import {asset} from '#/main/core/asset'

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
    }
  })
})

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

  const comment = `
    <div class="media comment-row">
        <div class="comment-contact col-md-2 col-sm-2 text-center comment-contact-left">
            ${picture}
            ${data['user']['firstName']}
            ${data['user']['lastName']}
            <br>
            ${data['comment']['creationDate']}
        </div>
        <div class="comment-content col-md-10 col-sm-10 comment-content-right">
            <div id="comment-content-${data['comment']['id']}">
                ${data['comment']['content']}
            </div>
        </div>
    </div>
  `
  $('#public-comments-list').prepend('<hr>')
  $('#public-comments-list').prepend(comment)
}