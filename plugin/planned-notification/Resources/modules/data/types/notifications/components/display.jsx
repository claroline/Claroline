import React from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {EmptyPlaceholder} from '#/main/app/content/components/placeholder'

import {Notification as NotificationType} from '#/plugin/planned-notification/tools/planned-notification/prop-types'
import {NotificationCard} from '#/plugin/planned-notification/tools/planned-notification/notification/data/components/notification-card'

const NotificationsDisplay = (props) => !isEmpty(props.data) ?
  <div>
    {props.data.map(notification =>
      <NotificationCard
        key={`notification-card-${notification.id}`}
        data={notification}
        size="sm"
        orientation="col"
      />
    )}
  </div> :
  <EmptyPlaceholder
    size="lg"
    icon="fa fa-bell"
    title={trans('no_notification', {}, 'planned_notification')}
  />

NotificationsDisplay.propTypes = {
  data: T.arrayOf(T.shape(NotificationType.propTypes))
}

export {
  NotificationsDisplay
}
