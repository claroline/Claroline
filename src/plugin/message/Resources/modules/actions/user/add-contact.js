import {url} from '#/main/app/api'
import {ASYNC_BUTTON} from '#/main/app/buttons'

import {trans} from '#/main/app/intl/translation'

/**
 * Add some users to contact.
 *
 * @param {Array}       users          - the list of users on which we want to execute the action.
 * @param {object}      usersRefresher - an object containing methods to update context in response to action (eg. add, update, delete).
 * @param {string}      path
 * @param {object|null} currentUser    - the user currently logged.
 */
export default (users, usersRefresher, path, currentUser) => {
  const filteredUsers = users.filter(user => !currentUser || user.id !== currentUser.id)

  return {
    name: 'add-contact',
    type: ASYNC_BUTTON,
    icon: 'fa fa-fw fa-address-book',
    label: trans('add-contact', {}, 'actions'),
    displayed: 0 !== filteredUsers.length,
    scope: ['object', 'collection'],
    request: {
      url: url(['apiv2_contacts_create'], {ids: filteredUsers.map(user => user.id)}),
      request: {
        method: 'PUT'
      }
    },
    group: trans('community')
  }
}
