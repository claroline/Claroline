import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {displayDate} from '#/main/app/intl/date'

import {DataCard} from '#/main/app/content/card/components/data'
import {UserAvatar} from '#/main/core/user/components/avatar'
import {User as UserTypes} from '#/main/core/user/prop-types'

const NotificationCard = props => {
  return(
    <DataCard
      {...props}
      id={props.data.id}
      poster={null}
      icon={<UserAvatar picture={props.data.picture} alt={true} />}
      contentText={props.data.text}
      primaryAction={props.data.primaryAction ?[]:null}

      footer={
        <span>
          {trans('done_at')}: {displayDate(props.data.notification.creation)}
        </span>
      }
    />
  )
}

NotificationCard.propTypes = {
  data: T.shape(
    UserTypes.propTypes
  ).isRequired
}

export {
  NotificationCard
}
