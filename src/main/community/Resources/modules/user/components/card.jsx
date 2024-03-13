import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import classes from 'classnames'

import {trans} from '#/main/app/intl/translation'
import {asset} from '#/main/app/config/asset'
import {displayDate} from '#/main/app/intl/date'

import {DataCard} from '#/main/app/data/components/card'
import {UserAvatar} from '#/main/app/user/components/avatar'
import {User as UserTypes} from '#/main/community/prop-types'
import {UserStatus} from '#/main/app/user/components/status'

const UserCard = props =>
  <DataCard
    {...props}
    className={classes(props.className, 'user-card', {
      'data-card-muted': get(props.data, 'restrictions.disabled', false)
    })}
    id={props.data.id}
    poster={props.data.thumbnail ? asset(props.data.thumbnail) : null}
    icon={
      <UserAvatar user={props.data} size={classes({
        sm: 'xs' === props.size,
        md: 'sm' === props.size,
        lg: 'lg' === props.size
      })} noStatus={true} />
    }
    title={props.data.name}
    subtitle={
      <UserStatus user={props.data} variant="badge" />
    }
    flags={[
      get(props.data, 'restrictions.disabled', false) && ['fa fa-circle-xmark', trans('user_disabled', {}, 'community')] // todo also checks accessibility dates
    ].filter(flag => !!flag)}
    contentText={get(props.data, 'meta.description')}
    footer={get(props.data, 'lastActivity') &&
      <span>
        {trans('last_activity_at')} <b>{displayDate(props.data.lastActivity, false, true)}</b>
      </span>
    }
  />

UserCard.propTypes = {
  size: T.string,
  orientation: T.string,
  className: T.string,
  data: T.shape(
    UserTypes.propTypes
  ).isRequired
}

export {
  UserCard
}
