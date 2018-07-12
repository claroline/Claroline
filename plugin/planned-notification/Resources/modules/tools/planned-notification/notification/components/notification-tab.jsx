import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {makeId} from '#/main/core/scaffolding/id'
import {trans} from '#/main/core/translation'
import {Routes} from '#/main/app/router'
import {PageActions, PageAction} from '#/main/core/layout/page/components/page-actions'

import {select} from '#/plugin/planned-notification/tools/planned-notification/selectors'
import {WORKSPACE_REGISTRATION_USER} from '#/plugin/planned-notification/tools/planned-notification/constants'
import {actions} from '#/plugin/planned-notification/tools/planned-notification/notification/actions'
import {Notifications} from '#/plugin/planned-notification/tools/planned-notification/notification/components/notifications'
import {Notification} from '#/plugin/planned-notification/tools/planned-notification/notification/components/notification'

const NotificationTabActions = () =>
  <PageActions>
    <PageAction
      type="link"
      icon="fa fa-plus"
      label={trans('create_planned_notification', {}, 'planned_notification')}
      target="/notifications/form"
      primary={true}
    />
  </PageActions>

const NotificationTabComponent = props =>
  <Routes
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
      }
    ]}
  />

NotificationTabComponent.propTypes = {
  workspace: T.object.isRequired,
  openForm: T.func.isRequired
}

const NotificationTab = connect(
  state => ({
    workspace: select.workspace(state)
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

      dispatch(actions.open('notifications.current', id, defaultValue))
    }
  })
)(NotificationTabComponent)

export {
  NotificationTabActions,
  NotificationTab
}
