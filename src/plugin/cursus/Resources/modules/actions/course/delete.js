import {createElement} from 'react'

import {hasPermission} from '#/main/app/security'
import {url} from '#/main/app/api'
import {ASYNC_BUTTON} from '#/main/app/buttons'
import {trans, transChoice} from '#/main/app/intl/translation'

import {CourseCard} from '#/plugin/cursus/course/components/card'

/**
 * Delete courses action.
 */
export default (courses, refresher) => {
  const processable = courses.filter(course => hasPermission('delete', course))

  return {
    name: 'delete',
    type: ASYNC_BUTTON,
    icon: 'fa fa-fw fa-trash',
    label: trans('delete', {}, 'actions'),
    displayed: 0 !== processable.length,
    dangerous: true,
    confirm: {
      title: transChoice('course_delete_confirm_title', processable.length, {}, 'cursus'),
      subtitle: 1 === processable.length ? processable[0].name : transChoice('count_elements', processable.length, {count: processable.length}),
      message: transChoice('course_delete_confirm_message', processable.length, {count: processable.length}, 'cursus'),
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
      url: url(['apiv2_cursus_course_delete_bulk'], {ids: processable.map(course => course.id)}),
      request: {
        method: 'DELETE'
      },
      success: () => refresher.delete(processable)
    },
    group: trans('management'),
    scope: ['object', 'collection']
  }
}
