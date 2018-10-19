import React from 'react'
import {PropTypes as T} from 'prop-types'

import {asset} from '#/main/app/config/asset'
import {DataCard} from '#/main/core/data/components/data-card'
import {UserAvatar} from '#/main/core/user/components/avatar'
import {convertTimestampToString} from '#/main/core/logs/utils'
import {LogConnectPlatform as LogConnectPlatformType} from '#/main/core/logs/prop-types'

const LogConnectPlatformCard = props =>
  <DataCard
    {...props}
    id={props.data.id}
    poster={props.data.user.thumbnail ? asset(props.data.user.thumbnail) : null}
    icon={<UserAvatar picture={props.data.user.picture} alt={true} />}
    title={props.data.user.firstName + ' ' + props.data.user.lastName}
    subtitle={props.data.date}
    contentText={props.data.duration !== null ? convertTimestampToString(props.data.duration) : null}
  />

LogConnectPlatformCard.propTypes = {
  data: T.shape(LogConnectPlatformType.propTypes).isRequired
}

export {
  LogConnectPlatformCard
}
