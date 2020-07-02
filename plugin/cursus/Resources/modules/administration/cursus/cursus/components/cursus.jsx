import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {MODAL_DATA_LIST} from '#/main/app/modals/list'
import {TreeData} from '#/main/app/content/tree/containers/data'

import {selectors} from '#/plugin/cursus/administration/cursus/store'
import {actions} from '#/plugin/cursus/administration/cursus/cursus/store'
import {CourseList} from '#/plugin/cursus/administration/cursus/course/components/course-list'
import {CursusCard} from '#/plugin/cursus/administration/cursus/cursus/data/components/cursus-card'

const CursusComponent = (props) =>
  <TreeData
    name={selectors.STORE_NAME + '.cursus.list'}
    primaryAction={(row) => ({
      type: LINK_BUTTON,
      target: `${props.path}/cursus/form/${row.id}`,
      label: trans('edit', {}, 'actions')
    })}
    fetch={{
      url: ['apiv2_cursus_list'],
      autoload: true
    }}
    delete={{
      url: ['apiv2_cursus_delete_bulk']
    }}
    definition={[
      {
        name: 'title',
        type: 'string',
        label: trans('title'),
        displayed: true,
        primary: true
      }, {
        name: 'code',
        type: 'string',
        label: trans('code'),
        displayed: true
      }, {
        name: 'meta.blocking',
        alias: 'blocking',
        type: 'boolean',
        label: trans('blocking', {}, 'cursus'),
        displayed: true
      }
    ]}
    actions={(rows) => [
      {
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-pencil',
        label: trans('edit'),
        scope: ['object'],
        target: `${props.path}/cursus/form/${rows[0].id}`
      }, {
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-plus',
        label: trans('create_cursus_child', {}, 'cursus'),
        displayed: !rows[0].meta || !rows[0].meta.course,
        scope: ['object'],
        target: `${props.path}/cursus/form/parent/${rows[0].id}`
      }, {
        type: MODAL_BUTTON,
        icon: 'fa fa-fw fa-tasks',
        label: trans('add_course_to_cursus', {}, 'cursus'),
        displayed: !rows[0].meta || !rows[0].meta.course,
        scope: ['object'],
        modal: [MODAL_DATA_LIST, {
          icon: 'fa fa-fw fa-tasks',
          title: trans('add_course_to_cursus', {}, 'cursus'),
          confirmText: trans('select', {}, 'actions'),
          name: selectors.STORE_NAME + '.courses.picker',
          definition: CourseList.definition,
          card: CourseList.card,
          fetch: {
            url: ['apiv2_cursus_course_list'],
            autoload: true
          },
          handleSelect: (selected) => props.addCourses(rows[0].id, selected)
        }]
      }
    ]}
    card={CursusCard}
  />

CursusComponent.propTypes = {
  path: T.string.isRequired,
  addCourses: T.func.isRequired
}

const Cursus = connect(
  null,
  (dispatch) => ({
    addCourses(cursusId, courses) {
      dispatch(actions.addCourses(cursusId, courses))
    }
  })
)(CursusComponent)

export {
  Cursus
}
