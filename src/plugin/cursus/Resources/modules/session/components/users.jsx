import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl'
import {CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {formatListField} from '#/main/app/content/form/parameters/utils'

import {constants} from '#/plugin/cursus/constants'
import {isFull} from '#/plugin/cursus/utils'
import {Course as CourseTypes, Session as SessionTypes} from '#/plugin/cursus/prop-types'

import {RegistrationUsers} from '#/plugin/cursus/registration/components/users'
import {MODAL_REGISTRATION_PARAMETERS} from '#/plugin/cursus/registration/modals/parameters'
import {MODAL_SESSIONS} from '#/plugin/cursus/modals/sessions'


const SessionUsers = (props) => {
  let customDefinition = [].concat(props.customDefinition || [])
  if (constants.LEARNER_TYPE === props.type && get(props.course, 'registration.form')) {
    get(props.course, 'registration.form').map(formSection => {
      customDefinition = customDefinition.concat(formSection.fields.map(field => formatListField(field, customDefinition, 'data')))
    })
  }

  return (
    <RegistrationUsers
      {...props}
      url={props.session ?
        ['apiv2_training_session_user_list', {id: props.course.id, sessionId: props.session.id}] :
        ['apiv2_training_session_user_list', {id: props.course.id}]
      }
      unregisterUrl={['apiv2_training_session_user_delete_bulk']}
      session={props.session || props.course}
      customDefinition={customDefinition}
      actions={(rows) => [
        {
          name: 'edit',
          type: MODAL_BUTTON,
          icon: 'fa fa-fw fa-pencil',
          label: trans('edit', {}, 'actions'),
          displayed: constants.LEARNER_TYPE === props.type && !isEmpty(get(props.course, 'registration.form')),
          modal: [MODAL_REGISTRATION_PARAMETERS, {
            course: props.course,
            session: rows[0].session,
            registration: rows[0],
            onSave: (registrationData) => props.updateUser(registrationData)
          }],
          group: trans('management'),
          scope: ['object'],
          primary: true
        }, {
          name: 'invite',
          type: CALLBACK_BUTTON,
          icon: 'fa fa-fw fa-envelope',
          label: trans('send_invitation', {}, 'actions'),
          callback: () => props.inviteUsers(rows)
        }, {
          name: 'confirm',
          type: CALLBACK_BUTTON,
          icon: 'fa fa-fw fa-user-check',
          label: trans('confirm_registration', {}, 'actions'),
          callback: () => props.confirmPending(rows.filter(row => !row.confirmed)),
          disabled: props.session ? isFull(props.session) : false,
          displayed: -1 !== rows.findIndex(row => !row.confirmed),
          group: trans('management')
        }, {
          name: 'validate',
          type: CALLBACK_BUTTON,
          icon: 'fa fa-fw fa-check',
          label: trans('validate_registration', {}, 'actions'),
          callback: () => props.validatePending(rows.filter(row => !row.validated)),
          disabled: props.session ? isFull(props.session) : false,
          displayed: -1 !== rows.findIndex(row => !row.validated),
          group: trans('management')
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
              callback: () => props.moveUsers(selected[0].id, rows, props.type)
            })
          }]
        }, {
          name: 'move-pending',
          type: CALLBACK_BUTTON,
          icon: 'fa fa-fw fa-hourglass-half',
          label: trans('move-pending', {}, 'actions'),
          displayed: constants.LEARNER_TYPE === props.type && get(props.course, 'registration.pendingRegistrations', false),
          group: trans('management'),
          callback: () => props.movePending(props.course.id, rows)
        }
      ]}
    />
  )
}

SessionUsers.propTypes = {
  course: T.shape(
    CourseTypes.propTypes
  ).isRequired,
  session: T.shape(
    SessionTypes.propTypes
  ),
  name: T.string.isRequired,
  type: T.string.isRequired,
  customDefinition: T.arrayOf(T.shape({
    // data list prop types
  })),

  updateUser: T.func.isRequired,
  inviteUsers: T.func.isRequired,
  confirmPending: T.func.isRequired,
  validatePending: T.func.isRequired,
  moveUsers: T.func.isRequired,
  movePending: T.func.isRequired
}

export {
  SessionUsers
}
