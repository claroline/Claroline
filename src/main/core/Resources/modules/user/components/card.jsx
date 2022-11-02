import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import classes from 'classnames'

import {trans} from '#/main/app/intl/translation'
import {asset} from '#/main/app/config/asset'
import {displayDate} from '#/main/app/intl/date'

import {DataCard} from '#/main/app/data/components/card'
import {UserAvatar} from '#/main/core/user/components/avatar'
import {User as UserTypes} from '#/main/community/prop-types'
import {displayUsername} from '#/main/community/utils'

const UserCard = props =>
  <DataCard
    {...props}
    className={classes(props.className, {
      'data-card-muted': get(props.data, 'restrictions.disabled', false)
    })}
    id={props.data.id}
    poster={props.data.thumbnail ? asset(props.data.thumbnail) : null}
    icon={<UserAvatar picture={props.data.picture} alt={true} />}
    title={props.data.username}
    subtitle={displayUsername(props.data)}
    flags={[
      get(props.data, 'restrictions.disabled', false) && ['fa fa-times-circle', trans('user_disabled')] // todo also checks accessibility dates
    ].filter(flag => !!flag)}
    contentText={get(props.data, 'meta.description')}
    footer={get(props.data, 'meta.lastActivity') &&
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
