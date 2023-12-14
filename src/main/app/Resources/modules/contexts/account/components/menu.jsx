import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl'
import {LINK_BUTTON} from '#/main/app/buttons'

import {User as UserTypes} from '#/main/community/prop-types'
import {UserAvatar} from '#/main/core/user/components/avatar'
import {ContextMenu} from '#/main/app/context/containers/menu'

const CurrentUser = (props) =>
  <div className="app-menu-status">
    <UserAvatar className="user-avatar-md" picture={props.currentUser.picture} alt={false} />

    <div className="app-menu-status-info">
      <h3 className="h5">
        {props.currentUser.name}
      </h3>

      {props.roles.map(role => trans(role.translationKey)).join(', ')}
    </div>
  </div>

CurrentUser.propTypes = {
  currentUser: T.shape(
    UserTypes.propTypes
  ).isRequired,
  roles: T.arrayOf(T.shape({
    translationKey: T.string.isRequired
  }))
}

const AccountMenu = (props) =>
  <ContextMenu
    basePath={props.basePath}
    title={trans('my_account')}
    backAction={{
      type: LINK_BUTTON,
      label: trans('desktop'),
      target: '/desktop',
      exact: true
    }}
    tools={props.tools}
    shortcuts={props.shortcuts}
  >
    <CurrentUser currentUser={props.currentUser} roles={props.roles} />
  </ContextMenu>

AccountMenu.propTypes = {
  basePath: T.string,
  currentUser: T.shape(
    UserTypes.propTypes
  ).isRequired,
  roles: T.arrayOf(T.shape({
    translationKey: T.string.isRequired
  })),
  shortcuts: T.arrayOf(T.shape({
    type: T.oneOf(['tool', 'action']).isRequired,
    name: T.string.isRequired
  })),
  tools: T.arrayOf(T.shape({
    icon: T.string.isRequired,
    name: T.string.isRequired,
    permissions: T.object
  }))
}

export {
  AccountMenu
}
