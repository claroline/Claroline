import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {hasPermission} from '#/main/app/security'
import {CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {Alert} from '#/main/app/alert/components/alert'
import {formatListField} from '#/main/app/content/form/parameters/utils'
import {MODAL_USERS} from '#/main/community/modals/users'

import {selectors} from '#/plugin/cursus/tools/trainings/catalog/store/selectors'
import {Course as CourseTypes} from '#/plugin/cursus/prop-types'
import {MODAL_SESSIONS} from '#/plugin/cursus/modals/sessions'
import {RegistrationUsers} from '#/plugin/cursus/registration/components/users'

import {MODAL_REGISTRATION_PARAMETERS} from '#/plugin/cursus/registration/modals/parameters'

const CoursePending = (props) => {
  let customDefinition = []
  if (get(props.course, 'registration.form')) {
    get(props.course, 'registration.form').map(formSection => {
      customDefinition = customDefinition.concat(formSection.fields.map(field => formatListField(field, customDefinition, 'data')))
    })
  }

  return (
    <Fragment>
      <Alert type="info">
        {trans('Les utilisateurs suivant doivent être inscrit manuellement à une session.', {}, 'cursus')}
      </Alert>

      <RegistrationUsers
        session={props.course}
        name={selectors.STORE_NAME+'.coursePending'}
        url={['apiv2_training_course_pending_list', {id: props.course.id}]}
        unregisterUrl={['apiv2_training_course_user_delete_bulk']}
        actions={(rows) => [
          {
            name: 'edit',
            type: MODAL_BUTTON,
            icon: 'fa fa-fw fa-pencil',
            label: trans('edit', {}, 'actions'),
            displayed: !isEmpty(get(props.course, 'registration.form')),
            modal: [MODAL_REGISTRATION_PARAMETERS, {
              course: props.course,
              registration: rows[0],
              onSave: (registrationData) => props.updateUser(registrationData)
            }],
            group: trans('management'),
            scope: ['object'],
            primary: true
          }, {
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
        customDefinition={customDefinition}
        add={{
          name: 'add_pending',
          type: MODAL_BUTTON,
          label: trans('add_pending', {}, 'cursus'),
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
  )
}

CoursePending.propTypes = {
  course: T.shape(
    CourseTypes.propTypes
  ).isRequired,
  updateUser: T.func.isRequired,
  addUsers: T.func.isRequired,
  moveUsers: T.func.isRequired
}

export {
  CoursePending
}