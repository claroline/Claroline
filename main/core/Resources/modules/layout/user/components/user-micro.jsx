import React from 'react'
import {PropTypes as T} from 'prop-types'

import {asset} from '#/main/core/asset'
import {t} from '#/main/core/translation'

/**
 * Micro representation of a User.
 *
 * @param props
 * @constructor
 */
const UserMicro = props =>
  <div className="user-micro">
    {props.picture ?
      <img className="avatar" src={asset('uploads/pictures/'+props.picture)} /> :
      <span className="avatar fa fa-user-circle-o" />
    }

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
