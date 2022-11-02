import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {Toolbar} from '#/main/app/action/components/toolbar'
import {MenuSection} from '#/main/app/layout/menu/components/section'

const CommunityMenu = (props) =>
  <MenuSection
    {...omit(props, 'path')}
    title={trans('community', {}, 'tools')}
  >
    <Toolbar
      className="list-group"
      buttonName="list-group-item"
      actions={[
        {
          name: 'users',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-user',
          label: trans('users'),
          target: `${props.path}/users`
        }, {
          name: 'groups',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-users',
          label: trans('groups'),
          target: `${props.path}/groups`
        }, {
          name: 'roles',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-id-badge',
          label: trans('roles'),
          target: `${props.path}/roles`,
          displayed: props.isAdmin
        }, {
          name: 'organizations',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-building',
          label: trans('organizations'),
          target: `${props.path}/organizations`
        }, {
          name: 'profile',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-id-card',
          label: trans('user_profile'),
          target: `${props.path}/profile`,
          displayed: props.isAdmin
        }, {
          name: 'parameters',
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-cog',
          label: trans('parameters'),
          target: `${props.path}/parameters`,
          displayed: props.isAdmin
        }
      ]}
      onClick={props.autoClose}
    />
  </MenuSection>

CommunityMenu.propTypes = {
  path: T.string,
  isAdmin: T.bool.isRequired,

  // from menu
  opened: T.bool.isRequired,
  toggle: T.func.isRequired,
  autoClose: T.func.isRequired
}

export {
  CommunityMenu
}
