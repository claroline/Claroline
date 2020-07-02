import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data'

import {selectors} from '#/plugin/cursus/administration/cursus/store'
import {CourseList} from '#/plugin/cursus/administration/cursus/course/components/course-list'

const Courses = (props) =>
  <ListData
    name={selectors.STORE_NAME + '.courses.list'}
    fetch={{
      url: ['apiv2_cursus_course_list'],
      autoload: true
    }}
    primaryAction={(row) => ({
      type: LINK_BUTTON,
      target: `${props.path}/courses/form/${row.id}`,
      label: trans('edit', {}, 'actions')
    })}
    actions={(rows) => [
      {
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-pencil',
        label: trans('edit'),
        scope: ['object'],
        target: `${props.path}/courses/form/${rows[0].id}`
      }
    ]}
    delete={{
      url: ['apiv2_cursus_course_delete_bulk']
    }}
    definition={CourseList.definition}
    card={CourseList.card}
  />

Courses.propTypes = {
  path: T.string.isRequired
}

export {
  Courses
}
