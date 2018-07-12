import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/core/translation'
import {select as formSelect} from '#/main/core/data/form/selectors'
import {FormContainer} from '#/main/core/data/form/containers/form.jsx'

import {
  TRIGGERING_ACTIONS,
  WORKSPACE_REGISTRATION_USER,
  WORKSPACE_REGISTRATION_GROUP
} from '#/plugin/planned-notification/tools/planned-notification/constants'
import {select} from '#/plugin/planned-notification/tools/planned-notification/selectors'
import {Notification as NotificationTypes} from '#/plugin/planned-notification/tools/planned-notification/prop-types'

const NotificationForm = props =>
  <FormContainer
    level={3}
    name="notifications.current"
    disabled={!props.canEdit}
    buttons={true}
    target={(notification, isNew) => isNew ?
      ['apiv2_plannednotification_create'] :
      ['apiv2_plannednotification_update', {id: notification.id}]
    }
    cancel={{
      type: 'link',
      target: '/notifications',
      exact: true
    }}
    sections={[
      {
        id: 'general',
        title: trans('general'),
        primary: true,
        fields: [
          {
            name: 'parameters.action',
            type: 'choice',
            label: trans('action'),
            required: true,
            options: {
              noEmpty: true,
              condensed: true,
              choices: TRIGGERING_ACTIONS
            },
            linked: [
              {
                name: 'roles',
                label: trans('roles'),
                type: 'workspace_roles',
                required: false,
                displayed: -1 < [WORKSPACE_REGISTRATION_USER, WORKSPACE_REGISTRATION_GROUP].indexOf(props.notification.parameters.action)
              }
            ]
          }, {
            name: 'message',
            type: 'message',
            label: trans('message'),
            required: true
          }, {
            name: 'parameters.interval',
            type: 'number',
            label: trans('planned_interval', {}, 'planned_notification'),
            help: trans('planned_interval_infos', {}, 'planned_notification'),
            options: {
              min: 0,
              unit: trans('days')
            },
            required: true
          }, {
            name: 'parameters.byMail',
            type: 'boolean',
            label: trans('send_a_mail', {}, 'planned_notification'),
            required: true
          }, {
            name: 'parameters.byMessage',
            type: 'boolean',
            label: trans('send_a_message', {}, 'planned_notification'),
            required: true
          }
        ]
      }
    ]}
  />

NotificationForm.propTypes = {
  canEdit: T.bool.isRequired,
  new: T.bool.isRequired,
  notification: T.shape(NotificationTypes.propTypes).isRequired
}

const Notification = connect(
  state => ({
    canEdit: select.canEdit(state),
    roles: select.workspaceRolesChoices(state),
    new: formSelect.isNew(formSelect.form(state, 'notifications.current')),
    notification: formSelect.data(formSelect.form(state, 'notifications.current'))
  })
)(NotificationForm)

export {
  Notification
}