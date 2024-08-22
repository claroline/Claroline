import get from 'lodash/get'

import {url} from '#/main/app/api'
import {ASYNC_BUTTON} from '#/main/app/buttons'
import {trans} from '#/main/app/intl/translation'
import {hasPermission} from '#/main/app/security'

/**
 * Restore action.
 */
export default (courses, refresher) => {
  const processable = courses.filter(course => hasPermission('edit', course) && get(course, 'meta.archived'))

  return {
    name: 'restore',
    type: ASYNC_BUTTON,
    icon: 'fa fa-fw fa-box-open',
    label: trans('restore', {}, 'actions'),
    displayed: 0 !== processable.length,
    request: {
      url: url(['apiv2_cursus_course_restore']),
      request: {
        method: 'POST',
        body: JSON.stringify({
          ids: processable.map(course => course.id)
        })
      },
      success: (response) => refresher.update(response)
    },
    group: trans('management'),
    scope: ['object', 'collection'],
    dangerous: true
  }
}
