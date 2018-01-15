import React from 'react'
import {PropTypes as T} from 'prop-types'

import {t} from '#/main/core/translation'
import {localeDate} from '#/main/core/scaffolding/date'

import {getPlatformRoles} from '#/main/core/user/role/utils'

const UserDetails = props =>
  <div className="user-details panel panel-default">
    <div className="panel-body text-center">
      {getPlatformRoles(props.user.roles).join(', ')}
    </div>

    <ul className="list-group list-group-values">
      <li className="list-group-item">
        {t('registered_at')}
        <span className="value">
          {localeDate(props.user.meta.created)}
        </span>
      </li>
      <li className="list-group-item">
        {t('last_logged_at')}
        <span className="value">
          {props.user.meta.lastLogin ? localeDate(props.user.meta.lastLogin) : t('never')}
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