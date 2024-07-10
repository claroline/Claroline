import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import classes from 'classnames'

import {DataCard} from '#/main/app/data/components/card'

import {User as UserTypes} from '#/main/community/prop-types'
import {UserStatus} from '#/main/app/user/components/status'

const UserCard = props =>
  <DataCard
    className={classes(props.className, {
      'data-card-muted': get(props.data, 'restrictions.disabled', false)
    })}
    id={props.data.id}
    poster={get(props.data, 'picture')}
    icon={!get(props.data, 'picture') ? <>{props.data.name.charAt(0)}</> : null}
    title={props.data.name}
    meta={
      <UserStatus user={props.data} variant="badge" />
    }
    contentText={get(props.data, 'meta.description')}
    asIcon={true}
    {...props}
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
