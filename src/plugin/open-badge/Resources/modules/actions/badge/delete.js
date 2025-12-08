import isEmpty from 'lodash/isEmpty'

import {hasPermission} from '#/main/app/security'
import {ASYNC_BUTTON} from '#/main/app/buttons'
import {trans, transChoice} from '#/main/app/intl/translation'
import {constants, declareAction} from '#/main/app/action'
import get from 'lodash/get'

/**
 * Delete badges action.
 */
export default declareAction((badges, refresher) => {
  const processable = badges.filter(badge => hasPermission('delete', badge) &&  get(badge, 'meta.archived'))

  return {
    name: 'delete',
    type: ASYNC_BUTTON,
    icon: 'fa fa-fw fa-trash',
    label: trans('delete', {}, 'actions'),
    displayed: !isEmpty(processable),
    dangerous: true,
    confirm: {
      message: transChoice('delete_confirm_message', processable.length, {count: '<b class="fw-bold">'+processable.length+'</b>'}, 'badge'),
      additional: trans('irreversible_action_confirm'),
      items:  processable.map(item => ({
        thumbnail: item.image,
        id: item.id,
        name: item.name
      }))
    },
    request: {
      url: ['apiv2_badge_delete'],
      request: {
        method: 'DELETE',
        body: JSON.stringify(processable.map(badge => badge.id))
      },
      success: () => refresher.delete(processable)
    },
    group: trans('management'),
    scope: ['object', 'collection'],
    set: [constants.ACTION_SET_LIST, constants.ACTION_SET_DETAILS, constants.ACTION_SET_ADVANCED],
    title: trans('delete_badge', {}, 'actions'),
    description: trans('delete_badge_desc', {}, 'actions')
  }
})
