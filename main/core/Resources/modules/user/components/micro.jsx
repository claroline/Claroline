import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {url} from '#/main/app/api'
import {trans} from '#/main/core/translation'

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

  if (props.link && props.publicUrl) {
    return (
      <a className={classes('user-micro', props.className)} href={url(['claro_user_profile', {publicUrl: props.publicUrl}])}>
        <UserAvatar picture={props.picture} alt={false} />

        {displayName ?
          displayName : trans('unknown')
        }
      </a>
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
  publicUrl: T.string,
  showUsername: T.bool
}

UserMicro.defaultProps = {
  link: false,
  showUsername: false
}

export {
  UserMicro
}
