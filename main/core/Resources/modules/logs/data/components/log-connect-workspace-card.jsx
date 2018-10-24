import React from 'react'
import {PropTypes as T} from 'prop-types'

import {asset} from '#/main/app/config/asset'
import {DataCard} from '#/main/app/content/card/components/data'
import {UserAvatar} from '#/main/core/user/components/avatar'
import {convertTimestampToString} from '#/main/core/logs/utils'
import {LogConnectWorkspace as LogConnectWorkspaceType} from '#/main/core/logs/prop-types'

const LogConnectWorkspaceCard = props =>
  <DataCard
    {...props}
    id={props.data.id}
    poster={props.data.user.thumbnail ? asset(props.data.user.thumbnail) : null}
    icon={<UserAvatar picture={props.data.user.picture} alt={true} />}
    title={props.data.user.firstName + ' ' + props.data.user.lastName}
    subtitle={props.data.date}
    contentText={props.data.duration !== null ? convertTimestampToString(props.data.duration) : null}
  />

LogConnectWorkspaceCard.propTypes = {
  data: T.shape(LogConnectWorkspaceType.propTypes).isRequired
}

export {
  LogConnectWorkspaceCard
}
