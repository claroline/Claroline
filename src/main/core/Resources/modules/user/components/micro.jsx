import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {trans} from '#/main/app/intl/translation'
import {LinkButton} from '#/main/app/buttons/link'

import {route} from '#/main/community/user/routing'
import {UserAvatar} from '#/main/core/user/components/avatar'

/**
 * Micro representation of a User.
 *
 * @param props
 * @constructor
 */
const UserMicro = props => {
  let displayName
  if (props.showUsername) {
    displayName = props.username
  } else if (props.name) {
    displayName = props.name
  } else {
    displayName = (props.firstName || '') + ' ' + (props.lastName || '')
    displayName = displayName.trim()
  }

  if (props.link && displayName) {
    return (
      <LinkButton className={classes('user-micro', props.className)} target={route(props)}>
        <UserAvatar picture={props.picture} alt={false} />

        {displayName}
      </LinkButton>
    )
  }

  return (
    <div className={classes('user-micro', props.className)}>
      <UserAvatar picture={props.picture} alt={false} />

      {displayName ?
        displayName : trans('unknown')
      }
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
  showUsername: T.bool,
  link: T.bool
}

UserMicro.defaultProps = {
  showUsername: false,
  link: false
}

export {
  UserMicro
}
