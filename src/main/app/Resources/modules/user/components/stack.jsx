import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {UserAvatar} from '#/main/app/user/components/avatar'

const UserStack = (props) =>
  <div className={classes('user-avatar-stack', props.className)}>
    {props.users.map(user =>
      <UserAvatar key={user.name} user={user} size={props.size} />
    )}
  </div>

UserStack.propTypes = {
  className: T.string,
  users: T.arrayOf(T.shape({
    picture: T.string,
    name: T.string.isRequired
  })),
  size: T.oneOf(['sm', 'md', 'lg'])
}

export {
  UserStack
}