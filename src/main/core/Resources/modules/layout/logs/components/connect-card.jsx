import React from 'react'
import {PropTypes as T} from 'prop-types'

import {asset} from '#/main/app/config/asset'
import {displayDuration} from '#/main/app/intl/date'
import {DataCard} from '#/main/app/data/components/card'
import {UserAvatar} from '#/main/core/user/components/avatar'
import {User as UserTypes} from '#/main/core/user/prop-types'

const LogConnectCard = props =>
  <DataCard
    {...props}
    id={props.data.id}
    poster={props.data.user.thumbnail ? asset(props.data.user.thumbnail) : null}
    icon={<UserAvatar picture={props.data.user.picture} alt={true} />}
    title={props.data.user.firstName + ' ' + props.data.user.lastName}
    subtitle={props.data.date}
    contentText={props.data.duration !== null ? displayDuration(props.data.duration) : null}
  />

LogConnectCard.propTypes = {
  data: T.shape({
    id: T.string.isRequired,
    date: T.string.isRequired,
    user: T.shape(
      UserTypes.propTypes
    ).isRequired,
    duration: T.number
  }).isRequired
}

export {
  LogConnectCard
}
