import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {User as UserTypes} from '#/main/community/prop-types'
import {UserDetails} from '#/main/core/user/components/details'

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
