import {url} from '#/main/app/api'
import {ASYNC_BUTTON} from '#/main/app/buttons'
import {hasPermission} from '#/main/app/security'
import {trans, transChoice} from '#/main/app/intl/translation'

export default (courses, refresher) => {
  const processable = courses.filter(course => hasPermission('administrate', course))

  return {
    name: 'copy',
    type: ASYNC_BUTTON,
    icon: 'fa fa-fw fa-clone',
    label: trans('copy', {}, 'actions'),
    displayed: 0 !== processable.length,
    confirm: {
      message: transChoice('copy_course_confirm_message', processable.length, {count: '<b class="fw-bold">'+processable.length+'</b>'}, 'actions'),
      items:  processable.map(item => ({
        thumbnail: item.thumbnail,
        name: item.name
      }))
    },
    request: {
      url: url(['apiv2_cursus_course_copy']),
      request: {
        method: 'POST',
        body: JSON.stringify({
          ids: processable.map(course => course.id)
        })
      },
      success: (response) => refresher.update(response)
    },
    group: trans('management'),
    scope: ['object', 'collection']
  }
}
