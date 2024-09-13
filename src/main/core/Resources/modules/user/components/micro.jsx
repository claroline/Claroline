import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {trans} from '#/main/app/intl/translation'
import {LinkButton} from '#/main/app/buttons/link'

import {route} from '#/main/community/user/routing'
import {UserAvatar} from '#/main/app/user/components/avatar'

/**
 * Micro representation of a User.
 *
 * @param props
 * @constructor
 */
const UserMicro = props => {
  if (props.link && props.name) {
    return (
      <LinkButton className={classes('user-micro text-reset', props.className)} target={route(props)}>
        <UserAvatar user={props} size="xs" noStatus={props.noStatus} />

        {props.name}
      </LinkButton>
    )
  }

  return (
    <div className={classes('user-micro', props.className)}>
      <UserAvatar user={props} size="xs" noStatus={props.noStatus} />

      {props.name || trans('unknown')}
    </div>
  )
}

UserMicro.propTypes = {
  name: T.string,
  firstName: T.string,
  lastName: T.string,
  username: T.string,
  className: T.string,
  picture: T.string,
  link: T.bool,
  noStatus: T.bool
}

UserMicro.defaultProps = {
  showUsername: false,
  link: false
}

export {
  UserMicro
}
