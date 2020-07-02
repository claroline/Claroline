import React from 'react'
import {PropTypes as T} from 'prop-types'

import {asset} from '#/main/app/config/asset'
import {DataCard} from '#/main/app/data/components/card'

import {UserAvatar} from '#/main/core/user/components/avatar'

import {SessionQueue as SessionQueueType} from '#/plugin/cursus/administration/cursus/prop-types'

const SessionQueueCard = props =>
  <DataCard
    {...props}
    id={props.data.id}
    poster={props.data.user.thumbnail ? asset(props.data.user.thumbnail) : null}
    icon={<UserAvatar picture={props.data.user.picture} alt={true} />}
    title={props.data.user.firstName + ' ' + props.data.user.lastName}
    subtitle={props.data.session.name}
  />

SessionQueueCard.propTypes = {
  data: T.shape(SessionQueueType.propTypes).isRequired
}

export {
  SessionQueueCard
}
