import React from 'react'
import get from 'lodash/get'
import classes from 'classnames'
import {PropTypes as T} from 'prop-types'

import {asset} from '#/main/app/config/asset'
import {trans} from '#/main/app/intl/translation'
import {DataCard} from '#/main/app/data/components/card'

import {constants} from '#/plugin/cursus/constants'
import {displayUsername} from '#/main/community/utils'
import {User as UserTypes} from '#/main/community/prop-types'
import {UserAvatar} from '#/main/core/user/components/avatar'

const PresenceCard = props =>
  <DataCard
    {...props}
    className={classes(props.className, {
      'data-card-muted': get(props.data.user, 'restrictions.disabled', false)
    })}
    id={props.data.user.id}
    poster={props.data.user.thumbnail ? asset(props.data.user.thumbnail) : null}
    icon={
      <UserAvatar
        picture={props.data.user.picture}
        alt={true}/>}
    title={displayUsername(props.data.user)}
    subtitle={
      <div
        className={classes('badge', `text-bg-${constants.PRESENCE_STATUS_COLORS[props.data.status]}`)}>
        {constants.PRESENCE_STATUSES[props.data.status]}
      </div>}
    flags={[get(props.data.user, 'restrictions.disabled', false) && ['fa fa-circle-xmark', trans('user_disabled', {}, 'community')]
    ].filter(flag => !!flag)}
    contentText={get(props.data.user, 'meta.description')}
  />

PresenceCard.propTypes = {
  className: T.string,
  data: T.shape(
    UserTypes.propTypes
  ).isRequired
}

export {
  PresenceCard
}
