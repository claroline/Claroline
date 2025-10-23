import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {trans, transChoice} from '#/main/app/intl/translation'
import {ASYNC_BUTTON} from '#/main/app/buttons'
import {hasPermission} from '#/main/app/security'
import {declareAction} from '#/main/app/action'

export default declareAction((evaluations, refresher) => {
  const processable = evaluations.filter(evaluation =>
    hasPermission('administrate', evaluation)
    && !get(evaluation, 'meta.archived', false)
  )

  return ({
    name: 'archive',
    type: ASYNC_BUTTON,
    icon: 'fa fa-fw fa-box',
    label: trans('archive', {}, 'actions'),
    request: {
      url: ['apiv2_workspace_evaluation_archive'],
      request: {
        method: 'PUT',
        body: JSON.stringify(processable.map(evaluation => evaluation.id))
      },
      success: () => refresher.update(processable)
    },
    confirm: {
      message: transChoice('archive_evaluation_confirm', processable.length, {count: '<b class="fw-bold">'+processable.length+'</b>'}, 'actions'),
      additional: trans('archive_evaluation_confirm_additional', {}, 'actions'),
      items:  processable.map(item => ({
        thumbnail: get(item, 'user.picture'),
        id: get(item, 'user.id'),
        name: get(item, 'user.name')
      }))
    },
    displayed: !isEmpty(processable),
    scope: ['object', 'collection'],
    group: trans('management')
  })
})
