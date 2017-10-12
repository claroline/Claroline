import React from 'react'
import {PropTypes as T} from 'prop-types'

import {asset} from '#/main/core/asset'

/**
 * Avatar of a User.
 *
 * @param props
 * @constructor
 */
const UserAvatar = props =>
  props.picture ?
    <img className="user-avatar" alt="avatar" src={asset('uploads/pictures/'+props.picture)} /> :
    <span className="user-avatar fa fa-user-circle-o" />

UserAvatar.propTypes = {
  picture: T.string
}

export {
  UserAvatar
}
