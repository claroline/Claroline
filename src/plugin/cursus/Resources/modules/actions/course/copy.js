import {createElement} from 'react'

import {url} from '#/main/app/api'
import {ASYNC_BUTTON} from '#/main/app/buttons'
import {hasPermission} from '#/main/app/security'
import {trans, transChoice} from '#/main/app/intl/translation'

import {CourseCard} from '#/plugin/cursus/course/components/card'

export default (courses, refresher) => {
  const processable = courses.filter(course => hasPermission('administrate', course))

  return {
    name: 'copy',
    type: ASYNC_BUTTON,
    icon: 'fa fa-fw fa-clone',
    label: trans('copy', {}, 'actions'),
    displayed: 0 !== processable.length,
    confirm: {
      title: transChoice('copy_course_confirm_title', processable.length, {}, 'actions'),
      subtitle: 1 === processable.length ? processable[0].name : transChoice('count_elements', processable.length, {count: processable.length}),
      message: transChoice('copy_course_confirm_message', processable.length, {count: processable.length}, 'actions'),
      additional: [
        createElement('div', {
          key: 'additional',
          className: 'modal-body'
        }, processable.map(course => createElement(CourseCard, {
          key: course.id,
          orientation: 'row',
          size: 'xs',
          data: course
        })))
      ]
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
