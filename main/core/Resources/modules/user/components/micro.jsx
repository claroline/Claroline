import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/core/translation'

import {UserAvatar} from '#/main/core/user/components/avatar.jsx'

/**
 * Micro representation of a User.
 *
 * @param props
 * @constructor
 */
const UserMicro = props =>
  <div className="user-micro">
    <UserAvatar picture={props.picture} alt={false} />

    {props.name ?
      props.name : trans('unknown')
    }
  </div>

UserMicro.propTypes = {
  name: T.string,
  picture: T.shape({
    url: T.string.isRequired
  })
}

export {
  UserMicro
}
