import React from 'react'
import {PropTypes as T} from 'prop-types'

import {MenuButton} from '#/main/app/buttons/menu'

import {UserMicro} from '#/main/core/user/components/micro'
import {User as UserTypes} from '#/main/community/prop-types'

const UserMicroList = props => {
  if (1 === props.users.length) {
    return (
      <UserMicro {...props.users[0]} link={props.link} />
    )
  }

  return (
    <div className="user-micro-list">
      <UserMicro {...props.users[0]} link={props.link} />

      <MenuButton
        id={props.id+'-list'}
        className="badge icon-with-text-left"
        menu={
          <ul className="dropdown-menu dropdown-menu-right">
            {props.label &&
              <li role="heading" className="dropdown-header">{props.label}</li>
            }

            {props.users.map(user =>
              <li role="presentation" key={user.id}>
                <UserMicro {...user} link={props.link} />
              </li>
            )}
          </ul>
        }
      >
        + {props.users.length - 1}
      </MenuButton>
    </div>
  )
}

UserMicroList.propTypes = {
  id: T.string.isRequired,
  label: T.string,
  link: T.bool,
  users: T.arrayOf(T.shape(
    UserTypes.propTypes
  )).isRequired
}

UserMicroList.defaultProps = {
  link: false
}

export {
  UserMicroList
}
