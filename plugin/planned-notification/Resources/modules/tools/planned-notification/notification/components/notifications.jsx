import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/core/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data.jsx'

import {select} from '#/plugin/planned-notification/tools/planned-notification/selectors'

const NotificationsList = props =>
  <ListData
    name="notifications.list"
    primaryAction={(row) => ({
      type: LINK_BUTTON,
      target: `/notifications/form/${row.id}`
    })}
    fetch={{
      url: ['apiv2_plannednotification_workspace_list', {workspace: props.workspace.uuid}],
      autoload: true
    }}
    delete={{
      url: ['apiv2_plannednotification_delete_bulk'],
      displayed: () => props.canEdit
    }}
    definition={[
      {
        name: 'parameters.action',
        label: trans('action'),
        alias: 'action',
        type: 'string',
        displayed: true,
        render: (row) => trans(row.parameters.action, {}, 'planned_notification')
      }, {
        name: 'roles',
        label: trans('roles'),
        type: 'string',
        displayed: true,
        render: (row) => row.roles.map(r => r.translationKey).join(', ')
      }, {
        name: 'parameters.interval',
        label: trans('planned_interval', {}, 'planned_notification'),
        alias: 'interval',
        type: 'number',
        displayed: true
      }, {
        name: 'parameters.byMail',
        label: trans('email'),
        alias: 'byMail',
        type: 'boolean',
        displayed: true
      }, {
        name: 'parameters.byMessage',
        label: trans('message'),
        alias: 'byMessage',
        type: 'boolean',
        displayed: true
      }
    ]}
  />

NotificationsList.propTypes = {
  canEdit: T.bool.isRequired,
  workspace: T.shape({
    uuid: T.string.isRequired
  }).isRequired
}

const Notifications = connect(
  state => ({
    canEdit: select.canEdit(state),
    workspace: select.workspace(state)
  })
)(NotificationsList)

export {
  Notifications
}