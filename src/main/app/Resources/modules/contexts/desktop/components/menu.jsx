import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl'
import {LINK_BUTTON} from '#/main/app/buttons'
import {User as UserTypes} from '#/main/community/prop-types'
import {ContextMenu} from '#/main/app/context/containers/menu'

import {getActions} from '#/main/core/desktop'
import {UserAvatar} from '#/main/core/user/components/avatar'

const CurrentUser = (props) =>
  <div className="app-menu-status">
    <UserAvatar className="user-avatar-md" picture={props.currentUser.picture} alt={false} />

    <div className="app-menu-status-info">
      <h3 className="h5">
        {props.currentUser ? props.currentUser.name : trans('guest')}
      </h3>

      {props.roles.map(role => trans(role.translationKey)).join(', ')}
    </div>
  </div>

CurrentUser.propTypes = {
  currentUser: T.shape(
    UserTypes.propTypes
  ),
  roles: T.arrayOf(T.shape({
    translationKey: T.string.isRequired
  }))
}

const DesktopMenu = props => {
  const desktopActions = getActions(props.currentUser)

  return (
    <ContextMenu
      basePath={props.basePath}
      title={trans('desktop')}
      backAction={{
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-angle-double-left',
        label: trans('home'),
        target: '/',
        exact: true
      }}
      tools={props.tools}
      shortcuts={props.shortcuts}
      actions={desktopActions}
    >
      {props.currentUser &&
        <CurrentUser currentUser={props.currentUser} roles={props.roles} />
      }
    </ContextMenu>
  )
}

DesktopMenu.propTypes = {
  basePath: T.string,
  impersonated: T.bool.isRequired,
  currentUser: T.shape(
    UserTypes.propTypes
  ),
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

DesktopMenu.defaultProps = {
  shortcuts: [],
  tools: []
}

export {
  DesktopMenu
}
