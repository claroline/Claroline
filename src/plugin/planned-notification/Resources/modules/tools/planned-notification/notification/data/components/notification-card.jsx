import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {DataCard} from '#/main/app/data/components/card'

import {Notification as NotificationType} from '#/plugin/planned-notification/tools/planned-notification/prop-types'

const NotificationCard = props =>
  <DataCard
    {...props}
    id={props.data.id}
    icon="fa fa-bell"
    title={trans(props.data.parameters.action, {}, 'planned_notification')}
    subtitle={`${props.data.parameters.interval} ${trans('days', {}, 'planned_notification')}`}
    footer={
      <span>
        {props.data.message.title}
      </span>
    }
    contentText={props.data.message.content}
  />

NotificationCard.propTypes = {
  data: T.shape(NotificationType.propTypes).isRequired
}

export {
  NotificationCard
}
