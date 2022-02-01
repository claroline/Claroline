import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {trans} from '#/main/app/intl/translation'
import {asset} from '#/main/app/config/asset'
import {displayDate} from '#/main/app/intl/date'

import {DataCard} from '#/main/app/data/components/card'
import {UserAvatar} from '#/main/core/user/components/avatar'
import {User as UserTypes} from '#/main/core/user/prop-types'
import {displayUsername} from '#/main/core/user/utils'

const UserCard = props =>
  <DataCard
    {...props}
    className={classes(props.className, {
      'data-card-muted': props.data.restrictions.disabled
    })}
    id={props.data.id}
    poster={props.data.thumbnail ? asset(props.data.thumbnail.url) : null}
    icon={<UserAvatar picture={props.data.picture} alt={true} />}
    title={props.data.username}
    subtitle={displayUsername(props.data)}
    flags={[
      props.data.meta.personalWorkspace && ['fa fa-book', trans('has_personal_workspace')],
      props.data.restrictions.disabled && ['fa fa-times-circle', trans('user_disabled')] // todo also checks accessibility dates
    ].filter(flag => !!flag)}
    contentText={props.data.meta.description}
    footer={props.data.meta.lastActivity &&
      <span>
        {trans('last_activity_at')} <b>{displayDate(props.data.meta.lastActivity, false, true)}</b>
      </span>
    }
  />

UserCard.propTypes = {
  className: T.string,
  data: T.shape(
    UserTypes.propTypes
  ).isRequired
}

export {
  UserCard
}
