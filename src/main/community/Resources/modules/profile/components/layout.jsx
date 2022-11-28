import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {displayDate, trans} from '#/main/app/intl'

import {User as UserTypes} from '#/main/community/prop-types'
import {getPlatformRoles} from '#/main/community/utils'


const UserDetails = props =>
  <div className="user-details panel panel-default">
    <div className="panel-body text-center">
      {getPlatformRoles(props.user.roles).map(role => trans(role.translationKey)).join(', ')}
    </div>

    <ul className="list-group list-group-values">
      <li className="list-group-item">
        {trans('registered_at')}
        <span className="value">
          {displayDate(props.user.meta.created)}
        </span>
      </li>
      <li className="list-group-item">
        {trans('last_activity_at')}
        <span className="value">
          {props.user.meta.lastActivity ? displayDate(props.user.meta.lastActivity, false, true) : trans('never')}
        </span>
      </li>
    </ul>
  </div>

UserDetails.propTypes = {
  user: T.shape({
    meta: T.shape({
      created: T.string.isRequired,
      lastActivity: T.string
    }),
    roles: T.arrayOf(T.shape({
      type: T.number.isRequired,
      translationKey: T.string.isRequired
    })).isRequired
  })
}

const ProfileLayout = props =>
  <div className={classes('row user-profile', props.className)}>
    <div className="user-profile-aside col-md-3">
      <UserDetails
        user={props.user}
      />

      {props.affix}
    </div>

    <div className="user-profile-content col-md-9">
      {props.content}
    </div>
  </div>

ProfileLayout.propTypes = {
  user: T.shape(
    UserTypes.propTypes
  ).isRequired,
  affix: T.node.isRequired,
  content: T.node.isRequired,
  className: T.string
}

export {
  ProfileLayout
}
