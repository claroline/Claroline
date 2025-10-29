import get from 'lodash/get'

import {declareAction} from '#/main/app/action'
import {trans} from '#/main/app/intl'
import {ASYNC_BUTTON} from '#/main/app/buttons'
import {hasPermission} from '#/main/app/security'

export default declareAction((tasks, refresher) => ({
  name: 'mark-done',
  type: ASYNC_BUTTON,
  icon: 'fa fa-fw fa-circle-check',
  label: trans('mark-as-done', {}, 'actions'),
  request: {
    url: ['apiv2_task_mark_done'],
    request: {
      method: 'PUT',
      body: JSON.stringify([tasks[0].id])
    },
    success: (response) => refresher.update(response)
  },
  displayed: hasPermission('edit', tasks[0]) && !get(tasks[0], 'meta.done'),
  scope: ['object']
}))
