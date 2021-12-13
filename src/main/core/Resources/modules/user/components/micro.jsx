import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {trans} from '#/main/app/intl/translation'
import {LinkButton} from '#/main/app/buttons/link'

import {route} from '#/main/core/user/routing'
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
  } else {
    displayName = props.name
  }

  if (props.link && props.username) {
    return (
      <LinkButton className={classes('user-micro', props.className)} target={route(props)}>
        <UserAvatar picture={props.picture} alt={false} />

        {displayName ?
          displayName : trans('unknown')
        }
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
  username: T.string,
  className: T.string,
  picture: T.shape({
    url: T.string.isRequired
  }),
  link: T.bool,
  showUsername: T.bool
}

UserMicro.defaultProps = {
  link: false,
  showUsername: false
}

export {
  UserMicro
}
