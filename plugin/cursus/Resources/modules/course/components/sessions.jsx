import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data'

import {route} from '#/plugin/cursus/routing'
import {Course as CourseTypes} from '#/plugin/cursus/course/prop-types'
import {MODAL_SESSION_FORM} from '#/plugin/cursus/administration/modals/session-form'
import {SessionList} from '#/plugin/cursus/administration/cursus/session/components/session-list'
import {selectors} from '#/plugin/cursus/tools/cursus/catalog/store/selectors'

const CourseSessions = (props) =>
  <Fragment>
    <ListData
      name={selectors.STORE_NAME+'.courseSessions'}
      fetch={{
        url: ['apiv2_cursus_course_list_sessions', {id: props.course.id}],
        autoload: true
      }}
      primaryAction={(row) => ({
        type: LINK_BUTTON,
        target: route(props.course, row),
        label: trans('edit', {}, 'actions')
      })}
      delete={{
        url: ['apiv2_cursus_session_delete_bulk']
      }}
      definition={SessionList.definition}
      card={SessionList.card}
      actions={(rows) => [
        {
          name: 'edit',
          type: MODAL_BUTTON,
          icon: 'fa fa-fw fa-pencil',
          label: trans('edit', {}, 'actions'),
          modal: [MODAL_SESSION_FORM, {
            session: rows[0],
            onSave: () => props.invalidateList()
          }],
          scope: ['object'],
          group: trans('management')
        }
      ]}
    />

    <Button
      className="btn btn-block btn-emphasis component-container"
      type={MODAL_BUTTON}
      label={trans('add_session', {}, 'cursus')}
      modal={[MODAL_SESSION_FORM, {
        course: props.course,
        onSave: () => props.reload(props.course.slug)
      }]}
      primary={true}
    />
  </Fragment>

CourseSessions.propTypes = {
  path: T.string.isRequired,
  course: T.shape(
    CourseTypes.propTypes
  ).isRequired,
  reload: T.func.isRequired,
  invalidateList: T.func.isRequired
}

export {
  CourseSessions
}
