import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {asset} from '#/main/app/config/asset'

/**
 * Avatar of a User.
 *
 * @param props
 * @constructor
 */
const UserAvatar = props => {
  if (props.picture) {
    return (
      <img className={classes('user-avatar', props.size && `user-avatar-${props.size}`, props.className)} alt="avatar" src={asset(props.picture)} />
    )
  }

  return (
    <span className={classes('user-avatar fa', props.size && `user-avatar-${props.size}`, props.className, {
      'fa-user-circle': !props.alt,
      'fa-user': props.alt
    })} />
  )
}

UserAvatar.propTypes = {
  className: T.string,
  picture: T.string,
  alt: T.bool,
  size: T.oneOf(['sm', 'md', 'lg'])
}

UserAvatar.defaultProps = {
  alt: true
}

export {
  UserAvatar
}
