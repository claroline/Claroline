import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl'
import {CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'

import {RegistrationGroups} from '#/plugin/cursus/registration/components/groups'
import {Course as CourseTypes, Session as SessionTypes} from '#/plugin/cursus/prop-types'
import {MODAL_SESSIONS} from '#/plugin/cursus/modals/sessions'

const SessionGroups = (props) =>
  <RegistrationGroups
    {...props}
    session={props.session || props.course}
    url={props.session ?
      ['apiv2_training_session_group_list', {id: props.course.id, sessionId: props.session.id}] :
      ['apiv2_training_session_group_list', {id: props.course.id}]
    }
    unregisterUrl={['apiv2_training_session_group_delete_bulk']}
    actions={(rows) => [
      {
        name: 'invite',
        type: CALLBACK_BUTTON,
        icon: 'fa fa-fw fa-envelope',
        label: trans('send_invitation', {}, 'actions'),
        callback: () => props.inviteGroups(rows)
      }, {
        name: 'move',
        type: MODAL_BUTTON,
        icon: 'fa fa-fw fa-arrows',
        label: trans('move', {}, 'actions'),
        group: trans('management'),
        modal: [MODAL_SESSIONS, {
          url: ['apiv2_cursus_course_list_sessions', {id: get(props.course, 'id')}],
          filters: [{property: 'status', value: 'not_ended'}],
          selectAction: (selected) => ({
            type: CALLBACK_BUTTON,
            callback: () => props.moveGroups(selected[0].id, rows, props.type)
          })
        }]
      }
    ]}
  />

SessionGroups.propTypes = {
  course: T.shape(
    CourseTypes.propTypes
  ).isRequired,
  session: T.shape(
    SessionTypes.propTypes
  ),
  type: T.string,
  name: T.string.isRequired,
  inviteGroups: T.func.isRequired,
  moveGroups: T.func.isRequired
}

export {
  SessionGroups
}
