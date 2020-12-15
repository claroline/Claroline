import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {selectors} from '#/plugin/planned-notification/tools/planned-notification/store'

const ManualNotificationForm = props =>
  <FormData
    level={3}
    name={selectors.STORE_NAME+'.notifications.manual'}
    disabled={!props.canEdit}
    buttons={true}
    target={() => ['apiv2_plannednotification_manual_notifications_trigger']}
    cancel={{
      type: LINK_BUTTON,
      target: props.path+'/notifications',
      exact: true
    }}
    sections={[
      {
        id: 'general',
        title: trans('general'),
        primary: true,
        fields: [
          {
            name: 'date',
            type: 'date',
            label: trans('triggering_date', {}, 'planned_notification'),
            required: true,
            help: trans('triggering_date_desc', {}, 'planned_notification'),
            options: {
              time: true
            }
          }, {
            name: 'notifications',
            type: 'planned_notifications',
            label: trans('notifications'),
            required: true
          }, {
            name: 'users',
            type: 'users',
            label: trans('users'),
            required: true
          }
        ]
      }
    ]}
  />

ManualNotificationForm.propTypes = {
  path: T.string.isRequired,
  canEdit: T.bool.isRequired
}

const ManualNotification = connect(
  state => ({
    path: toolSelectors.path(state),
    canEdit: selectors.canEdit(state)
  })
)(ManualNotificationForm)

export {
  ManualNotification
}