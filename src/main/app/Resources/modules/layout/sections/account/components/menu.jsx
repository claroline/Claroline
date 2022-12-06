import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl'
import {LINK_BUTTON} from '#/main/app/buttons'

import {Toolbar} from '#/main/app/action/components/toolbar'
import {MenuMain} from '#/main/app/layout/menu/containers/main'
import {MenuSection} from '#/main/app/layout/menu/components/section'

import {getPlatformRoles} from '#/main/community/utils'
import {User as UserTypes} from '#/main/community/prop-types'
import {UserAvatar} from '#/main/core/user/components/avatar'

import {getSections} from '#/main/app/account/utils'

import {route} from '#/main/app/account/routing'

const CurrentUser = (props) =>
  <div className="app-menu-status">
    <UserAvatar className="user-avatar-md" picture={props.currentUser.picture} alt={false} />

    <div className="app-menu-status-info">
      <h3 className="h4">
        {props.currentUser.name}
      </h3>

      {getPlatformRoles(props.currentUser.roles).map(role => trans(role.translationKey)).join(', ')}
    </div>
  </div>

CurrentUser.propTypes = {
  currentUser: T.shape(
    UserTypes.propTypes
  ).isRequired
}

const Links = (props) =>
  <MenuSection
    title={trans('my_account')}
    opened={true}
    toggle={() => true}
  >
    <Toolbar
      className="list-group"
      buttonName="list-group-item"
      actions={getSections().then(sections => sections.map(section => ({
        name: section.name,
        type: LINK_BUTTON,
        icon: section.icon,
        label: section.label,
        target: route(section.name),
        onClick: props.autoClose
      })))}
    />
  </MenuSection>

const AccountMenu = (props) =>
  <MenuMain
    title={trans('my_account')}
    backAction={{
      type: LINK_BUTTON,
      label: trans('desktop'),
      target: '/desktop',
      exact: true
    }}
  >
    <CurrentUser currentUser={props.currentUser} />
    <Links />
  </MenuMain>

AccountMenu.propTypes = {
  currentUser: T.shape(
    UserTypes.propTypes
  ).isRequired
}

export {
  AccountMenu
}
