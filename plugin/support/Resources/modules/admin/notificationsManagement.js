/*global Routing*/
/*global Translator*/
/*global UserPicker*/
import $ from 'jquery'

const contacts = window['contacts']
let contactsIds = []
contacts.forEach(c => contactsIds.push(c['id']))

$('#contacts-management-body').on('click', '#add-support-contact-btn', function () {
  const userPicker = new UserPicker()
  const params = {
    picker_name: 'support_contacts_picker',
    picker_title: Translator.trans('select_support_contacts', {}, 'support'),
    multiple: true,
    blacklist: contactsIds,
    show_mail: true,
    return_datas: true
  }
  userPicker.configure(params, addContacts)
  userPicker.open()
})

$('#contacts-management-body').on('click', '.remove-support-contact-btn', function () {
  const userId = $(this).data('user-id')
  removeContact(userId)
})

$('.notify-checkbox').on('change', function () {
  const type = $(this).data('type')
  const value = $(this).is(':checked') ? 1 : 0
  $.ajax({
    url: Routing.generate('formalibre_admin_support_notify_update'),
    type: 'PUT',
    data: {notifyType: type, notifyValue: value}
  })
})

const addContacts = function (users) {
  if (users !== null) {
    let ids = []
    users.forEach(u => ids.push(u['id']))
    $.ajax({
      url: Routing.generate('formalibre_admin_support_contacts_add'),
      type: 'POST',
      data: {contactIds: ids},
      success: function () {
        users.forEach(u => addContact(u))
      }
    })
  }
}

const addContact = function (user) {
  contactsIds.push(user['id'])
  const contact = `
    <tr id="support-contact-row-${user['id']}">
        <td>
            ${user['firstName']}
            ${user['lastName']}
        </td>
        <td>
            ${user['email']}
        </td>
        <td>
            <span class="btn btn-danger btn-sm remove-support-contact-btn"
                  data-user-id="${user['id']}"
            >
                <i class="fa fa-times-circle"></i>
            </span>
        </td>
    </tr>
  `
  $('#contacts-list').append(contact)
  $('#no-contact-alert').hide('slow')
  $('#contacts-table').removeClass('hidden')
}

const removeContact = function (userId) {
  $.ajax({
    url: Routing.generate('formalibre_admin_support_contact_remove', {contactId: userId}),
    type: 'DELETE',
    success: function () {
      $(`#support-contact-row-${userId}`).remove()
      const index = contactsIds.indexOf(userId)

      if (index > -1) {
        contactsIds.splice(index, 1)
      }
    }
  })
}