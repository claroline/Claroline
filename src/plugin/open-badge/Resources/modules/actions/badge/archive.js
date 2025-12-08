import get from 'lodash/get'

import {ASYNC_BUTTON} from '#/main/app/buttons'
import {trans, transChoice} from '#/main/app/intl'
import {hasPermission} from '#/main/app/security'
import {constants, declareAction} from '#/main/app/action'

export default declareAction((badges, refresher) => {
  const processable = badges.filter(badge => hasPermission('edit', badge) && !get(badge, 'meta.archived', false))

  return {
    name: 'archive',
    type: ASYNC_BUTTON,
    icon: 'fa fa-fw fa-box',
    label: trans('archive', {}, 'actions'),

    displayed: 0 !== processable.length,
    request: {
      url: ['apiv2_badge_archive'],
      request: {
        method: 'POST',
        body: JSON.stringify(processable.map(u => u.id))
      },
      success: () => refresher.update(processable)
    },
    confirm: {
      message: transChoice('archive_confirm_message', processable.length, {count: '<b class="fw-bold">'+processable.length+'</b>'}, 'badge'),
      items:  processable.map(item => ({
        thumbnail: item.image,
        id: item.id,
        name: item.name
      }))
    },
    scope: ['object', 'collection'],
    group: trans('transfer'),
    dangerous: true,
    set: [constants.ACTION_SET_LIST, constants.ACTION_SET_DETAILS, constants.ACTION_SET_ADVANCED],
    title: trans('archive_badge', {}, 'actions'),
    description: trans('archive_badge_desc', {}, 'actions')
  }
})
