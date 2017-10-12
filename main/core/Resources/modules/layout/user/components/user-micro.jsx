import React from 'react'
import {PropTypes as T} from 'prop-types'

import {t} from '#/main/core/translation'

import {UserAvatar} from './user-avatar.jsx'

/**
 * Micro representation of a User.
 *
 * @param props
 * @constructor
 */
const UserMicro = props =>
  <div className="user-micro">
    <UserAvatar picture={props.picture} />

    {props.name ?
      props.name : t('unknown')
    }
  </div>

UserMicro.propTypes = {
  name: T.string,
  picture: T.string
}

export {
  UserMicro
}
