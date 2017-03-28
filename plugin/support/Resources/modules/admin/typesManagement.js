/*global Routing*/
/*global Translator*/
import $ from 'jquery'

$('#types-management-body').on('click', '#create-type-btn', function () {
  window.Claroline.Modal.displayForm(
    Routing.generate('formalibre_admin_support_type_create_form'),
    addTypeRow,
    function () {}
  )
})

$('#types-management-body').on('click', '.edit-type-btn', function () {
  const typeId = $(this).data('type-id')

  window.Claroline.Modal.displayForm(
    Routing.generate('formalibre_admin_support_type_edit_form', {'type': typeId}),
    updateTypeRow,
    function () {}
  )
})

$('#types-management-body').on('click', '.delete-type-btn', function () {
  const typeId = $(this).data('type-id')

  window.Claroline.Modal.confirmRequest(
    Routing.generate('formalibre_admin_support_type_delete', {'type': typeId}),
    removeTypeRow,
    typeId,
    Translator.trans('support_type_deletion_confirm_message', {}, 'support'),
    Translator.trans('support_type_deletion', {}, 'support')
  )
})

const addTypeRow = function (data) {
  const type = `
    <tr id="row-type-${data['id']}">
        <td class="support-type-name">${data['name']}</td>
        <td class="support-type-description">${data['description']}</td>
        <td class="text-center">
            <button class="btn btn-default edit-type-btn btn-sm"
                    data-type-id="${data['id']}"
            >
                <i class="fa fa-edit"></i>
            </button>
            <button class="btn btn-danger delete-type-btn btn-sm"
                    data-type-id="${data['id']}"
            >
                <i class="fa fa-trash"></i>
            </button>
        </td>
    </tr>
  `
  $('#types-table-body').append(type)
}

const updateTypeRow = function (data) {
  if (!data['locked']) {
    $(`#types-management-body #row-type-${data['id']} .support-type-name`).html(data['name'])
  }
  $(`#types-management-body #row-type-${data['id']} .support-type-description`).html(data['description'])
}

const removeTypeRow = function (event, typeId) {
  $(`#row-type-${typeId}`).remove()
}