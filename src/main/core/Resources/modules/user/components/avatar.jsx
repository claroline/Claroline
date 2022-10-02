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
const UserAvatar = props =>
  props.picture ?
    <img className={classes('user-avatar', props.className)} alt="avatar" src={asset(props.picture)} /> :
    <span className={classes('user-avatar fa', props.className, {
      'fa-user-circle-o': !props.alt,
      'fa-user': props.alt
    })} />

UserAvatar.propTypes = {
  className: T.string,
  picture: T.string,
  alt: T.bool
}

UserAvatar.defaultProps = {
  alt: true
}

export {
  UserAvatar
}
