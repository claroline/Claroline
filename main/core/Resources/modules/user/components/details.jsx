import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {displayDate} from '#/main/app/intl/date'

import {getPlatformRoles} from '#/main/core/user/role/utils'

const UserDetails = props =>
  <div className="user-details panel panel-default">
    <div className="panel-body text-center">
      {getPlatformRoles(props.user.roles).join(', ')}
    </div>

    <ul className="list-group list-group-values">
      <li className="list-group-item">
        {trans('registered_at')}
        <span className="value">
          {displayDate(props.user.meta.created)}
        </span>
      </li>
      <li className="list-group-item">
        {trans('last_logged_at')}
        <span className="value">
          {props.user.meta.lastLogin ? displayDate(props.user.meta.lastLogin, false, true) : trans('never')}
        </span>
      </li>
    </ul>
  </div>

UserDetails.propTypes = {
  user: T.shape({
    meta: T.shape({
      created: T.string.isRequired,
      lastLogin: T.string
    }),
    roles: T.arrayOf(T.shape({
      type: T.number.isRequired,
      translationKey: T.string.isRequired
    })).isRequired
  })
}

export {
  UserDetails
}