import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {hasPermission} from '#/main/app/security'
import {CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {Alert} from '#/main/app/alert/components/alert'
import {MODAL_USERS} from '#/main/core/modals/users'

import {selectors} from '#/plugin/cursus/tools/trainings/catalog/store/selectors'
import {Course as CourseTypes} from '#/plugin/cursus/prop-types'
import {MODAL_SESSIONS} from '#/plugin/cursus/modals/sessions'
import {SessionUsers} from '#/plugin/cursus/session/components/users'

const CoursePending = (props) =>
  <Fragment>
    <Alert type="info">
      {trans('Les utilisateurs doivent être inscrit manuellement à une session', {}, 'cursus')}
    </Alert>

    <SessionUsers
      session={props.course}
      name={selectors.STORE_NAME+'.coursePending'}
      url={['apiv2_cursus_course_list_users', {id: props.course.id}]}
      unregisterUrl={['apiv2_cursus_course_remove_users', {id: props.course.id}]}
      actions={(rows) => [
        /*{
          name: 'invite',
          type: CALLBACK_BUTTON,
          icon: 'fa fa-fw fa-envelope',
          label: trans('send_invitation', {}, 'actions'),
          callback: () => props.inviteUsers(props.course.id, rows),
          displayed: hasPermission('register', props.course)
        }, */{
          name: 'move',
          type: MODAL_BUTTON,
          icon: 'fa fa-fw fa-arrows',
          label: trans('move', {}, 'actions'),
          displayed: hasPermission('register', props.course),
          group: trans('management'),
          modal: [MODAL_SESSIONS, {
            url: ['apiv2_cursus_course_list_sessions', {id: props.course.id}],
            filters: [{property: 'status', value: 'not_ended'}],
            selectAction: (selected) => ({
              type: CALLBACK_BUTTON,
              callback: () => props.moveUsers(props.course.id, selected[0].id, rows)
            })
          }]
        }
      ]}
      add={{
        name: 'add_users',
        type: MODAL_BUTTON,
        label: trans('add_users'),
        modal: [MODAL_USERS, {
          selectAction: (selected) => ({
            type: CALLBACK_BUTTON,
            label: trans('register', {}, 'actions'),
            callback: () => props.addUsers(props.course.id, selected)
          })
        }]
      }}
    />
  </Fragment>

CoursePending.propTypes = {
  path: T.string.isRequired,
  course: T.shape(
    CourseTypes.propTypes
  ).isRequired,
  addUsers: T.func.isRequired,
  inviteUsers: T.func.isRequired,
  moveUsers: T.func.isRequired
}

export {
  CoursePending
}