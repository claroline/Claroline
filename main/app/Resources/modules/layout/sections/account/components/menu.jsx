import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl'
import {LINK_BUTTON} from '#/main/app/buttons'

import {Toolbar} from '#/main/app/action/components/toolbar'
import {MenuMain} from '#/main/app/layout/menu/containers/main'
import {MenuSection} from '#/main/app/layout/menu/components/section'

import {getPlatformRoles} from '#/main/core/user/utils'
import {User as UserTypes} from '#/main/core/user/prop-types'
import {UserAvatar} from '#/main/core/user/components/avatar'

const CurrentUser = (props) =>
  <div className="app-menu-status">
    <UserAvatar className="user-avatar-md" picture={props.currentUser.picture} alt={true} />

    <div className="app-menu-status-info">
      <h3 className="h4">
        {props.currentUser.name}
      </h3>

      {getPlatformRoles(props.currentUser.roles).join(', ')}
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
      actions={[
        {
          name: 'profile',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-user',
          label: trans('user_profile'),
          target: '/account/profile',
          onClick: props.autoClose
        }, {
          name: 'parameters',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-cog',
          label: trans('parameters'),
          target: '/account/parameters',
          onClick: props.autoClose,
          displayed: false
        }, {
          name: 'appearance',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-drafting-compass',
          label: trans('appearance'),
          target: '/account/appearance',
          onClick: props.autoClose,
          displayed: false
        }, {
          name: 'linked-accounts',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-plug',
          label: trans('Comptes liés'),
          target: '/account/linked',
          onClick: props.autoClose,
          displayed: false
        }
      ]}
    />
  </MenuSection>

const AccountMenu = (props) =>
  <MenuMain
    title={trans('my_account')}
    backAction={{
      type: LINK_BUTTON,
      icon: 'fa fa-fw fa-angle-double-left',
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
