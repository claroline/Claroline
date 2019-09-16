import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data.jsx'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {selectors} from '#/plugin/planned-notification/tools/planned-notification/store'
import {NotificationCard} from '#/plugin/planned-notification/tools/planned-notification/notification/data/components/notification-card'

const NotificationsList = props =>
  <ListData
    name={selectors.STORE_NAME+'.notifications.list'}
    primaryAction={(row) => ({
      type: LINK_BUTTON,
      target: props.path+`/notifications/form/${row.id}`
    })}
    fetch={{
      url: ['apiv2_plannednotification_workspace_list', {workspace: props.workspace.uuid}],
      autoload: true
    }}
    delete={{
      url: ['apiv2_plannednotification_delete_bulk'],
      displayed: () => props.canEdit
    }}
    actions={(rows) => [
      {
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-edit',
        label: trans('edit'),
        scope: ['object'],
        target: `${props.path}/notifications/form/${rows[0].id}`
      }
    ]}
    definition={[
      {
        name: 'parameters.action',
        label: trans('action'),
        alias: 'action',
        type: 'string',
        displayed: true,
        render: (row) => trans(row.parameters.action, {}, 'planned_notification')
      }, {
        name: 'message.title',
        label: trans('message'),
        type: 'string',
        displayed: true
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
    card={NotificationCard}
  />

NotificationsList.propTypes = {
  path: T.string.isRequired,
  canEdit: T.bool.isRequired,
  workspace: T.shape({
    uuid: T.string.isRequired
  }).isRequired
}

const Notifications = connect(
  state => ({
    path: toolSelectors.path(state),
    canEdit: selectors.canEdit(state),
    workspace: selectors.workspace(state)
  })
)(NotificationsList)

export {
  Notifications
}