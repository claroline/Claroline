import React from 'react'
import {PropTypes as T} from 'prop-types'

import {t} from '#/main/core/translation'

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
