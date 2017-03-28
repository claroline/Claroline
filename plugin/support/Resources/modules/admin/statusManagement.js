/*global Routing*/
/*global Translator*/
import $ from 'jquery'

$('#status-management-body').on('click', '#create-status-btn', function () {
  window.Claroline.Modal.displayForm(
    Routing.generate('formalibre_admin_support_status_create_form'),
    addStatusRow,
    function () {}
  )
})

$('#status-management-body').on('click', '.edit-status-btn', function () {
  const statusId = $(this).data('status-id')

  window.Claroline.Modal.displayForm(
    Routing.generate('formalibre_admin_support_status_edit_form', {'status': statusId}),
    updateStatusRow,
    function () {}
  )
})

$('#status-management-body').on('click', '.delete-status-btn', function () {
  const statusId = $(this).data('status-id')

  window.Claroline.Modal.confirmRequest(
    Routing.generate('formalibre_admin_support_status_delete', {'status': statusId}),
    removeStatusRow,
    statusId,
    Translator.trans('support_status_deletion_confirm_message', {}, 'support'),
    Translator.trans('support_status_deletion', {}, 'support')
  )
})

$('#status-elements-list').sortable({
  items: '.movable-status',
  cursor: 'move'
})

$('#status-elements-list').on('sortupdate', function (event, ui) {
  if (this === ui.item.parents('#status-elements-list')[0]) {
    const statusId = $(ui.item).data('status-id')
    let nextStatusId = -1
    const nextElement = $(ui.item).next()

    if (nextElement !== undefined && nextElement.hasClass('movable-status')) {
      nextStatusId = nextElement.data('status-id')
    }
    $.ajax({
      url: Routing.generate('formalibre_admin_support_status_reorder', {'status': statusId, 'nextStatusId': nextStatusId}),
      type: 'POST'
    })
  }
})

const addStatusRow = function (data) {
  const status = `
    <tr id="row-status-${data['id']}"
        class="movable-status"
        data-status-id="${data['id']}"
    >
        <td>
            <i class="fa fa-sort text-muted"></i>
            <span class="support-status-name">${data['name']}</span>
        </td>
        <td class="support-status-description">
            ${data['description']}
        </td>
        <td class="text-center">
            <button class="btn btn-default edit-status-btn btn-sm"
                    data-status-id="${data['id']}"
            >
                <i class="fa fa-edit"></i>
            </button>
            <button class="btn btn-danger delete-status-btn btn-sm"
                    data-status-id="${data['id']}"
            >
                <i class="fa fa-trash"></i>
            </button>
        </td>
    </tr>
  `
  $('#status-elements-list').append(status)
}

const updateStatusRow = function (data) {
  if (!data['locked']) {
    $(`#status-elements-list #row-status-${data['id']} .support-status-name`).html(data['name'])
  }
  $(`#status-elements-list #row-status-${data['id']} .support-status-description`).html(data['description'])
}

const removeStatusRow = function (event, statusId) {
  $(`#row-status-${statusId}`).remove()
}