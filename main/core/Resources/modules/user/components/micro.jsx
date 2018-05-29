import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {url} from '#/main/app/api'
import {trans} from '#/main/core/translation'

import {UserAvatar} from '#/main/core/user/components/avatar.jsx'

/**
 * Micro representation of a User.
 *
 * @param props
 * @constructor
 */
const UserMicro = props => !props.link || !props.publicUrl ?
  <div className={classes('user-micro', props.className)}>
    <UserAvatar picture={props.picture} alt={false} />

    {props.name ?
      props.name : trans('unknown')
    }
  </div> :
  <a className={classes('user-micro', props.className)} href={url(['claro_user_profile', {publicUrl: props.publicUrl}])}>
    <UserAvatar picture={props.picture} alt={false} />

    {props.name ?
      props.name : trans('unknown')
    }
  </a>

UserMicro.propTypes = {
  name: T.string,
  className: T.string,
  picture: T.shape({
    url: T.string.isRequired
  }),
  link: T.bool,
  publicUrl: T.string
}

UserMicro.defaultProps = {
  link: false
}

export {
  UserMicro
}
