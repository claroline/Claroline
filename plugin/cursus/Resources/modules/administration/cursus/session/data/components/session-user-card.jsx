import React from 'react'
import {PropTypes as T} from 'prop-types'

import {asset} from '#/main/app/config/asset'
import {DataCard} from '#/main/app/data/components/card'

import {UserAvatar} from '#/main/core/user/components/avatar'

import {SessionUser as SessionUserType} from '#/plugin/cursus/administration/cursus/prop-types'

const SessionUserCard = props =>
  <DataCard
    {...props}
    id={props.data.id}
    poster={props.data.user.thumbnail ? asset(props.data.user.thumbnail) : null}
    icon={<UserAvatar picture={props.data.user.picture} alt={true} />}
    title={props.data.user.firstName + ' ' + props.data.user.lastName}
    subtitle={props.data.registrationDate}
  />

SessionUserCard.propTypes = {
  data: T.shape(SessionUserType.propTypes).isRequired
}

export {
  SessionUserCard
}
