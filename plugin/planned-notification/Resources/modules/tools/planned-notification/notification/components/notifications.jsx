import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/core/translation'
import {DataListContainer} from '#/main/core/data/list/containers/data-list.jsx'

import {select} from '#/plugin/planned-notification/tools/planned-notification/selectors'

const NotificationsList = props =>
  <DataListContainer
    name="notifications.list"
    open={{
      action: (row) => `#/notifications/form/${row.id}`
    }}
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
        renderer: (row) => trans(row.parameters.action, {}, 'planned_notification')
      }, {
        name: 'roles',
        label: trans('roles'),
        type: 'string',
        displayed: true,
        renderer: (row) => row.roles.map(r => r.translationKey).join(', ')
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