import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/core/translation'
import {select as formSelect} from '#/main/core/data/form/selectors'
import {FormContainer} from '#/main/core/data/form/containers/form.jsx'

import {constants} from '#/plugin/planned-notification/tools/planned-notification/constants'
import {select} from '#/plugin/planned-notification/tools/planned-notification/selectors'
import {Notification as NotificationTypes} from '#/plugin/planned-notification/tools/planned-notification/prop-types'

const NotificationForm = props =>
  <FormContainer
    level={3}
    name="notifications.current"
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
            disabled: !props.canEdit,
            required: true,
            options: {
              noEmpty: true,
              condensed: true,
              choices: constants.TRIGGERING_ACTIONS
            }
          }, {
            name: 'roles',
            label: trans('roles'),
            type: 'workspace_roles',
            disabled: !props.canEdit,
            required: false
          }, {
            name: 'message',
            type: 'message',
            label: trans('message'),
            disabled: !props.canEdit,
            required: true
          }, {
            name: 'parameters.interval',
            type: 'number',
            label: trans('planned_interval', {}, 'planned_notification'),
            disabled: !props.canEdit,
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
            disabled: !props.canEdit,
            required: true
          }, {
            name: 'parameters.byMessage',
            type: 'boolean',
            label: trans('send_a_message', {}, 'planned_notification'),
            disabled: !props.canEdit,
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