import get from 'lodash/get'

import {trans} from '#/main/app/intl'
import {ASYNC_BUTTON} from '#/main/app/buttons'
import {hasPermission} from '#/main/app/security'
import {declareAction} from '#/main/app/action'

export default declareAction((evaluations, refresher) => {
  const processable = evaluations.filter(evaluation => !get(evaluation, 'meta.archived', false) && hasPermission('administrate', evaluation))

  return ({
    name: 'recompute',
    label: trans('recalculate', {}, 'actions'),
    icon: 'fa fa-fw fa-refresh',
    type: ASYNC_BUTTON,
    displayed: 0 !== processable.length,
    request: {
      url: ['apiv2_workspace_evaluation_recompute', {workspaceId: get(evaluations[0], 'workspace.id')}],
      request: {
        method: 'PUT',
        body: JSON.stringify(processable.map(evaluation => evaluation.id))
      },
      success: refresher.update
    },
    group: trans('management'),
    scope: ['object', 'collection']
  })
})
