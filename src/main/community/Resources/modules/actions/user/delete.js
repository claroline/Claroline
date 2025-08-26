import get from 'lodash/get'

import {hasPermission} from '#/main/app/security'
import {ASYNC_BUTTON} from '#/main/app/buttons'
import {trans, transChoice} from '#/main/app/intl/translation'
import {constants, declareAction} from '#/main/app/action'

/**
 * Delete users action.
 */
export default declareAction((users, refresher) => {
  const processable = users.filter(user => hasPermission('delete', user) && get(user, 'restrictions.disabled'))

  return {
    name: 'delete',
    type: ASYNC_BUTTON,
    icon: 'fa fa-fw fa-trash',
    label: trans('delete', {}, 'actions'),
    displayed: 0 !== processable.length,
    dangerous: true,
    confirm: {
      message: transChoice('user_delete_confirm_message', processable.length, {count: '<b class="fw-bold">'+processable.length+'</b>'}, 'community'),
      additional: trans('irreversible_action_confirm'),
      items: processable.map(item => ({
        thumbnail: item.picture,
        id: item.id,
        name: item.name
      }))
    },
    request: {
      url: ['apiv2_user_delete'],
      request: {
        method: 'DELETE',
        body: JSON.stringify(processable.map(user=> user.id))
      },
      success: () => refresher.delete(processable)
    },
    group: trans('management'),
    scope: [constants.ACTION_SCOPE_OBJECT, constants.ACTION_SCOPE_COLLECTION],
    set: [constants.ACTION_SET_LIST, constants.ACTION_SET_DETAILS, constants.ACTION_SET_ADVANCED],
    managerOnly: true,
    title: trans('delete_user', {}, 'privacy'),
    description: trans('delete_user_desc', {}, 'actions')
  }
})
