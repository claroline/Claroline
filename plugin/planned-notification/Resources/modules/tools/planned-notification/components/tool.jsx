import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/core/translation'
import {TabbedPageContainer} from '#/main/core/layout/tabs'

import {select} from '#/plugin/planned-notification/tools/planned-notification/selectors'
import {
  NotificationTab,
  NotificationTabActions,
  NotificationTabEditActions
} from '#/plugin/planned-notification/tools/planned-notification/notification/components/notification-tab.jsx'
import {
  MessageTab,
  MessageTabActions,
  MessageTabEditActions
} from '#/plugin/planned-notification/tools/planned-notification/message/components/message-tab.jsx'

const Tool = props => {
  const tabs = [
    {
      icon: 'fa fa-bell',
      title: trans('notifications'),
      path: '/notifications',
      content: NotificationTab,
      actions: props.canEdit ? NotificationTabEditActions : NotificationTabActions
    }, {
      icon: 'fa fa-envelope',
      title: trans('messages'),
      path: '/messages',
      content: MessageTab,
      actions: props.canEdit ? MessageTabEditActions : MessageTabActions
    }
  ]

  return (
    <TabbedPageContainer
      title={trans('claroline_planned_notification_tool', {}, 'tools')}
      redirect={[
        {from: '/', exact: true, to: '/notifications'}
      ]}
      tabs={tabs}
    />
  )
}

Tool.propTypes = {
  canEdit: T.bool.isRequired
}

const PlannedNotificationTool = connect(
  state => ({
    canEdit: select.canEdit(state)
  })
)(Tool)

export {
  PlannedNotificationTool
}