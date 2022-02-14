import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {makeId} from '#/main/core/scaffolding/id'
import {trans} from '#/main/app/intl/translation'
import {Routes} from '#/main/app/router'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ToolPage} from '#/main/core/tool/containers/page'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {selectors} from '#/plugin/planned-notification/tools/planned-notification/store'
import {WORKSPACE_REGISTRATION_USER} from '#/plugin/planned-notification/tools/planned-notification/constants'
import {actions} from '#/plugin/planned-notification/tools/planned-notification/notification/actions'
import {Notifications} from '#/plugin/planned-notification/tools/planned-notification/notification/components/notifications'
import {Notification} from '#/plugin/planned-notification/tools/planned-notification/notification/components/notification'
import {ManualNotification} from '#/plugin/planned-notification/tools/planned-notification/notification/components/manual-notification'

const NotificationTabComponent = props =>
  <ToolPage
    subtitle={trans('notifications')}
    primaryAction="add"
    actions={[
      {
        name: 'add',
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-plus',
        label: trans('create_planned_notification', {}, 'planned_notification'),
        target: props.path+'/notifications/form',
        displayed: props.canEdit,
        primary: true
      }, {
        name: 'trigger',
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-calendar-alt',
        label: trans('trigger_planned_notifications_manually', {}, 'planned_notification'),
        target: props.path+'/notifications/manual',
        displayed: props.canEdit
      }
    ]}
  >
    <Routes
      path={props.path}
      routes={[
        {
          path: '/notifications',
          exact: true,
          component: Notifications
        }, {
          path: '/notifications/form/:id?',
          component: Notification,
          onEnter: (params) => props.openForm(params.id, props.workspace),
          onLeave: () => props.openForm(null, props.workspace)
        }, {
          path: '/notifications/manual',
          exact: true,
          component: ManualNotification,
          disabled: !props.canEdit
        }
      ]}
    />
  </ToolPage>

NotificationTabComponent.propTypes = {
  path: T.string.isRequired,
  canEdit: T.bool.isRequired,
  workspace: T.object.isRequired,
  openForm: T.func.isRequired
}

const NotificationTab = connect(
  state => ({
    path: toolSelectors.path(state),
    canEdit: selectors.canEdit(state),
    workspace: selectors.workspace(state)
  }),
  dispatch => ({
    openForm(id, workspace) {
      const defaultValue = {
        id: makeId(),
        workspace: workspace,
        parameters: {
          action: WORKSPACE_REGISTRATION_USER,
          interval: 1,
          byMail: true,
          byMessage: false
        }
      }

      dispatch(actions.open(selectors.STORE_NAME+'.notifications.current', id, defaultValue))
    }
  })
)(NotificationTabComponent)

export {
  NotificationTab
}
