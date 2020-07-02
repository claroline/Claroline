import React from 'react'
import {PropTypes as T} from 'prop-types'

import {asset} from '#/main/app/config/asset'
import {DataCard} from '#/main/app/data/components/card'

import {UserAvatar} from '#/main/core/user/components/avatar'

import {CursusUser as CursusUserType} from '#/plugin/cursus/administration/cursus/prop-types'

const CursusUserCard = props =>
  <DataCard
    {...props}
    id={props.data.id}
    poster={props.data.user.thumbnail ? asset(props.data.user.thumbnail) : null}
    icon={<UserAvatar picture={props.data.user.picture} alt={true} />}
    title={props.data.user.firstName + ' ' + props.data.user.lastName}
    subtitle={props.data.registrationDate}
  />

CursusUserCard.propTypes = {
  data: T.shape(CursusUserType.propTypes).isRequired
}

export {
  CursusUserCard
}
